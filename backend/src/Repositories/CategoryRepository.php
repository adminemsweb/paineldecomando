<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class CategoryRepository
{
    public function __construct(private readonly PDO $pdo) {}

    /** @return list<array<string,mixed>> */
    public function all(): array
    {
        $statement = $this->pdo->query(
            "SELECT id,parent_id,name,slug,description,status,sort_order,seo_title,seo_description,created_at,updated_at
             FROM categories
             WHERE deleted_at IS NULL
             ORDER BY
               CASE WHEN parent_id IS NULL THEN id ELSE parent_id END,
               CASE WHEN parent_id IS NULL THEN 0 ELSE 1 END,
               sort_order,
               name"
        );

        return array_map(static function (array $category): array {
            $category['id'] = (int)$category['id'];
            $category['parent_id'] = $category['parent_id'] === null ? null : (int)$category['parent_id'];
            $category['sort_order'] = (int)$category['sort_order'];
            return $category;
        }, $statement->fetchAll());
    }

    /** @param array<string,mixed> $data */
    public function create(array $data): array
    {
        $statement = $this->pdo->prepare('INSERT INTO categories (parent_id,name,slug,description,sort_order,status,seo_title,seo_description) VALUES (:parent_id,:name,:slug,:description,:sort_order,:status,:seo_title,:seo_description)');
        $statement->execute($this->values($data));
        return $this->byId((int)$this->pdo->lastInsertId());
    }

    /** @param array<string,mixed> $data */
    public function update(int $id, array $data): ?array
    {
        if (!$this->exists($id)) return null;
        $values = $this->values($data);
        $values['id'] = $id;
        $this->pdo->prepare('UPDATE categories SET parent_id=:parent_id,name=:name,slug=:slug,description=:description,sort_order=:sort_order,status=:status,seo_title=:seo_title,seo_description=:seo_description,updated_at=CURRENT_TIMESTAMP WHERE id=:id AND deleted_at IS NULL')->execute($values);
        return $this->byId($id);
    }

    public function delete(int $id): bool
    {
        $statement = $this->pdo->prepare('UPDATE categories SET deleted_at=CURRENT_TIMESTAMP,updated_at=CURRENT_TIMESTAMP WHERE id=:id AND deleted_at IS NULL');
        $statement->execute(['id' => $id]);
        return $statement->rowCount() > 0;
    }

    public function slugExists(string $slug, ?int $exceptId = null): bool
    {
        $sql = 'SELECT 1 FROM categories WHERE slug=:slug AND deleted_at IS NULL';
        $values = ['slug' => $slug];
        if ($exceptId !== null) { $sql .= ' AND id<>:id'; $values['id'] = $exceptId; }
        $statement = $this->pdo->prepare($sql . ' LIMIT 1');
        $statement->execute($values);
        return (bool)$statement->fetchColumn();
    }

    public function exists(int $id): bool
    {
        $statement = $this->pdo->prepare('SELECT 1 FROM categories WHERE id=:id AND deleted_at IS NULL LIMIT 1');
        $statement->execute(['id' => $id]);
        return (bool)$statement->fetchColumn();
    }

    public function hasChildren(int $id): bool
    {
        $statement = $this->pdo->prepare('SELECT 1 FROM categories WHERE parent_id=:id AND deleted_at IS NULL LIMIT 1');
        $statement->execute(['id' => $id]);
        return (bool)$statement->fetchColumn();
    }

    /** @param array<string,mixed> $data @return array<string,mixed> */
    private function values(array $data): array
    {
        $nullable = static fn(mixed $value): ?string => trim((string)$value) === '' ? null : trim((string)$value);
        return [
            'parent_id' => !empty($data['parent_id']) ? (int)$data['parent_id'] : null,
            'name' => trim((string)$data['name']),
            'slug' => trim((string)$data['slug']),
            'description' => $nullable($data['description'] ?? null),
            'sort_order' => (int)($data['sort_order'] ?? 0),
            'status' => (string)($data['status'] ?? 'draft'),
            'seo_title' => $nullable($data['seo_title'] ?? null),
            'seo_description' => $nullable($data['seo_description'] ?? null),
        ];
    }

    /** @return array<string,mixed> */
    private function byId(int $id): array
    {
        $statement = $this->pdo->prepare('SELECT id,parent_id,name,slug,description,sort_order,status,seo_title,seo_description,created_at,updated_at FROM categories WHERE id=:id AND deleted_at IS NULL');
        $statement->execute(['id' => $id]);
        $category = $statement->fetch();
        if (!is_array($category)) return [];
        $category['id'] = (int)$category['id'];
        $category['parent_id'] = $category['parent_id'] === null ? null : (int)$category['parent_id'];
        $category['sort_order'] = (int)$category['sort_order'];
        return $category;
    }
}
