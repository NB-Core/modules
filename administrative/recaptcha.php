<?php

declare(strict_types=1);

/**
 * Classic Google reCAPTCHA v3 module integration.
 *
 * README (short)
 * --------------
 * This module adds score-based classic reCAPTCHA v3 protection to account
 * creation, login, and petition submission via NB-Core hooks. It renders a
 * hidden token field, calls grecaptcha.execute() with a per-form action, and
 * verifies that token server-side with Google's siteverify endpoint.
 *
 * Classic v3 setup requires only a site key and secret key. Unlike reCAPTCHA
 * Enterprise assessments, it does not need a Google Cloud project ID or API key.
 * The legacy project_id module setting is intentionally unused so older module
 * installs can upgrade without losing stored settings.
 *
 * Server-side v3 enforcement checks:
 * - siteverify request succeeds and returns success = true
 * - returned action matches the expected action for the hook
 * - returned score >= configured minimum
 * - returned hostname, when present, matches the current request host using a
 *   conservative same-host/www-subdomain check
 *
 * Why it's implemented this way:
 * - Classic reCAPTCHA v3 score keys do not present a normal checkbox challenge;
 *   the visible clue is usually Google's badge/logo.
 * - Tokens are short-lived, so protected forms refresh tokens immediately before
 *   submit. The server also validates score, action, and hostname.
 * - Brave Shields and other script/ad blockers may block api.js. When that
 *   happens the server remains fail-closed, while the page shows a clear
 *   client-side hint that the captcha script could not be loaded.
 * - The module uses CURL with short timeouts and does not require Composer or
 *   Google client libraries, because this repository contains standalone LOTGD
 *   modules only.
 *
 * Setup:
 * 1) Create/configure a classic reCAPTCHA v3 score-based website key for this
 *    domain.
 * 2) Copy the website key into "sitekey".
 * 3) Copy the v3 secret key into "sitesecret".
 * 4) Tune the minimum score based on your traffic.
 *
 * Docs:
 * https://developers.google.com/recaptcha/docs/v3
 * https://developers.google.com/recaptcha/docs/verify
 */

/**
 * Provide module metadata and settings for the reCAPTCHA integration.
 *
 * @return array Module information and configuration definitions.
 */
function recaptcha_getmoduleinfo(): array
{
    $info = array(
            "name" => "Google ReCaptcha Plugin",
            "version" => "1.0",
            "author" => "`2Oliver Brendel",
            "override_forced_nav" => true,
            "category" => "Administrative",
            "download" => "",
            "settings" => array(
                "Captcha Settings,title",
                "sitekey" => "reCAPTCHA v3 Site Key,text|KEY",
                "project_id" => "Unused legacy Google Cloud Project ID (classic v3 uses only site key and secret),text|PROJECT_ID",
                "sitesecret" => "reCAPTCHA v3 Secret Key,text|SECRET",
                "min_score" => "Minimum acceptable score,range,0,1,0.1|0.5",
                ),
             );
    return $info;
}

/**
 * Register module hooks and ensure dependencies are available.
 *
 * @return bool True when installed successfully; false when missing CURL.
 */
function recaptcha_install(): bool
{
    if (extension_loaded('curl')) {
        debug("CURL is necessary to make this work and is loaded.`n");
    } else {
        debug("CURL PHP5 extension is necessary and NOT loaded! Install it on your server!`n");
        return false;
    }
    module_addhook_priority("addpetition", 50);
    module_addhook_priority("check-create", 50);
    module_addhook_priority("pre-login", 50);
    module_addhook_priority("create-form", 50);
    module_addhook_priority("index-login", 50);
    module_addhook_priority("petitionform", 50);
    return true;
}

/**
 * Map module hooks to reCAPTCHA v3 actions.
 *
 * Keeping this mapping in one place avoids drift between render-time action
 * assignment and verify-time enforcement.
 *
 * @return array<string, string> Hook name to expected action mapping.
 */
function recaptcha_get_hook_action_map(): array
{
    return array(
        'create-form' => 'create',
        'check-create' => 'create',
        'petitionform' => 'petition',
        'addpetition' => 'petition',
        'index-login' => 'login',
        'pre-login' => 'login',
    );
}

/**
 * Resolve the expected reCAPTCHA action for a given hook.
 *
 * @param string $hookname Current NB-Core hook name.
 *
 * @return string|null Action name when mapped; null when unsupported.
 */
function recaptcha_get_expected_action(string $hookname): ?string
{
    $actionMap = recaptcha_get_hook_action_map();

    return $actionMap[$hookname] ?? null;
}

/**
 * Uninstall hook for the module.
 *
 * @return bool Always true; no teardown required.
 */
function recaptcha_uninstall(): bool
{
    return true;
}

/**
 * Hook handler to render reCAPTCHA widgets and verify submissions.
 *
 * @param string $hookname Hook name provided by the engine.
 * @param array  $args     Hook arguments.
 *
 * @return array Modified hook arguments.
 */
function recaptcha_dohook(string $hookname, array $args): array
{
    global $session;
    if (!extension_loaded('curl')) {
        // Without CURL, we cannot reach Google's verification endpoint safely.
        output("Verification by Captcha disabled. Code #154 Order 66`n");
        return $args;
    }
    // Load configured classic reCAPTCHA v3 settings. The legacy project_id
    // setting is intentionally unused because v3 siteverify needs only the
    // site key on the client and the secret key on the server.
    $sitekey = (string) get_module_setting('sitekey');
    $siteSecret = (string) get_module_setting('sitesecret');
    $rawMinScore = get_module_setting('min_score');
    $hasMinScore = is_numeric($rawMinScore);
    $minScore = (float) $rawMinScore;
    $expectedAction = recaptcha_get_expected_action($hookname);

    $renderHooks = array('create-form', 'petitionform', 'index-login');

    switch ($hookname) {
        case "check-create":
        case "addpetition":
        case "pre-login":
            // Server-generated auto-login forms (forgotten password, email
            // validation) embed the stored hash with a "!md52!" prefix and
            // never pass through home.php where the reCAPTCHA widget is
            // rendered.  Skip verification for this trusted server flow;
            // login.php itself rejects !md52! without force=1, so a bot
            // cannot abuse this bypass without already holding the DB hash.
            if ($hookname === 'pre-login') {
                $postedPassword = (string) httppost('password');
                if (substr($postedPassword, 0, 6) === '!md52!') {
                    break;
                }
            }
            // Verify the classic v3 token with Google's siteverify endpoint.
            if ($expectedAction === null || $sitekey === '' || $siteSecret === '' || !$hasMinScore) {
                // Missing backend credentials mean we cannot verify server-side.
                // Fail closed for protected submissions while leaving public pages renderable.
                recaptcha_log_diagnostic('missing-config', $hookname, array(
                    'has_sitekey' => $sitekey !== '',
                    'has_secret' => $siteSecret !== '',
                    'has_expected_action' => $expectedAction !== null,
                    'has_min_score' => $hasMinScore,
                ));
                $failureMessage = "`c`b`\$Sorry, the captcha service is unavailable. Please try again later.`b`c`n`n";
                recaptcha_apply_failure($hookname, $args, $failureMessage);
                break;
            }

            // Extract the short-lived classic v3 token posted by the client.
            $recaptchaResponse = (string) httppost('g-recaptcha-response');
            if ($recaptchaResponse === '') {
                // No token usually means api.js was blocked, failed to load,
                // or the hidden input was not inside the submitted form.
                recaptcha_log_diagnostic('missing-token', $hookname, array(
                    'action' => $expectedAction,
                    'user_agent' => recaptcha_get_server_value('HTTP_USER_AGENT'),
                ));
                $failureMessage = "`c`b`\$Sorry, the captcha could not be loaded or verified. Please disable Brave Shields/ad blocking for this site and try again.`b`c`n`n";
                recaptcha_apply_failure($hookname, $args, $failureMessage);
                break;
            }

            $verification = recaptcha_verify_v3_response($siteSecret, $recaptchaResponse);

            if (!$verification['request_success']) {
                recaptcha_log_diagnostic('verify-request-failed', $hookname, $verification['diagnostics']);
                $failureMessage = "`c`b`\$Sorry, the captcha service is unavailable. Please try again later.`b`c`n`n";
                recaptcha_apply_failure($hookname, $args, $failureMessage);
                break;
            }

            if (!recaptcha_v3_response_passes($verification, (string) $expectedAction, $minScore)) {
                recaptcha_log_diagnostic('verify-rejected', $hookname, $verification['diagnostics']);

                if (recaptcha_is_low_score_create_rejection($hookname, $verification, (string) $expectedAction, $minScore)) {
                    // Valid-but-low-score v3 results are common with privacy browsers,
                    // strict extensions, and proxy/VPN egress. Keep account creation
                    // fail-closed to protect against spam, but give honest next steps
                    // without exposing score, threshold, hostname, token, or diagnostics.
                    $failureMessage = "`c`b`\$Account creation could not be verified. If you use Brave Shields, a VPN, proxy, or strict privacy extensions, please temporarily disable them for registration and try again.`b`c`n`n";
                    recaptcha_apply_failure($hookname, $args, $failureMessage);
                    unset($args['g-recaptcha-response']);
                    break;
                }

                $extra = array();
                if ($verification['score'] !== null) {
                    $extra[] = sprintf('score: %.2f', $verification['score']);
                }
                if ($verification['action'] !== null) {
                    $extra[] = sprintf('action: %s', $verification['action']);
                }
                if ($verification['hostname'] !== null) {
                    $extra[] = sprintf('host: %s', $verification['hostname']);
                }
                if (!empty($verification['error_codes'])) {
                    $extra[] = implode(',', $verification['error_codes']);
                }
                $extraMessage = !empty($extra) ? sprintf("(%s)", implode(" | ", $extra)) : '';
                $failureMessage = sprintf("`c`b`\$Sorry, but you entered the wrong captcha code, try again`b`c%s`n`n", $extraMessage);
                recaptcha_apply_failure($hookname, $args, $failureMessage);
            }
            // Remove the raw token from args; it's no longer needed.
            unset($args['g-recaptcha-response']); //unset this as it is useless now
            break;
        case "create-form":
        case "petitionform":
        case "index-login":
            if ($expectedAction === null || !in_array($hookname, $renderHooks, true) || $sitekey === '') {
                // Keep home/index rendering resilient for anonymous users even when
                // reCAPTCHA is misconfigured; submit-time hooks remain fail-closed.
                break;
            }

            // Login intentionally uses the exact same v3 rendering/token path as
            // petition/create so all hooks share one code path and one token shape.
            recaptcha_render_v3_token(
                (string) $sitekey,
                $expectedAction,
                recaptcha_get_form_selector($hookname)
            );
            break;
    }
    return $args;
}

/**
 * Render api.js and generate a classic reCAPTCHA v3 token.
 *
 * All protected forms refresh their token at submit-time because v3 tokens are
 * short-lived and should be freshly bound to the submitted action. The form
 * selector is a defensive fallback for themes/core versions that render module
 * hooks just outside the
 * actual form element.
 *
 * @param string $sitekey      Public site key used by api.js.
 * @param string $action       Expected reCAPTCHA action for this hook.
 * @param string $formSelector Conservative CSS selector for the target form.
 *
 * @return void
 */
function recaptcha_render_v3_token(string $sitekey, string $action, string $formSelector): void
{
    $sitekeyJson = json_encode($sitekey, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $actionJson = json_encode($action, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $formSelectorJson = json_encode($formSelector, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    $sitekeyQuery = rawurlencode($sitekey);

    rawoutput("<script src=\"https://www.google.com/recaptcha/api.js?render={$sitekeyQuery}\" defer></script>");
    rawoutput("<input type=\"hidden\" name=\"g-recaptcha-response\" id=\"g-recaptcha-response\" data-recaptcha-action=\"" . htmlentities($action, ENT_QUOTES, 'UTF-8') . "\">");
    rawoutput("<div id=\"recaptcha-load-warning\" role=\"alert\" aria-live=\"polite\" style=\"display:none; color:#c00; font-weight:bold; margin:0.5em 0;\">Captcha could not be loaded. Please disable Brave Shields/ad blocking for this site and retry.</div>");
    rawoutput("<script>
        document.addEventListener('DOMContentLoaded', function () {
            var siteKey = {$sitekeyJson};
            var action = {$actionJson};
            var formSelector = {$formSelectorJson};
            var tokenInput = document.getElementById('g-recaptcha-response');
            var warning = document.getElementById('recaptcha-load-warning');
            var tokenPromise = null;
            var handledSubmit = false;

            function findTargetForm() {
                if (tokenInput && tokenInput.form) {
                    return tokenInput.form;
                }

                // Core/theme hook placement can vary. Prefer the hook-specific
                // selector, then fall back to the nearest POST form so the token
                // is submitted with the protected action rather than left outside
                // the form.
                var targetForm = formSelector ? document.querySelector(formSelector) : null;
                if (!targetForm) {
                    targetForm = document.querySelector('form[method=\"post\" i], form[method=\"POST\"]');
                }

                if (targetForm && tokenInput) {
                    targetForm.appendChild(tokenInput);
                }

                return targetForm;
            }

            function showLoadWarning() {
                if (warning) {
                    warning.style.display = 'block';
                }
            }

            function recaptchaAvailable() {
                return Boolean(window.grecaptcha && grecaptcha.ready && grecaptcha.execute);
            }

            function waitForRecaptcha(timeoutMs) {
                var startedAt = Date.now();

                return new Promise(function (resolve) {
                    function poll() {
                        if (recaptchaAvailable()) {
                            resolve(true);
                            return;
                        }

                        if (Date.now() - startedAt >= timeoutMs) {
                            resolve(false);
                            return;
                        }

                        window.setTimeout(poll, 100);
                    }

                    poll();
                });
            }

            function executeRecaptcha(forceRefresh, showWarningOnFailure) {
                if (!tokenInput) {
                    if (showWarningOnFailure) {
                        showLoadWarning();
                    }
                    return Promise.resolve('');
                }

                if (!forceRefresh && tokenPromise) {
                    return tokenPromise;
                }

                tokenPromise = waitForRecaptcha(2500).then(function (available) {
                    if (!available) {
                        // reCAPTCHA v3 api.js can finish initialization after
                        // DOMContentLoaded. Initial background generation must
                        // stay silent so normal page loads do not show a false
                        // warning before Google exposes grecaptcha.
                        if (showWarningOnFailure) {
                            tokenInput.value = '';
                            showLoadWarning();
                        }
                        return '';
                    }

                    return new Promise(function (resolve) {
                        grecaptcha.ready(function () {
                            grecaptcha.execute(siteKey, { action: action }).then(function (token) {
                                tokenInput.value = token || '';
                                if (!tokenInput.value && showWarningOnFailure) {
                                    showLoadWarning();
                                }
                                resolve(tokenInput.value);
                            }, function () {
                                tokenInput.value = '';
                                if (showWarningOnFailure) {
                                    showLoadWarning();
                                }
                                resolve('');
                            });
                        });
                    });
                });

                return tokenPromise;
            }

            var form = findTargetForm();

            // Generate an initial token so the badge/script state is visible,
            // then refresh at submit-time because v3 tokens are short-lived.
            // reCAPTCHA v3 script initialization can lag behind DOMContentLoaded,
            // so this background generation intentionally suppresses visible
            // warnings; submit-time generation below surfaces actionable errors.
            executeRecaptcha(true, false);

            if (form) {
                form.addEventListener('submit', function (event) {
                    if (handledSubmit) {
                        return;
                    }
                    event.preventDefault();
                    var submitter = event.submitter || null;
                    executeRecaptcha(true, true).then(function () {
                        handledSubmit = true;
                        // Native validation and inline onsubmit handlers (such as
                        // legacy login password hashing) already ran for this
                        // submit event. Dispatching another one with
                        // requestSubmit() would run them twice, so send the form
                        // directly and carry the clicked button's name along.
                        if (submitter && submitter.name) {
                            var carrier = document.createElement('input');
                            carrier.type = 'hidden';
                            carrier.name = submitter.name;
                            carrier.value = submitter.value;
                            form.appendChild(carrier);
                        }
                        HTMLFormElement.prototype.submit.call(form);
                    });
                });
            }
        });
    </script>");
}

/**
 * Resolve a conservative target form selector for each render hook.
 *
 * @param string $hookname Current render hook.
 *
 * @return string CSS selector used only when the hidden input is outside a form.
 */
function recaptcha_get_form_selector(string $hookname): string
{
    switch ($hookname) {
        case 'index-login':
            return "form[action*='login']";
        case 'petitionform':
            return "form[action*='petition']";
        case 'create-form':
            return "form[action*='create'], form[action*='newday.php'], form[action*='login.php']";
    }

    return '';
}

/**
 * Verify a classic reCAPTCHA v3 browser token with Google's siteverify API.
 *
 * Classic v3 verification posts only the secret key, browser token response,
 * and an optional client IP hint. The secret and token are never copied into
 * diagnostics because those details may be written to logs.
 *
 * @param string $siteSecret Secret key from the classic reCAPTCHA v3 admin UI.
 * @param string $token      Short-lived token from grecaptcha.execute().
 *
 * @return array<string, mixed> Normalized v3 verification result and diagnostics.
 */
function recaptcha_verify_v3_response(string $siteSecret, string $token): array
{
    $postFields = array(
        'secret' => $siteSecret,
        'response' => $token,
    );

    $remoteIp = recaptcha_get_user_ip_address();
    if ($remoteIp !== '') {
        $postFields['remoteip'] = $remoteIp;
    }

    $request = curl_init('https://www.google.com/recaptcha/api/siteverify');
    curl_setopt($request, CURLOPT_POST, true);
    curl_setopt($request, CURLOPT_POSTFIELDS, http_build_query($postFields, '', '&'));
    curl_setopt($request, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
    curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($request, CURLOPT_TIMEOUT, 5);
    curl_setopt($request, CURLOPT_CONNECTTIMEOUT, 3);

    $responseBody = curl_exec($request);
    $curlError = curl_error($request);
    $httpCode = (int) curl_getinfo($request, CURLINFO_HTTP_CODE);
    curl_close($request);

    if ($responseBody === false || $httpCode < 200 || $httpCode >= 300) {
        return recaptcha_empty_v3_response(false, array(
            'http_code' => $httpCode,
            'curl_error' => $curlError !== '' ? $curlError : null,
        ));
    }

    $verification = json_decode((string) $responseBody, true);
    if (!is_array($verification)) {
        return recaptcha_empty_v3_response(false, array(
            'http_code' => $httpCode,
            'error' => 'json-decode-failed',
        ));
    }

    $success = ($verification['success'] ?? false) === true;
    $returnedAction = isset($verification['action']) ? (string) $verification['action'] : null;
    $score = isset($verification['score']) ? (float) $verification['score'] : null;
    $hostname = isset($verification['hostname']) ? (string) $verification['hostname'] : null;
    $challengeTs = isset($verification['challenge_ts']) ? (string) $verification['challenge_ts'] : null;
    $errorCodes = isset($verification['error-codes']) && is_array($verification['error-codes'])
        ? array_values($verification['error-codes'])
        : array();

    return array(
        'request_success' => true,
        'success' => $success,
        'action' => $returnedAction,
        'score' => $score,
        'hostname' => $hostname,
        'challenge_ts' => $challengeTs,
        'error_codes' => $errorCodes,
        'diagnostics' => array(
            'http_code' => $httpCode,
            'success' => $success,
            'action' => $returnedAction,
            'score' => $score,
            'hostname' => $hostname,
            'challenge_ts' => $challengeTs,
            'error_codes' => $errorCodes,
        ),
    );
}

/**
 * Build an empty normalized v3 verification result.
 *
 * @param bool  $requestSuccess Whether the API request itself succeeded.
 * @param array $diagnostics    Non-secret details safe for logs.
 *
 * @return array<string, mixed> Normalized failed v3 verification result.
 */
function recaptcha_empty_v3_response(bool $requestSuccess, array $diagnostics): array
{
    return array(
        'request_success' => $requestSuccess,
        'success' => false,
        'action' => null,
        'score' => null,
        'hostname' => null,
        'challenge_ts' => null,
        'error_codes' => array(),
        'diagnostics' => $diagnostics,
    );
}

/**
 * Decide whether a normalized v3 response satisfies this module's policy.
 *
 * The module fails closed unless Google accepted the token, the action matches
 * the protected hook, the score meets the configured threshold, and any returned
 * hostname belongs to the current request host.
 *
 * @param array<string, mixed> $verification   Normalized v3 verification result.
 * @param string              $expectedAction Expected action for this hook.
 * @param float               $minScore       Minimum acceptable v3 score.
 *
 * @return bool True when the request should proceed.
 */
function recaptcha_v3_response_passes(array $verification, string $expectedAction, float $minScore): bool
{
    return (
        ($verification['request_success'] ?? false) === true
        && ($verification['success'] ?? false) === true
        && ($verification['action'] ?? null) === $expectedAction
        && isset($verification['score'])
        && (float) $verification['score'] >= $minScore
        && recaptcha_hostname_allowed($verification['hostname'] ?? null)
    );
}

/**
 * Identify account-creation denials caused only by a valid low-score v3 token.
 *
 * This is deliberately narrower than the full rejection path: action and hostname
 * must already match, and Google's response must be successful. The caller uses
 * this to show create-specific recovery guidance without disclosing scoring
 * internals to a potential spammer.
 *
 * @param string              $hookname       Hook currently being processed.
 * @param array<string, mixed> $verification   Normalized v3 verification result.
 * @param string              $expectedAction Expected action for this hook.
 * @param float               $minScore       Minimum acceptable v3 score.
 *
 * @return bool True when account creation failed only because score was low.
 */
function recaptcha_is_low_score_create_rejection(
    string $hookname,
    array $verification,
    string $expectedAction,
    float $minScore
): bool
{
    return (
        $hookname === 'check-create'
        && $expectedAction === 'create'
        && ($verification['request_success'] ?? false) === true
        && ($verification['success'] ?? false) === true
        && ($verification['action'] ?? null) === $expectedAction
        && isset($verification['score'])
        && (float) $verification['score'] < $minScore
        && recaptcha_hostname_allowed($verification['hostname'] ?? null)
    );
}

/**
 * Check Google's returned hostname against the current request host.
 *
 * reCAPTCHA v3 normally returns the hostname where the token was generated. If
 * Google omits it, keep compatibility and allow the response; otherwise require
 * an exact host match or a conservative www/non-www equivalent.
 *
 * @param mixed $hostname Hostname value returned by siteverify.
 *
 * @return bool True when absent or matched to the current request host.
 */
function recaptcha_hostname_allowed($hostname): bool
{
    if ($hostname === null || $hostname === '') {
        return true;
    }

    $returnedHost = recaptcha_normalize_hostname((string) $hostname);
    $requestHost = recaptcha_normalize_hostname(recaptcha_get_server_value('HTTP_HOST'));

    if ($returnedHost === '' || $requestHost === '') {
        return false;
    }

    if ($returnedHost === $requestHost) {
        return true;
    }

    return recaptcha_strip_www_prefix($returnedHost) === recaptcha_strip_www_prefix($requestHost);
}

/**
 * Normalize a hostname by removing ports, trailing dots, and case differences.
 *
 * @param string $hostname Raw hostname, possibly with a port.
 *
 * @return string Normalized hostname.
 */
function recaptcha_normalize_hostname(string $hostname): string
{
    $hostname = strtolower(trim($hostname));
    $hostname = rtrim($hostname, '.');

    if ($hostname === '') {
        return '';
    }

    $parsedHost = parse_url(strpos($hostname, '://') === false ? 'https://' . $hostname : $hostname, PHP_URL_HOST);

    return is_string($parsedHost) ? rtrim(strtolower($parsedHost), '.') : '';
}

/**
 * Strip a single leading www. prefix for conservative same-site matching.
 *
 * @param string $hostname Normalized hostname.
 *
 * @return string Hostname without a leading www. prefix.
 */
function recaptcha_strip_www_prefix(string $hostname): string
{
    return strncmp($hostname, 'www.', 4) === 0 ? substr($hostname, 4) : $hostname;
}

/**
 * Return a safe server value without raising notices.
 *
 * @param string $key $_SERVER key to read.
 *
 * @return string Trimmed server value or an empty string.
 */
function recaptcha_get_server_value(string $key): string
{
    return isset($_SERVER[$key]) ? trim((string) $_SERVER[$key]) : '';
}

/**
 * Determine the most useful client IP value available to the module.
 *
 * The direct REMOTE_ADDR value is preferred because X-Forwarded-For is only
 * trustworthy when the deployment controls its reverse proxy chain. If only an
 * X-Forwarded-For value exists, use its first item as a best-effort signal for
 * Google's risk analysis.
 *
 * @return string Client IP address hint, or an empty string.
 */
function recaptcha_get_user_ip_address(): string
{
    $remoteAddress = recaptcha_get_server_value('REMOTE_ADDR');
    if ($remoteAddress !== '') {
        return filter_var($remoteAddress, FILTER_VALIDATE_IP) ? $remoteAddress : '';
    }

    $forwardedFor = recaptcha_get_server_value('HTTP_X_FORWARDED_FOR');
    if ($forwardedFor === '') {
        return '';
    }

    $parts = explode(',', $forwardedFor);
    $candidate = trim((string) ($parts[0] ?? ''));

    return filter_var($candidate, FILTER_VALIDATE_IP) ? $candidate : '';
}

/**
 * Log non-secret captcha diagnostics for administrators.
 *
 * @param string $event    Short diagnostic event name.
 * @param string $hookname Hook being processed.
 * @param array  $details  Non-secret details; never include API keys or tokens.
 *
 * @return void
 */
function recaptcha_log_diagnostic(string $event, string $hookname, array $details = array()): void
{
    $safeDetails = array();
    foreach ($details as $key => $value) {
        if ($value === null || $value === '') {
            continue;
        }
        $safeDetails[$key] = $value;
    }

    $jsonFlags = JSON_UNESCAPED_SLASHES;
    if (defined('JSON_INVALID_UTF8_SUBSTITUTE')) {
        $jsonFlags |= JSON_INVALID_UTF8_SUBSTITUTE;
    }

    $detailsJson = json_encode($safeDetails, $jsonFlags);
    if ($detailsJson === false) {
        $detailsJson = '{}';
    }

    $message = sprintf(
        'recaptcha %s on %s: %s',
        $event,
        $hookname,
        $detailsJson
    );

    if (function_exists('debug')) {
        debug($message . "`n");
    }

    error_log($message);
}

/**
 * Apply hook-specific failure behavior while keeping existing non-login logic.
 *
 * Account creation and petitions preserve historical cancel/block flags. Login
 * failures instead attach a message and clear the in-progress user session so
 * authentication cannot succeed on invalid, missing, or mismatched tokens.
 *
 * @param string $hookname Hook currently being processed.
 * @param array  $args     Hook argument payload, mutated by reference.
 * @param string $message  User-facing failure message.
 *
 * @return void
 */
function recaptcha_apply_failure(string $hookname, array &$args, string $message): void
{
    global $session;

    if ($hookname === 'pre-login') {
        // login.php renders only $session['message'] after the pre-login hook.
        // For this path we must not rely on hook args like cancelreason/msg.
        $loginMessage = recaptcha_normalize_login_failure_message($message);
        if (!isset($session['message']) || trim((string) $session['message']) === '') {
            // login.php reads this channel after HookHandler::hook("pre-login").
            // Always seed a non-empty fallback so captcha failures stay visible.
            $session['message'] = $loginMessage;
        } else {
            $session['message'] .= $loginMessage;
        }
        // Preserve core login behavior: append message, clear user, redirect to entry.
        $session['user'] = array();
        require_once('lib/redirect.php');
        redirect('index.php');
        return;
    }

    $args['cancelreason'] = $message;
    $args['cancelpetition'] = true;
    $args['blockaccount'] = true;
    $args['msg'] = $args['cancelreason'];
}

/**
 * Normalize a login failure message so it is always safe and non-empty.
 *
 * The login page displays $session['message'], therefore pre-login failures
 * must provide a direct user-facing string in that channel.
 *
 * @param string $message Candidate login failure message.
 *
 * @return string Sanitized, non-empty display message.
 */
function recaptcha_normalize_login_failure_message(string $message): string
{
    $normalizedMessage = trim(strip_tags($message));
    if ($normalizedMessage === '') {
        $normalizedMessage = 'Sorry, but you entered the wrong captcha code, try again.';
    }

    if (!str_ends_with($normalizedMessage, "\n")) {
        $normalizedMessage .= "\n\n";
    }

    return $normalizedMessage;
}

function recaptcha_run(): void
{
}
