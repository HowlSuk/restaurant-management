<?php
namespace App\Middleware;

use App\Core\Response;

class StaffMiddleware extends AuthMiddleware
{
    public function handle(): array
    {
        $ctx = parent::handle();
        $role = $ctx['user']['role'] ?? '';
        if (!in_array($role, ['chef', 'employee'], true)) {
            Response::error('Forbidden: staff role required', 403);
        }
        return $ctx;
    }
}
