<?php

declare(strict_types=1);

use Doctrine\DBAL\ParameterType;
use Lotgd\MySQL\Database;

/**
 * Result of resolving an API header credential to its owning account.
 */
final class AvailabilityApiAuthenticationResult
{
    public int $status;
    public ?int $accountId;

    public function __construct(int $status, ?int $accountId = null)
    {
        $this->status = $status;
        $this->accountId = $accountId;
    }
}

/**
 * Read the Authorization header without accepting credentials from query data.
 *
 * Header syntax is validated separately so an empty or malformed supplied value
 * is rejected deterministically.
 */
function availabilityapi_authorization_header(): ?string
{
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        return (string) $_SERVER['HTTP_AUTHORIZATION'];
    }

    if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        return (string) $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    }

    if (function_exists('getallheaders')) {
        foreach (getallheaders() as $name => $value) {
            if (strcasecmp((string) $name, 'Authorization') === 0) {
                return (string) $value;
            }
        }
    }

    return null;
}

/**
 * Read and validate the X-API-TOKEN header.
 *
 * Only surrounding optional whitespace is removed. The credential must otherwise
 * consist exclusively of the URL-safe alphabet used by generated tokens. A null
 * result means the header was absent; an empty string means it was supplied but
 * invalid. Tokens are never accepted from request parameters, cookies, or bodies.
 */
function availabilityapi_api_token_header(): ?string
{
    $header = null;

    if (isset($_SERVER['HTTP_X_API_TOKEN'])) {
        $header = (string) $_SERVER['HTTP_X_API_TOKEN'];
    } elseif (function_exists('getallheaders')) {
        foreach (getallheaders() as $name => $value) {
            if (strcasecmp((string) $name, 'X-API-TOKEN') === 0) {
                $header = (string) $value;
                break;
            }
        }
    }

    if ($header === null) {
        return null;
    }

    $token = trim($header, " \t");
    if (
        $token === ''
        || strlen($token) > 256
        || !preg_match('/^[A-Za-z0-9_-]+$/D', $token)
    ) {
        return '';
    }

    return $token;
}

/**
 * Extract a standards-compatible Bearer credential from an Authorization value.
 *
 * The scheme is case-insensitive, surrounding optional whitespace is accepted,
 * and one or more spaces or horizontal tabs must separate it from the token.
 */
function availabilityapi_bearer_token(string $header): string
{
    if (!preg_match('/^[ \t]*Bearer[ \t]+([A-Za-z0-9_-]+)[ \t]*$/iD', $header, $matches)) {
        return '';
    }

    $token = $matches[1];

    return strlen($token) <= 256 ? $token : '';
}

/**
 * Authenticate the request against the deterministic module preference hash.
 *
 * A joined account lookup prevents preferences left behind by account deletion
 * from authenticating. The deployment consistently uses accounts.locked as its
 * account eligibility flag, including authenticated player searches.
 */
function availabilityapi_authenticate_request(): AvailabilityApiAuthenticationResult
{
    $authorizationHeader = availabilityapi_authorization_header();
    $bearerToken = $authorizationHeader === null
        ? null
        : availabilityapi_bearer_token($authorizationHeader);
    $apiToken = availabilityapi_api_token_header();

    if ($bearerToken === '' || $apiToken === '') {
        return new AvailabilityApiAuthenticationResult(401);
    }

    if ($bearerToken !== null && $apiToken !== null && !hash_equals($bearerToken, $apiToken)) {
        return new AvailabilityApiAuthenticationResult(401);
    }

    $token = $apiToken ?? $bearerToken;
    if ($token === null) {
        return new AvailabilityApiAuthenticationResult(401);
    }

    $candidateHash = hash('sha256', $token);
    try {
        $connection = Database::getDoctrineConnection();
        $preferences = Database::prefix('module_userprefs');
        $accounts = Database::prefix('accounts');

        // Parameter binding keeps even the derived credential hash out of SQL text.
        $row = $connection->executeQuery(
            "SELECT p.value AS token_hash, a.acctid, a.locked
               FROM {$preferences} p
               INNER JOIN {$accounts} a ON a.acctid = p.userid
              WHERE p.modulename = :module
                AND p.setting = :setting
                AND p.value = :token_hash
              LIMIT 1",
            [
                'module' => 'availabilityapi',
                'setting' => 'api_token_hash',
                'token_hash' => $candidateHash,
            ],
            [
                'module' => ParameterType::STRING,
                'setting' => ParameterType::STRING,
                'token_hash' => ParameterType::STRING,
            ]
        )->fetchAssociative();
    } catch (Throwable $exception) {
        // Credential stores are an authentication dependency. Preserve the
        // cause for operators while the endpoint exposes only its generic 503.
        throw new AvailabilityApiServiceException(
            'API credentials could not be resolved.',
            0,
            $exception
        );
    }

    if (!is_array($row) || !hash_equals((string) $row['token_hash'], $candidateHash)) {
        return new AvailabilityApiAuthenticationResult(401);
    }

    // Core verification needed: accounts.locked is the only per-account ban/lock
    // field established by modules in this deployment. If the deployed core adds
    // a separate account-level ban status, add it to this eligibility boundary.
    if ((int) $row['locked'] !== 0) {
        return new AvailabilityApiAuthenticationResult(403, (int) $row['acctid']);
    }

    return new AvailabilityApiAuthenticationResult(200, (int) $row['acctid']);
}

/**
 * Create a 256-bit URL-safe bearer token and its deterministic lookup hash.
 *
 * @return array{token: string, hash: string}
 */
function availabilityapi_create_token(): array
{
    $token = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');

    return [
        'token' => $token,
        'hash' => hash('sha256', $token),
    ];
}
