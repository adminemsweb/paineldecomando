<?php
declare(strict_types=1);

use App\Controllers\ProductController;
use App\Controllers\ShippingController;
use App\Core\Request;
use App\Core\Response;
use App\Database\Connection;
use App\Repositories\ProductRepository;
use App\Services\LeadService;
use App\Services\CorreiosService;
use App\Services\CepService;
use App\Validators\LeadValidator;

$router->get('/api/v1/health', static fn() => Response::success(['service' => 'site-industrial-api', 'version' => '1.0.0'], 'API disponível.'));

$router->get('/api/v1/products', static function (Request $request) {
    (new ProductController(new ProductRepository(Connection::get())))->index($request);
});
$router->get('/api/v1/products/{slug}', static function (Request $request, array $params) {
    (new ProductController(new ProductRepository(Connection::get())))->show($request, $params);
});

foreach (['categories','segments','services','projects','posts'] as $resource) {
    $router->get("/api/v1/{$resource}", static fn() => Response::success([], 'Base funcional: conteúdo será cadastrado na etapa correspondente.', 200, ['page'=>1,'per_page'=>12,'total'=>0,'total_pages'=>0]));
    $router->get("/api/v1/{$resource}/{slug}", static fn(Request $request, array $params) => Response::error(ucfirst($resource) . ' não encontrado.', 404));
}

$router->post('/api/v1/leads', static function (Request $request) {
    $data = $request->body();
    $errors = LeadValidator::validate($data);
    if ($errors) Response::error('Revise os campos informados.', 422, $errors);
    $result = (new LeadService(Connection::get()))->create($data);
    Response::success($result, 'Solicitação recebida com sucesso.', 201);
});

$router->post('/api/v1/shipping/quote', static function (Request $request) {
    (new ShippingController(new CorreiosService()))->quote($request);
});
$router->post('/api/v1/shipping/cep', static function (Request $request) {
    (new ShippingController(new CorreiosService(), new CepService()))->lookupCep($request);
});

$router->post('/api/v1/contacts', static fn() => Response::error('Endpoint reservado para a Etapa 3.', 501));
$router->post('/api/v1/auth/login', static fn() => Response::error('Autenticação será habilitada na Etapa 4.', 501));
$router->post('/api/v1/auth/logout', static fn() => Response::success(null, 'Sessão encerrada.'));
$router->post('/api/v1/auth/forgot-password', static fn() => Response::error('Recuperação será habilitada na Etapa 4.', 501));
