<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Exceptions\AuthException;
use App\Repositories\CategoryRepository;
use App\Services\AuthService;
use PDOException;

final class AdminCategoryController
{
    private const COOKIE = 'painel_session';

    public function __construct(private readonly CategoryRepository $categories, private readonly AuthService $auth) {}

    public function index(Request $request): never
    {
        $this->admin($request);
        Response::success($this->categories->all(), 'Categorias encontradas.');
    }

    public function store(Request $request): never
    {
        $this->admin($request);
        $data = $request->body();
        $this->validate($data);
        try { Response::success($this->categories->create($data), 'Categoria criada com sucesso.', 201); }
        catch (PDOException $exception) { $this->databaseError($exception); }
    }

    /** @param array{id:string} $params */
    public function update(Request $request, array $params): never
    {
        $this->admin($request);
        $id = (int)$params['id'];
        $data = $request->body();
        $this->validate($data, $id);
        try {
            $category = $this->categories->update($id, $data);
            if ($category === null) Response::error('Categoria não encontrada.', 404);
            Response::success($category, 'Categoria atualizada com sucesso.');
        } catch (PDOException $exception) { $this->databaseError($exception); }
    }

    /** @param array{id:string} $params */
    public function destroy(Request $request, array $params): never
    {
        $this->admin($request);
        $id = (int)$params['id'];
        if ($this->categories->hasChildren($id)) Response::error('Remova ou mova as subcategorias antes de remover esta categoria.', 409);
        if (!$this->categories->delete($id)) Response::error('Categoria não encontrada.', 404);
        Response::success(null, 'Categoria removida com sucesso.');
    }

    private function admin(Request $request): void
    {
        try { $this->auth->currentAdmin($request->cookie(self::COOKIE)); }
        catch (AuthException $exception) { Response::error($exception->getMessage(), $exception->status); }
    }

    /** @param array<string,mixed> $data */
    private function validate(array $data, ?int $id = null): void
    {
        $errors = [];
        $name = trim((string)($data['name'] ?? ''));
        $slug = trim((string)($data['slug'] ?? ''));
        $status = (string)($data['status'] ?? 'draft');
        $parentId = !empty($data['parent_id']) ? (int)$data['parent_id'] : null;
        if ($name === '') $errors['name'][] = 'Informe o nome da categoria.';
        if (mb_strlen($name) > 150) $errors['name'][] = 'Use no máximo 150 caracteres.';
        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) $errors['slug'][] = 'Use somente letras minúsculas, números e hífens.';
        if ($this->categories->slugExists($slug, $id)) $errors['slug'][] = 'Este endereço já está em uso.';
        if (!in_array($status, ['draft','published','archived'], true)) $errors['status'][] = 'Status inválido.';
        if ($parentId !== null && (!$this->categories->exists($parentId) || $parentId === $id)) $errors['parent_id'][] = 'Categoria principal inválida.';
        if ($errors) Response::error('Revise os campos da categoria.', 422, $errors);
    }

    private function databaseError(PDOException $exception): never
    {
        if ($exception->getCode() === '23000' || str_contains(strtolower($exception->getMessage()), 'unique')) Response::error('Já existe uma categoria com este endereço.', 409, ['slug'=>['Este endereço já está em uso.']]);
        throw $exception;
    }
}
