<?php
declare(strict_types=1);

use App\Controllers\AdminProductController;
use App\Controllers\AdminUploadController;
use App\Controllers\AuthController;
use App\Core\Request;
use App\Core\Response;
use App\Database\Connection;
use App\Repositories\AuthRepository;
use App\Repositories\ProductRepository;
use App\Services\AuthService;

$adminAuthService = static fn() => new AuthService(new AuthRepository(Connection::get()));
$adminAuthController = static fn() => new AuthController($adminAuthService());
$adminProducts = static fn() => new AdminProductController(new ProductRepository(Connection::get()), $adminAuthService());
$adminUploads = static fn() => new AdminUploadController($adminAuthService());

$router->post('/api/v1/admin/auth/login', static fn(Request $request) => $adminAuthController()->adminLogin($request));
$router->get('/api/v1/admin/auth/me', static fn(Request $request) => $adminAuthController()->adminMe($request));
$router->post('/api/v1/admin/auth/logout', static fn(Request $request) => $adminAuthController()->logout($request));
$router->get('/api/v1/admin/products', static fn(Request $request) => $adminProducts()->index($request));
$router->post('/api/v1/admin/products', static fn(Request $request) => $adminProducts()->store($request));
$router->put('/api/v1/admin/products/{id}', static fn(Request $request, array $params) => $adminProducts()->update($request, $params));
$router->delete('/api/v1/admin/products/{id}', static fn(Request $request, array $params) => $adminProducts()->destroy($request, $params));
$router->post('/api/v1/admin/uploads', static fn(Request $request) => $adminUploads()->store($request));

foreach (['categories','segments','services','projects','posts','leads','settings'] as $resource) {
    $router->get("/api/v1/admin/{$resource}", static fn() => Response::error('Módulo ainda não habilitado.', 501));
}
