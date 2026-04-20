<?php
/**
 * API route definitions.
 * All routes are mounted under /backend (see index.php + .htaccess).
 */

use App\Core\Router;
use App\Controllers\AuthController;
use App\Controllers\UserController;
use App\Controllers\CategoryController;
use App\Controllers\PlatController;
use App\Controllers\RestaurantTableController;
use App\Controllers\ReservationController;
use App\Controllers\CommandeController;
use App\Controllers\OrderItemController;
use App\Controllers\PaymentController;
use App\Controllers\MessageController;
use App\Controllers\ReclamationController;
use App\Controllers\ContactController;
use App\Controllers\AvisController;
use App\Middleware\AuthMiddleware;
use App\Middleware\AdminMiddleware;

/** @var Router $router */
$router = new Router();

// ---------- Public ----------
$router->post('/api/auth/register', [AuthController::class, 'register']);
$router->post('/api/auth/login',    [AuthController::class, 'login']);
$router->post('/api/contact',       [ContactController::class, 'store']);

// Public reads
$router->get('/api/plats',             [PlatController::class, 'index']);
$router->get('/api/plats/{id}',        [PlatController::class, 'show']);
$router->get('/api/categories',        [CategoryController::class, 'index']);
$router->get('/api/categories/{id}',   [CategoryController::class, 'show']);
$router->get('/api/tables',            [RestaurantTableController::class, 'index']);
$router->get('/api/avis',              [AvisController::class, 'index']);

// ---------- Authenticated (any role) ----------
$router->get('/api/auth/me', [AuthController::class, 'me'], [AuthMiddleware::class]);

// Reservations
$router->get   ('/api/reservations',      [ReservationController::class, 'index'],   [AuthMiddleware::class]);
$router->post  ('/api/reservations',      [ReservationController::class, 'store'],   [AuthMiddleware::class]);
$router->get   ('/api/reservations/{id}', [ReservationController::class, 'show'],    [AuthMiddleware::class]);
$router->put   ('/api/reservations/{id}', [ReservationController::class, 'update'],  [AuthMiddleware::class]);
$router->delete('/api/reservations/{id}', [ReservationController::class, 'destroy'], [AuthMiddleware::class]);

// Commandes (orders)
$router->get   ('/api/commandes',      [CommandeController::class, 'index'],   [AuthMiddleware::class]);
$router->post  ('/api/commandes',      [CommandeController::class, 'store'],   [AuthMiddleware::class]);
$router->get   ('/api/commandes/{id}', [CommandeController::class, 'show'],    [AuthMiddleware::class]);
$router->put   ('/api/commandes/{id}', [CommandeController::class, 'update'],  [AuthMiddleware::class]);
$router->delete('/api/commandes/{id}', [CommandeController::class, 'destroy'], [AuthMiddleware::class]);

// Messages
$router->get   ('/api/messages',      [MessageController::class, 'index'],   [AuthMiddleware::class]);
$router->post  ('/api/messages',      [MessageController::class, 'store'],   [AuthMiddleware::class]);
$router->get   ('/api/messages/{id}', [MessageController::class, 'show'],    [AuthMiddleware::class]);
$router->put   ('/api/messages/{id}', [MessageController::class, 'update'],  [AuthMiddleware::class]);
$router->delete('/api/messages/{id}', [MessageController::class, 'destroy'], [AuthMiddleware::class]);

// Reclamations
$router->get   ('/api/reclamations',      [ReclamationController::class, 'index'],   [AuthMiddleware::class]);
$router->post  ('/api/reclamations',      [ReclamationController::class, 'store'],   [AuthMiddleware::class]);
$router->get   ('/api/reclamations/{id}', [ReclamationController::class, 'show'],    [AuthMiddleware::class]);
$router->put   ('/api/reclamations/{id}', [ReclamationController::class, 'update'],  [AuthMiddleware::class]);
$router->delete('/api/reclamations/{id}', [ReclamationController::class, 'destroy'], [AuthMiddleware::class]);

// Avis (reviews) - users can create/update their own
$router->post  ('/api/avis',      [AvisController::class, 'store'],   [AuthMiddleware::class]);
$router->get   ('/api/avis/{id}', [AvisController::class, 'show']);
$router->put   ('/api/avis/{id}', [AvisController::class, 'update'],  [AuthMiddleware::class]);
$router->delete('/api/avis/{id}', [AvisController::class, 'destroy'], [AuthMiddleware::class]);

// ---------- Admin-only ----------
// Users
$router->get   ('/api/users',      [UserController::class, 'index'],   [AdminMiddleware::class]);
$router->post  ('/api/users',      [UserController::class, 'store'],   [AdminMiddleware::class]);
$router->get   ('/api/users/{id}', [UserController::class, 'show'],    [AdminMiddleware::class]);
$router->put   ('/api/users/{id}', [UserController::class, 'update'],  [AdminMiddleware::class]);
$router->delete('/api/users/{id}', [UserController::class, 'destroy'], [AdminMiddleware::class]);

// Plats / Categories / Tables - admin writes only
$router->post  ('/api/plats',      [PlatController::class, 'store'],   [AdminMiddleware::class]);
$router->put   ('/api/plats/{id}', [PlatController::class, 'update'],  [AdminMiddleware::class]);
$router->delete('/api/plats/{id}', [PlatController::class, 'destroy'], [AdminMiddleware::class]);

$router->post  ('/api/categories',      [CategoryController::class, 'store'],   [AdminMiddleware::class]);
$router->put   ('/api/categories/{id}', [CategoryController::class, 'update'],  [AdminMiddleware::class]);
$router->delete('/api/categories/{id}', [CategoryController::class, 'destroy'], [AdminMiddleware::class]);

$router->post  ('/api/tables',      [RestaurantTableController::class, 'store'],   [AdminMiddleware::class]);
$router->get   ('/api/tables/{id}', [RestaurantTableController::class, 'show'],    [AdminMiddleware::class]);
$router->put   ('/api/tables/{id}', [RestaurantTableController::class, 'update'],  [AdminMiddleware::class]);
$router->delete('/api/tables/{id}', [RestaurantTableController::class, 'destroy'], [AdminMiddleware::class]);

// Payments (admin only)
$router->get   ('/api/payments',      [PaymentController::class, 'index'],   [AdminMiddleware::class]);
$router->post  ('/api/payments',      [PaymentController::class, 'store'],   [AdminMiddleware::class]);
$router->get   ('/api/payments/{id}', [PaymentController::class, 'show'],    [AdminMiddleware::class]);
$router->put   ('/api/payments/{id}', [PaymentController::class, 'update'],  [AdminMiddleware::class]);
$router->delete('/api/payments/{id}', [PaymentController::class, 'destroy'], [AdminMiddleware::class]);

// Order items (admin only)
$router->get   ('/api/order-items',      [OrderItemController::class, 'index'],   [AdminMiddleware::class]);
$router->post  ('/api/order-items',      [OrderItemController::class, 'store'],   [AdminMiddleware::class]);
$router->get   ('/api/order-items/{id}', [OrderItemController::class, 'show'],    [AdminMiddleware::class]);
$router->put   ('/api/order-items/{id}', [OrderItemController::class, 'update'],  [AdminMiddleware::class]);
$router->delete('/api/order-items/{id}', [OrderItemController::class, 'destroy'], [AdminMiddleware::class]);

// Contacts (admin only - list & delete)
$router->get   ('/api/contacts',      [ContactController::class, 'index'],   [AdminMiddleware::class]);
$router->get   ('/api/contacts/{id}', [ContactController::class, 'show'],    [AdminMiddleware::class]);
$router->delete('/api/contacts/{id}', [ContactController::class, 'destroy'], [AdminMiddleware::class]);

return $router;
