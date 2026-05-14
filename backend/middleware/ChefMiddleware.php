<?php
namespace App\Middleware;

class ChefMiddleware extends AuthMiddleware
{
    protected ?string $requiredRole = 'chef';
}
