<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Logger;
use App\Core\Request;
use App\Core\Response;
use App\Exceptions\AuthException;
use App\Repositories\ProductRepository;
use App\Services\AuthService;
use App\Validators\ProductValidator;
use PDOException;

final class AdminProductController
{
    private const COOKIE = 'painel_session';
    public function __construct(private readonly ProductRepository $products, private readonly AuthService $auth) {}

    public function index(Request $request): never
    {
        $this->admin($request);
        Response::success($this->products->adminAll($request->query()['search'] ?? ''), 'Produtos encontrados.');
    }

    public function store(Request $request): never
    {
        $admin = $this->admin($request);
        $data = $request->body();
        $this->validate($data);
        try {
            $product = $this->products->create($data, $admin['id']);
            Logger::security('Administrative product created', ['admin_id'=>$admin['id'],'product_id'=>$product['id'] ?? null]);
            Response::success($product, 'Produto criado com sucesso.', 201);
        }
        catch (PDOException $exception) { $this->databaseError($exception); }
    }

    /** @param array{id:string} $params */
    public function update(Request $request, array $params): never
    {
        $admin = $this->admin($request);
        $id = (int)$params['id'];
        $data = $request->body();
        $this->validate($data, $id);
        try {
            $product = $this->products->update($id, $data);
            if ($product === null) Response::error('Produto não encontrado.', 404);
            Logger::security('Administrative product updated', ['admin_id'=>$admin['id'],'product_id'=>$id]);
            Response::success($product, 'Produto atualizado com sucesso.');
        } catch (PDOException $exception) { $this->databaseError($exception); }
    }

    /** @param array{id:string} $params */
    public function destroy(Request $request, array $params): never
    {
        $admin = $this->admin($request);
        $id = (int)$params['id'];
        if (!$this->products->delete($id)) Response::error('Produto não encontrado.', 404);
        Logger::security('Administrative product deleted', ['admin_id'=>$admin['id'],'product_id'=>$id]);
        Response::success(null, 'Produto removido com sucesso.');
    }

    /** @return array{id:int,name:string,email:string,role:string} */
    private function admin(Request $request): array
    {
        try { return $this->auth->currentAdmin($request->cookie(self::COOKIE)); }
        catch (AuthException $exception) { Response::error($exception->getMessage(), $exception->status); }
    }

    /** @param array<string,mixed> $data */
    private function validate(array $data, ?int $id = null): void
    {
        $errors = ProductValidator::validate($data);
        $slug = trim((string)($data['slug'] ?? ''));
        if ($slug !== '' && $this->products->slugExists($slug, $id)) $errors['slug'][] = 'Este endereço já está em uso.';
        if ($errors) Response::error('Revise os campos do produto.', 422, $errors);
    }

    private function databaseError(PDOException $exception): never
    {
        if ($exception->getCode() === '23000' || str_contains(strtolower($exception->getMessage()), 'unique')) Response::error('Já existe um produto com este endereço.', 409, ['slug'=>['Este endereço já está em uso.']]);
        throw $exception;
    }
}
