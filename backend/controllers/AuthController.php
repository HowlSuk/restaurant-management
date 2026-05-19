<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\JWT;
use App\Core\Response;
use App\Models\User;

class AuthController extends Controller
{
    public function register(): void
{
    $data = $this->input();

    //(i Added 'max' and stronger requirements)
    $errors = $this->validate($data, [
        'name'     => 'required|min:2|max:50',
        'email'    => 'required|email',
        'password' => 'required|min:8', 
    ]);

    if ($errors) Response::error('Validation failed', 422, $errors);

    
    $cleanName  = htmlspecialchars(trim($data['name']));
    $cleanEmail = filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL);

    $users = new User();
    
    
    if ($users->findByEmail($cleanEmail)) {
        Response::error('Email is already registered', 409);
    }

    
    $options = ['cost' => 12];
    $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT, $options);

    $id = $users->create([
        'name'     => $cleanName,
        'email'    => $cleanEmail,
        'password' => $hashedPassword,
        'role'     => 'client', 
    ]);

    if (!$id) {
        Response::error('Registration failed', 500);
    }

    Response::success(['id' => $id], 'User registered successfully', 201);
}

    public function login(): void
    {
        $data   = $this->input();
        $errors = $this->validate($data, [
            'email'    => 'required|email',
            'password' => 'required',
        ]);
        if ($errors) Response::error('Validation failed', 422, $errors);

        $users = new User();
        $user  = $users->findByEmail($data['email']);

        if (!$user || !password_verify($data['password'], $user['password'])) {
            Response::error('Invalid email or password', 401);
        }

        //if user is valid, create a JWT token (a digital pass) that contains the user's ID, name, email, and role. This token is signed with a secret key from the config, so it can't be tampered with. The token also has an expiration time (TTL) to enhance security.
        $cfg = require __DIR__ . '/../config/config.php';
        $token = JWT::encode([
            'sub'   => (int) $user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'role'  => $user['role'],
            'iat'   => time(),
            'exp'   => time() + $cfg['jwt']['ttl'],
            'iss'   => $cfg['jwt']['issuer'],
        ], $cfg['jwt']['secret']);

        Response::success([
            'token' => $token,
            'user'  => [
                'id'    => (int) $user['id'],
                'name'  => $user['name'],
                'email' => $user['email'],
                'role'  => $user['role'],
            ],
        ], 'Login successful');
    }

    public function me(array $params, array $ctx): void
    {
        Response::success($ctx['user'] ?? null, 'Current user');
    }
}
