<?php
namespace App\Middleware;

class EmployeeMiddleware extends AuthMiddleware
{
    protected ?string $requiredRole = 'employee';
}
