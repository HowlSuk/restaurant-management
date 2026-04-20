<?php
namespace App\Middleware;

class AdminMiddleware extends AuthMiddleware
{
    protected ?string $requiredRole = 'admin';
}
