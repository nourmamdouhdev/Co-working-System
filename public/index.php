<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\ReceiptController;
use App\Controllers\StaffController;
use App\Core\Auth;
use App\Core\Router;
$router = new Router();

$router->get('/', static function (array $params = []): void {
    if (Auth::check()) {
        redirect('/checkin');
    }

    redirect('/login');
});

$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->post('/logout', [AuthController::class, 'logout']);

$router->get('/checkin', [StaffController::class, 'showCheckinForm']);
$router->post('/checkin', [StaffController::class, 'checkin']);

$router->get('/checkout', [StaffController::class, 'showCheckout']);
$router->get('/checkout/search', [StaffController::class, 'showCheckout']);
$router->post('/checkout/{visit_id}/finalize', [StaffController::class, 'finalizeCheckout']);

$router->get('/visits/active', [StaffController::class, 'listActiveVisits']);

$router->get('/admin/dashboard/daily', [AdminController::class, 'dailyDashboard']);
$router->get('/admin/staff', [AdminController::class, 'staff']);
$router->post('/admin/staff', [AdminController::class, 'staff']);
$router->get('/admin/products', [AdminController::class, 'products']);
$router->post('/admin/products', [AdminController::class, 'products']);
$router->get('/admin/settings', [AdminController::class, 'settings']);
$router->post('/admin/settings', [AdminController::class, 'settings']);

$router->get('/receipt/{payment_id}', [ReceiptController::class, 'show']);

$router->dispatch(request_method(), request_uri_path());
