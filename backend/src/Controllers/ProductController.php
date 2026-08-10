<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\ProductRepository;

final class ProductController
{
    public function __construct(private readonly ProductRepository $repository) {}

    public function index(Request $request): never
    {
        $query = $request->query();
        $page = max(1, (int)($query['page'] ?? 1));
        $perPage = min(50, max(1, (int)($query['per_page'] ?? 12)));
        $result = $this->repository->published($page, $perPage, $query['search'] ?? '');
        Response::success($result['items'], 'Produtos encontrados.', 200, ['page' => $page, 'per_page' => $perPage, 'total' => $result['total'], 'total_pages' => (int)ceil($result['total'] / $perPage)]);
    }

    /** @param array{slug:string} $params */
    public function show(Request $request, array $params): never
    {
        unset($request);
        $product = $this->repository->bySlug($params['slug']);
        if (!$product) Response::error('Produto não encontrado.', 404);
        Response::success($product);
    }
}

