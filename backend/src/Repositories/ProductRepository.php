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
        $sql = "SELECT id,name,slug,summary,featured_image AS image_url,category_name,reference_code,brand,price_cents,installments,stock_status,stock_quantity,lead_time,sales_channel,warranty_days,voltages,power_range,featured FROM products WHERE {$where} ORDER BY featured DESC,sort_order,name LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) $stmt->bindValue(':' . $key, $value);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', ($page - 1) * $perPage, PDO::PARAM_INT);
        $stmt->execute();
        $items = array_map(fn (array $product): array => $this->decodeProduct($product), $stmt->fetchAll());
        return ['items' => $items, 'total' => (int)$count->fetchColumn()];
    }

    public function bySlug(string $slug): array|false
    {
        $stmt = $this->pdo->prepare("SELECT id,name,slug,summary,description,features,benefits,components,voltages,power_range,protection_rating,featured_image AS image_url,gallery_images,video_url,video_urls,category_name,reference_code,brand,model,price_cents,installments,stock_status,stock_quantity,lead_time,sales_channel,warranty_days FROM products WHERE slug=:slug AND status='published' AND deleted_at IS NULL LIMIT 1");
        $stmt->execute(['slug' => $slug]);
        $product = $stmt->fetch();
        return is_array($product) ? $this->decodeProduct($product) : false;
    }

    /** @return list<array<string,mixed>> */
    public function adminAll(string $search = ''): array
    {
        $where = 'deleted_at IS NULL';
        $params = [];
        if ($search !== '') { $where .= ' AND (name LIKE :search OR slug LIKE :search)'; $params['search'] = '%' . $search . '%'; }
        $stmt = $this->pdo->prepare("SELECT id,name,slug,summary,description,features,benefits,components,voltages,power_range,protection_rating,featured_image AS image_url,gallery_images,video_url,video_urls,category_name,reference_code,brand,model,price_cents,installments,stock_status,stock_quantity,lead_time,sales_channel,warranty_days,sort_order,featured,status,seo_title,seo_description,created_at,updated_at FROM products WHERE {$where} ORDER BY sort_order,name");
        $stmt->execute($params);
        return array_map([$this, 'decodeProduct'], $stmt->fetchAll());
    }

    /** @param array<string,mixed> $data */
    public function create(array $data, int $userId): array
    {
        $sql = 'INSERT INTO products (name,slug,summary,description,features,benefits,components,voltages,power_range,protection_rating,featured_image,gallery_images,video_url,video_urls,category_name,reference_code,brand,model,price_cents,installments,stock_status,stock_quantity,lead_time,sales_channel,warranty_days,sort_order,featured,status,published_at,seo_title,seo_description,created_by) VALUES (:name,:slug,:summary,:description,:features,:benefits,:components,:voltages,:power_range,:protection_rating,:featured_image,:gallery_images,:video_url,:video_urls,:category_name,:reference_code,:brand,:model,:price_cents,:installments,:stock_status,:stock_quantity,:lead_time,:sales_channel,:warranty_days,:sort_order,:featured,:status,:published_at,:seo_title,:seo_description,:created_by)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->writeValues($data, $userId));
        return $this->adminById((int)$this->pdo->lastInsertId());
    }

    /** @param array<string,mixed> $data */
    public function update(int $id, array $data): ?array
    {
        if (!$this->exists($id)) return null;
        $values = $this->writeValues($data, null);
        unset($values['created_by']);
        $values['id'] = $id;
        $assignments = 'name=:name,slug=:slug,summary=:summary,description=:description,features=:features,benefits=:benefits,components=:components,voltages=:voltages,power_range=:power_range,protection_rating=:protection_rating,featured_image=:featured_image,gallery_images=:gallery_images,video_url=:video_url,video_urls=:video_urls,category_name=:category_name,reference_code=:reference_code,brand=:brand,model=:model,price_cents=:price_cents,installments=:installments,stock_status=:stock_status,stock_quantity=:stock_quantity,lead_time=:lead_time,sales_channel=:sales_channel,warranty_days=:warranty_days,sort_order=:sort_order,featured=:featured,status=:status,published_at=:published_at,seo_title=:seo_title,seo_description=:seo_description,updated_at=CURRENT_TIMESTAMP';
        $this->pdo->prepare("UPDATE products SET {$assignments} WHERE id=:id AND deleted_at IS NULL")->execute($values);
        return $this->adminById($id);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('UPDATE products SET deleted_at=CURRENT_TIMESTAMP,updated_at=CURRENT_TIMESTAMP WHERE id=:id AND deleted_at IS NULL');
        $stmt->execute(['id'=>$id]);
        return $stmt->rowCount() > 0;
    }

    public function slugExists(string $slug, ?int $exceptId = null): bool
    {
        $sql = 'SELECT 1 FROM products WHERE slug=:slug AND deleted_at IS NULL';
        $params = ['slug'=>$slug];
        if ($exceptId !== null) { $sql .= ' AND id<>:id'; $params['id'] = $exceptId; }
        $stmt = $this->pdo->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);
        return (bool)$stmt->fetchColumn();
    }

    private function exists(int $id): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM products WHERE id=:id AND deleted_at IS NULL');
        $stmt->execute(['id'=>$id]);
        return (bool)$stmt->fetchColumn();
    }

    private function adminById(int $id): array
    {
        $stmt = $this->pdo->prepare('SELECT id,name,slug,summary,description,features,benefits,components,voltages,power_range,protection_rating,featured_image AS image_url,gallery_images,video_url,video_urls,category_name,reference_code,brand,model,price_cents,installments,stock_status,stock_quantity,lead_time,sales_channel,warranty_days,sort_order,featured,status,seo_title,seo_description,created_at,updated_at FROM products WHERE id=:id AND deleted_at IS NULL');
        $stmt->execute(['id'=>$id]);
        $row = $stmt->fetch();
        return $this->decodeProduct(is_array($row) ? $row : []);
    }

    /** @param array<string,mixed> $data @return array<string,mixed> */
    private function writeValues(array $data, ?int $userId): array
    {
        $nullable = static fn(mixed $value): ?string => trim((string)$value) !== '' ? trim((string)$value) : null;
        $status = (string)$data['status'];
        return [
            'name'=>trim((string)$data['name']), 'slug'=>trim((string)$data['slug']),
            'summary'=>$nullable($data['summary'] ?? null), 'description'=>$nullable($data['description'] ?? null),
            'features'=>json_encode(array_values($data['features'] ?? []), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            'benefits'=>json_encode(array_values($data['benefits'] ?? []), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            'components'=>json_encode(array_values($data['components'] ?? []), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            'voltages'=>$nullable($data['voltages'] ?? null), 'power_range'=>$nullable($data['power_range'] ?? null),
            'protection_rating'=>$nullable($data['protection_rating'] ?? null), 'featured_image'=>$nullable($data['image_url'] ?? null),
            'gallery_images'=>json_encode(array_values($data['gallery_images'] ?? []), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            'video_url'=>$nullable($data['video_urls'][0] ?? $data['video_url'] ?? null),
            'video_urls'=>json_encode(array_values(array_slice($data['video_urls'] ?? [], 0, 2)), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            'category_name'=>$nullable($data['category_name'] ?? null),
            'reference_code'=>$nullable($data['reference_code'] ?? null), 'brand'=>$nullable($data['brand'] ?? null), 'model'=>$nullable($data['model'] ?? null),
            'price_cents'=>isset($data['price_cents']) && $data['price_cents'] !== '' ? (int)$data['price_cents'] : null,
            'installments'=>max(1, (int)($data['installments'] ?? 1)), 'stock_status'=>(string)($data['stock_status'] ?? 'on_demand'),
            'stock_quantity'=>max(0, (int)($data['stock_quantity'] ?? 0)), 'lead_time'=>$nullable($data['lead_time'] ?? null),
            'sales_channel'=>(string)($data['sales_channel'] ?? 'both'), 'warranty_days'=>max(0, (int)($data['warranty_days'] ?? 365)),
            'sort_order'=>(int)($data['sort_order'] ?? 0), 'featured'=>!empty($data['featured']) ? 1 : 0,
            'status'=>$status, 'published_at'=>$status === 'published' ? date('Y-m-d H:i:s') : null,
            'seo_title'=>$nullable($data['seo_title'] ?? null), 'seo_description'=>$nullable($data['seo_description'] ?? null),
            'created_by'=>$userId,
        ];
    }

    /** @param array<string,mixed> $product @return array<string,mixed> */
    private function decodeProduct(array $product): array
    {
        foreach (['features','benefits','components','gallery_images','video_urls'] as $field) {
            $decoded = json_decode((string)($product[$field] ?? '[]'), true);
            $product[$field] = is_array($decoded) ? array_values($decoded) : [];
        }
        if (isset($product['id'])) $product['id'] = (int)$product['id'];
        if (isset($product['sort_order'])) $product['sort_order'] = (int)$product['sort_order'];
        if (isset($product['featured'])) $product['featured'] = (bool)$product['featured'];
        if (($product['video_urls'] ?? []) === [] && !empty($product['video_url'])) $product['video_urls'] = [(string)$product['video_url']];
        foreach (['price_cents','installments','stock_quantity','warranty_days'] as $field) if (isset($product[$field]) && $product[$field] !== null) $product[$field] = (int)$product[$field];
        return $product;
    }
}
