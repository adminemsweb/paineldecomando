<?php
declare(strict_types=1);

use App\Core\Response;

$router->get('/api/v1/admin/dashboard', static fn() => Response::error('Autenticação administrativa necessária.', 401));
foreach (['products','categories','segments','services','projects','posts','leads','settings'] as $resource) {
    $router->get("/api/v1/admin/{$resource}", static fn() => Response::error('Autenticação administrativa necessária.', 401));
    $router->post("/api/v1/admin/{$resource}", static fn() => Response::error('Autenticação administrativa necessária.', 401));
    $router->put("/api/v1/admin/{$resource}/{id}", static fn() => Response::error('Autenticação administrativa necessária.', 401));
    $router->delete("/api/v1/admin/{$resource}/{id}", static fn() => Response::error('Autenticação administrativa necessária.', 401));
}

