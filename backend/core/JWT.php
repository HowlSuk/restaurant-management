<?php
namespace App\Core;

/**
 * Minimal HS256 JWT implementation (no external dependencies).
 */
class JWT
{
    public static function encode(array $payload, string $secret): string
    {
        $header = ['typ' => 'JWT', 'alg' => 'HS256'];
        $h = self::b64url(json_encode($header));
        $p = self::b64url(json_encode($payload));
        $sig = self::b64url(hash_hmac('sha256', "$h.$p", $secret, true));
        return "$h.$p.$sig";
    }

    public static function decode(string $token, string $secret): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return null;
        [$h, $p, $s] = $parts;
        $expected = self::b64url(hash_hmac('sha256', "$h.$p", $secret, true));
        if (!hash_equals($expected, $s)) return null;
        $payload = json_decode(self::b64urlDecode($p), true);
        if (!is_array($payload)) return null;
        if (isset($payload['exp']) && time() > $payload['exp']) return null;
        return $payload;
    }

    private static function b64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function b64urlDecode(string $data): string
    {
        $pad = 4 - (strlen($data) % 4);
        if ($pad < 4) $data .= str_repeat('=', $pad);
        return base64_decode(strtr($data, '-_', '+/')) ?: '';
    }
}
