<?php

declare(strict_types=1);

require_once __DIR__ . '/availabilityapi/Authentication.php';
require_once __DIR__ . '/availabilityapi/Providers.php';

/**
 * Describe the read-only availability API and its token preference.
 */
function availabilityapi_getmoduleinfo(): array
{
    return [
        'name' => 'Availability API',
        'version' => '1.0.0',
        'author' => 'Shinobi Legends',
        'category' => 'Administrative',
        'allowanonymous' => true,
        'prefs' => [
            'Availability API,title',
            // Tokens are exclusively managed by the owner page; there is no default.
            'api_token_hash' => 'API token hash (managed by Availability API),hidden|',
        ],
        'requires' => [
            'mountrarity' => '1.2|Mount Rarity',
            'ninjamerchantstore' => '1.0|Ninja Merchant Village Shop',
        ],
    ];
}

/**
 * Register the authenticated preferences navigation integration.
 */
function availabilityapi_install(): bool
{
    module_addhook('footer-prefs');

    return true;
}

/**
 * Preserve existing hashes if the module is uninstalled and later restored.
 */
function availabilityapi_uninstall(): bool
{
    return true;
}

/**
 * Add token management to the logged-in player's preferences interface.
 *
 * @param string $hookname Current hook name.
 * @param array<mixed> $args Hook payload.
 * @return array<mixed>
 */
function availabilityapi_dohook(string $hookname, array $args): array
{
    global $session;

    if ($hookname === 'footer-prefs' && (int) ($session['user']['acctid'] ?? 0) > 0) {
        addnav(translate_inline('Availability API'));
        addnav(
            translate_inline('Manage API token'),
            'runmodule.php?module=availabilityapi&manage=token'
        );
    }

    return $args;
}

/**
 * Route an API resource or the authenticated token-management page.
 */
function availabilityapi_run(): void
{
    $endpoint = isset($_GET['endpoint']) ? (string) $_GET['endpoint'] : '';
    if ($endpoint !== '') {
        availabilityapi_run_endpoint($endpoint);
    }

    $managementRoute = isset($_GET['manage']) ? (string) $_GET['manage'] : '';
    if ($managementRoute === 'token') {
        availabilityapi_run_token_management();

        return;
    }

    // Keep UI and API routing explicit: merely reaching this anonymous-enabled
    // module must neither expose token management nor imply authentication.
    availabilityapi_json_error(404, 'not_found', 'The requested module route was not found.');
}

/**
 * Authenticate and emit one read-only API resource.
 */
function availabilityapi_run_endpoint(string $endpoint): void
{
    if (!in_array($endpoint, ['mounts', 'merchant'], true)) {
        availabilityapi_json_error(404, 'not_found', 'The requested API endpoint was not found.');
    }

    if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'GET') {
        header('Allow: GET');
        availabilityapi_json_error(405, 'method_not_allowed', 'This endpoint only accepts GET requests.');
    }

    try {
        // allowanonymous only reaches this boundary; every dataset still requires
        // a header credential resolved to a current, eligible account.
        $authentication = availabilityapi_authenticate_request();
        if ($authentication->status !== 200) {
            availabilityapi_json_error(
                $authentication->status,
                $authentication->status === 403 ? 'account_ineligible' : 'unauthorized',
                $authentication->status === 403
                    ? 'The token owner is not eligible to use this API.'
                    : 'A valid API token is required.'
            );
        }

        // HTTP-layer limiting is authoritative. No application limiter is used
        // because this core has no verified, write-free rate-limit facility.
        $data = $endpoint === 'mounts'
            ? availabilityapi_mounts_dataset()
            : availabilityapi_merchant_dataset();

        availabilityapi_json_response(200, [
            'api_version' => '1',
            'generated_at' => gmdate('Y-m-d\TH:i:s\Z'),
            'data' => $data,
            'meta' => ['count' => count($data)],
        ]);
    } catch (AvailabilityApiServiceException $exception) {
        availabilityapi_json_error(503, 'service_unavailable', 'Availability data is temporarily unavailable.');
    } catch (Throwable $exception) {
        // Do not log request material here: server/PHP exception logging can add
        // SQL, paths, or headers. The public response remains deliberately generic.
        availabilityapi_json_error(500, 'internal_error', 'An unexpected error occurred.');
    }
}

/**
 * Render and process Generate, Rotate, and Revoke for the logged-in owner.
 */
function availabilityapi_run_token_management(): void
{
    global $session;

    if ((int) ($session['user']['acctid'] ?? 0) <= 0) {
        // Anonymous callers can reach run(), but never token lifecycle actions.
        availabilityapi_json_error(401, 'unauthorized', 'Authentication is required.');
    }

    page_header(translate_inline('Availability API token'));
    addnav(translate_inline('Navigation'));
    addnav(translate_inline('Return to preferences'), 'prefs.php');

    availabilityapi_render_token_guide();

    $tokenHash = (string) get_module_pref('api_token_hash');
    $action = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) === 'POST'
        ? trim((string) ($_POST['token_action'] ?? ''))
        : '';
    $plainToken = '';

    if ($action !== '') {
        $postedToken = (string) ($_POST['csrf_token'] ?? '');
        if (!hash_equals(availabilityapi_csrf_token(), $postedToken)) {
            output('`$Invalid request token. No token changes were made.`0`n`n');
        } elseif ($action === 'generate' && $tokenHash === '') {
            $created = availabilityapi_create_token();
            set_module_pref('api_token_hash', $created['hash']);
            $tokenHash = $created['hash'];
            $plainToken = $created['token'];
            output('`@Your API token was generated. Copy it now; it cannot be recovered later.`0`n');
        } elseif ($action === 'rotate' && $tokenHash !== '') {
            $created = availabilityapi_create_token();
            set_module_pref('api_token_hash', $created['hash']);
            $tokenHash = $created['hash'];
            $plainToken = $created['token'];
            output('`@Your previous token was invalidated. Copy the new token now; it cannot be recovered later.`0`n');
        } elseif ($action === 'revoke' && $tokenHash !== '') {
            set_module_pref('api_token_hash', '');
            $tokenHash = '';
            output('`@Your API token was revoked immediately.`0`n');
        } else {
            output('`$That token action is not currently available.`0`n');
        }
    }

    if ($plainToken !== '') {
        rawoutput(
            '<p><code>' . htmlspecialchars($plainToken, ENT_QUOTES, 'UTF-8') . '</code></p>'
        );
        output('Store this token securely. If it is lost, rotate it; the existing token cannot be displayed again.`n`n');
    }

    output(
        $tokenHash === ''
            ? 'No API token currently exists for your account.`n'
            : 'An API token is active for your account. Its plaintext value is not stored.`n'
    );
    availabilityapi_render_token_form($tokenHash === '' ? 'generate' : 'rotate');
    if ($tokenHash !== '') {
        availabilityapi_render_token_form('revoke');
    }

    page_footer();
}

/**
 * Return the documented endpoint URLs without guessing deployment details.
 *
 * @return array{mounts: string, merchant: string}
 */
function availabilityapi_endpoint_urls(): array
{
    // Core verification needed: identify a stable, public game-base-URL setting
    // or helper in NB-Core/lotgd 2.x. Until verified, relative URLs are safer than
    // deriving a hostname or protocol from request-controlled server variables.
    $baseUrl = '';
    $prefix = $baseUrl === '' ? '' : rtrim($baseUrl, '/') . '/';

    return [
        'mounts' => $prefix . 'runmodule.php?module=availabilityapi&endpoint=mounts',
        'merchant' => $prefix . 'runmodule.php?module=availabilityapi&endpoint=merchant',
    ];
}

/**
 * Render the translated, player-facing API and token-management guide.
 */
function availabilityapi_render_token_guide(): void
{
    $urls = availabilityapi_endpoint_urls();
    $mountsUrl = htmlspecialchars($urls['mounts'], ENT_QUOTES, 'UTF-8');
    $merchantUrl = htmlspecialchars($urls['merchant'], ENT_QUOTES, 'UTF-8');
    $customExample = htmlspecialchars(
        "curl -H 'X-API-TOKEN: YOUR_TOKEN' \\\n  'https://game.example/runmodule.php?module=availabilityapi&endpoint=mounts'",
        ENT_QUOTES,
        'UTF-8'
    );
    $bearerExample = htmlspecialchars(
        "curl -H 'Authorization: Bearer YOUR_TOKEN' \\\n  'https://game.example/runmodule.php?module=availabilityapi&endpoint=mounts'",
        ENT_QUOTES,
        'UTF-8'
    );

    output(translate_inline('`bAvailability API guide`b`n'));
    output(translate_inline('The API reports the mounts and Ninja Merchant stock that are currently available.`n'));
    output(translate_inline('Requests are read-only and do not reroll availability.`n`n'));
    output(
        translate_inline(
            'A generated token represents your API access. Keep it secret: its plaintext is shown only once.`n'
        )
    );
    output(
        translate_inline(
            'If you lose a token, rotate it. Revoke any token that is compromised or no longer used.`n'
        )
    );
    output(
        translate_inline(
            'Never place a token in a URL, Discord message, screenshot, or public bot source code.`n`n'
        )
    );
    output(translate_inline('Endpoints:`n'));
    rawoutput("<p><code>{$mountsUrl}</code><br><code>{$merchantUrl}</code></p>");
    output(
        translate_inline(
            'In the examples below, replace https://game.example with your game website address.`n`n'
        )
    );
    output(translate_inline('Recommended request header:`n'));
    rawoutput("<pre>{$customExample}</pre>");
    output(translate_inline('Standard Bearer alternative:`n'));
    rawoutput("<pre>{$bearerExample}</pre>");
    output(
        translate_inline(
            'When a client offers a Bearer Token authentication mode, paste only the raw token into its token field. '
            . 'The client adds the word Bearer itself.`n`n'
        )
    );
    output(
        translate_inline(
            'If Bearer authentication returns 401, the server may not be forwarding the Authorization header. '
            . 'Try X-API-TOKEN before rotating a token you know is current.`n`n'
        )
    );
}

/**
 * Build or return the session-bound token protecting preference actions.
 */
function availabilityapi_csrf_token(): string
{
    global $session;

    // Core verification needed: no generic synchronous-form CSRF helper is used
    // by repository modules. This follows the deployed TwoFactorAuth module's
    // verified session-token pattern and validates before every preference write.
    if (
        !isset($session['availabilityapi_csrf'])
        || !is_string($session['availabilityapi_csrf'])
        || $session['availabilityapi_csrf'] === ''
    ) {
        $session['availabilityapi_csrf'] = bin2hex(random_bytes(32));
    }

    return (string) $session['availabilityapi_csrf'];
}

/**
 * Render one POST-only token action with translated player-facing labels.
 */
function availabilityapi_render_token_form(string $action): void
{
    $labels = [
        'generate' => translate_inline('Generate token'),
        'rotate' => translate_inline('Rotate token'),
        'revoke' => translate_inline('Revoke token'),
    ];
    $csrf = htmlspecialchars(availabilityapi_csrf_token(), ENT_QUOTES, 'UTF-8');
    $label = htmlspecialchars($labels[$action], ENT_QUOTES, 'UTF-8');

    addnav('', 'runmodule.php?module=availabilityapi&manage=token');
    rawoutput("<form method='post' action='runmodule.php?module=availabilityapi&amp;manage=token'>");
    rawoutput("<input type='hidden' name='csrf_token' value='{$csrf}'>");
    rawoutput("<input type='hidden' name='token_action' value='{$action}'>");
    rawoutput("<button type='submit'>{$label}</button></form>");
}

/**
 * Emit a stable JSON response and terminate before page chrome is rendered.
 *
 * @param array<string, mixed> $payload
 */
function availabilityapi_json_response(int $status, array $payload): void
{
    $json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if ($json === false) {
        $status = 500;
        $json = '{"error":{"code":"internal_error","message":"An unexpected error occurred."}}';
    }
    $includeBearerChallenge = $status === 401;

    // This clears PHP output buffers only, not LotGD's page-content accumulator.
    // Bound cleanup by the initial depth and stop if a buffer is not removable.
    $bufferDepth = ob_get_level();
    for ($bufferIndex = 0; $bufferIndex < $bufferDepth; $bufferIndex++) {
        if (!ob_end_clean()) {
            break;
        }
    }

    if (!headers_sent()) {
        http_response_code($status);
        if ($includeBearerChallenge) {
            // Advertise the standard Bearer challenge without reflecting credential
            // material or disclosing why authentication failed.
            header('WWW-Authenticate: Bearer realm="availabilityapi"');
        }
        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: no-store');
        header('X-Content-Type-Options: nosniff');
    }

    // Repository raw-response precedents such as rss.php write directly to the
    // HTTP response; this endpoint exits before LotGD renders its page accumulator.
    echo $json;
    exit;
}

/**
 * Emit the documented public error representation.
 */
function availabilityapi_json_error(int $status, string $code, string $message): void
{
    availabilityapi_json_response($status, [
        'error' => [
            'code' => $code,
            'message' => $message,
        ],
    ]);
}
