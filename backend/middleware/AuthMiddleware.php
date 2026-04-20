<?php
namespace App\Middleware;

use App\Core\JWT;
use App\Core\Response;

class AuthMiddleware
{
    /** Required role: 'admin' | 'client' | null (any authenticated user). */
    protected ?string $requiredRole = null;

    public function handle(): array
    {
        $cfg = require __DIR__ . '/../config/config.php';

        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $headers = array_change_key_case($headers, CASE_LOWER);
        $auth = $headers['authorization']
            ?? ($_SERVER['HTTP_AUTHORIZATION'] ?? ($_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? ''));

        if (!$auth || !preg_match('/Bearer\s+(.+)/i', $auth, $m)) {
            Response::error('Missing or invalid Authorization header', 401);
        }

        $payload = JWT::decode($m[1], $cfg['jwt']['secret']);
        if (!$payload) {
            Response::error('Invalid or expired token', 401);
        }

        if ($this->requiredRole && ($payload['role'] ?? null) !== $this->requiredRole) {
            Response::error('Forbidden: ' . $this->requiredRole . ' role required', 403);
        }

        return ['user' => $payload];
    }
}
