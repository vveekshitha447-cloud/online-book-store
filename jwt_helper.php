<?php
// ─── jwt_helper.php ───────────────────────────────────────────────────────────
// Place this file inside your api/ folder.
// Include it in any file that needs JWT encode/decode or auth checking.

define('JWT_SECRET', 'CHANGE_THIS_TO_A_LONG_RANDOM_STRING_IN_PRODUCTION');
define('JWT_EXPIRY', 60 * 60 * 24); // 24 hours

function jwt_encode(array $payload): string {
    $payload['iat'] = time();
    $payload['exp'] = time() + JWT_EXPIRY;

    $header  = _b64(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    $payload = _b64(json_encode($payload));
    $sig     = _b64(hash_hmac('sha256', "$header.$payload", JWT_SECRET, true));

    return "$header.$payload.$sig";
}

function jwt_decode(string $token): ?array {
    $parts = explode('.', $token);
    if (count($parts) !== 3) return null;

    [$header, $payload, $sig] = $parts;
    $expected = _b64(hash_hmac('sha256', "$header.$payload", JWT_SECRET, true));
    if (!hash_equals($expected, $sig)) return null;

    $data = json_decode(_b64_decode($payload), true);
    if (!$data || $data['exp'] < time()) return null;

    return $data;
}

// Call this at the top of any protected route
// Returns the decoded token payload (user id, email, role)
function require_auth(): array {
    $headers = getallheaders();
    $auth    = $headers['Authorization'] ?? $headers['authorization'] ?? '';

    if (!$auth || !str_starts_with($auth, 'Bearer ')) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized. Send: Authorization: Bearer <token>']);
        exit;
    }

    $payload = jwt_decode(trim(substr($auth, 7)));
    if (!$payload) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Token invalid or expired. Please login again.']);
        exit;
    }

    return $payload;
}

// Call this for admin-only routes
function require_admin(): array {
    $payload = require_auth();
    if (($payload['role'] ?? '') !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Forbidden. Admins only.']);
        exit;
    }
    return $payload;
}

function _b64(string $data): string {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function _b64_decode(string $data): string {
    return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
}