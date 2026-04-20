<?php
/**
 * Front controller / entry point for the REST API.
 *
 * URL layout (when placed inside XAMPP htdocs/restaurant-management):
 *   http://localhost/restaurant-management/backend/api/...
 */

declare(strict_types=1);

// ---------------- Error handling ----------------
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

set_error_handler(function ($severity, $msg, $file, $line) {
    if (!(error_reporting() & $severity)) return false;
    throw new ErrorException($msg, 0, $severity, $file, $line);
});

set_exception_handler(function (Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage(),
    ]);
});

// ---------------- CORS ----------------
$cfg = require __DIR__ . '/config/config.php';
header('Access-Control-Allow-Origin: ' . $cfg['cors']['allow_origin']);
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Max-Age: 86400');

// ---------------- Autoloader ----------------
spl_autoload_register(function (string $class): void {
    // Map "App\Foo\Bar" -> "/backend/foo/Bar.php"
    if (!str_starts_with($class, 'App\\')) return;
    $relative = substr($class, 4); // drop App\
    $parts    = explode('\\', $relative);
    $file     = array_pop($parts);
    $dir      = strtolower(implode('/', $parts));
    $path     = __DIR__ . '/' . ($dir ? $dir . '/' : '') . $file . '.php';
    if (is_file($path)) require_once $path;
});

// ---------------- Seed helper ----------------
// Visit /backend/index.php?seed=1 once to (re)create the default admin account.
if (isset($_GET['seed'])) {
    $db   = App\Config\Database::connect();
    $hash = password_hash('admin123', PASSWORD_BCRYPT);
    $db->prepare("DELETE FROM users WHERE email = :e")
       ->execute([':e' => 'admin@restaurant.com']);
    $db->prepare("INSERT INTO users (name, email, password, role) VALUES (:n, :e, :p, 'admin')")
       ->execute([':n' => 'Admin', ':e' => 'admin@restaurant.com', ':p' => $hash]);
    App\Core\Response::success(null, 'Seeded default admin (admin@restaurant.com / admin123)');
}

// ---------------- Dispatch ----------------
/** @var App\Core\Router $router */
$router = require __DIR__ . '/routes/api.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri    = $_SERVER['REQUEST_URI']    ?? '/';

// Strip the subdirectory prefix so routes written as /api/... match regardless
// of where the project is deployed (e.g. /restaurant-management/backend).
$scriptName = dirname($_SERVER['SCRIPT_NAME']); // e.g. /restaurant-management/backend
if ($scriptName && $scriptName !== '/' && str_starts_with($uri, $scriptName)) {
    $uri = substr($uri, strlen($scriptName)) ?: '/';
}

$router->dispatch($method, $uri);
