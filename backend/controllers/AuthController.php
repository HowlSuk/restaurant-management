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
    // 1. Pull data from $_POST (FormData sends data as POST, not JSON)
    $data = [
        'name'     => $_POST['name'] ?? '',
        'email'    => $_POST['email'] ?? '',
        'password' => $_POST['password'] ?? '',
    ];

    // 2. Run your existing validation
    $errors = $this->validate($data, [
        'name'     => 'required|min:2|max:50',
        'email'    => 'required|email',
        'password' => 'required|min:8', 
    ]);

    // If validation fails, return the errors
    if ($errors) {
        Response::error('Validation failed', 422, $errors);
    }

    $users = new User();
    
    // Check if email exists
    if ($users->findByEmail($data['email'])) {
        Response::error('Email is already registered', 409);
    }

    // 3. Setup the default profile picture
    $db_relative_path = 'assets/uploads/default.png';

    // 4. Handle File Upload (ONLY if a file was actually selected)
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['profile_picture']['tmp_name'];
        $fileName    = $_FILES['profile_picture']['name'];
        $fileSize    = $_FILES['profile_picture']['size'];
        
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

        // Validate File Type
        if (!in_array($fileExtension, $allowedExtensions)) {
            Response::error('Invalid image type. Use JPG, PNG or WEBP.', 400);
        }

        // Validate File Size (2MB)
        if ($fileSize > 2 * 1024 * 1024) {
            Response::error('Image is too large (Max 2MB).', 400);
        }

        // Create unique name
        $newFileName = uniqid('avatar_', true) . '.' . $fileExtension;
        
        // Target path
        $uploadFileDir = __DIR__ . '/../assets/uploads/';
        
        // Create directory if missing
        if (!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0755, true);
        }

        if (move_uploaded_file($fileTmpPath, $uploadFileDir . $newFileName)) {
            $db_relative_path = 'assets/uploads/' . $newFileName;
        }
    }

    // 5. Finalize user creation
    $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);

    $id = $users->create([
        'name'            => htmlspecialchars(trim($data['name'])),
        'email'           => filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL),
        'password'        => $hashedPassword,
        'role'            => 'client', 
        'profile_picture' => $db_relative_path
    ]);

    if (!$id) {
        Response::error('Registration failed', 500);
    }

    Response::success(['id' => $id], 'User registered successfully', 201);
}

    public function login(): void
    {
        $data   = $this->input(); // Stays original because logins send plain text/json
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

        $cfg = require __DIR__ . '/../config/config.php';
        
        // Include the profile picture inside your token pass data block so you can fetch it on frontend instantly!
        $token = JWT::encode([
            'sub'             => (int) $user['id'],
            'name'            => $user['name'],
            'email'           => $user['email'],
            'role'            => $user['role'],
            'profile_picture' => $user['profile_picture'] ?? 'assets/uploads/default.png',
            'iat'             => time(),
            'exp'             => time() + $cfg['jwt']['ttl'],
            'iss'             => $cfg['jwt']['issuer'],
        ], $cfg['jwt']['secret']);

        Response::success([
            'token' => $token,
            'user'  => [
                'id'              => (int) $user['id'],
                'name'            => $user['name'],
                'email'           => $user['email'],
                'role'            => $user['role'],
                'profile_picture' => $user['profile_picture'] ?? 'assets/uploads/default.png'
            ],
        ], 'Login successful');
    }

    public function me(array $params, array $ctx): void
    {
        Response::success($ctx['user'] ?? null, 'Current user');
    }
}