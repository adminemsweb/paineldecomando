<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class ProductRepository
{
    public function __construct(private readonly PDO $pdo) {}

    /** @return array{items:list<array<string,mixed>>,total:int} */
    public function published(int $page, int $perPage, string $search): array
    {
        $where = "status = 'published' AND deleted_at IS NULL";
        $params = [];
        if ($search !== '') { $where .= ' AND (name LIKE :search OR summary LIKE :search)'; $params['search'] = '%' . $search . '%'; }
        $count = $this->pdo->prepare("SELECT COUNT(*) FROM products WHERE {$where}");
        $count->execute($params);
        $sql = "SELECT id,name,slug,summary,featured_image AS image_url FROM products WHERE {$where} ORDER BY sort_order,name LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) $stmt->bindValue(':' . $key, $value);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', ($page - 1) * $perPage, PDO::PARAM_INT);
        $stmt->execute();
        return ['items' => $stmt->fetchAll(), 'total' => (int)$count->fetchColumn()];
    }

    public function bySlug(string $slug): array|false
    {
        $stmt = $this->pdo->prepare("SELECT id,name,slug,summary,description,features,benefits,voltages,power_range,protection_rating,featured_image AS image_url FROM products WHERE slug=:slug AND status='published' AND deleted_at IS NULL LIMIT 1");
        $stmt->execute(['slug' => $slug]);
        return $stmt->fetch();
    }
}

