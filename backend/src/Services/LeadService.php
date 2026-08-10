<?php
declare(strict_types=1);

namespace App\Services;

use PDO;

final class LeadService
{
    public function __construct(private readonly PDO $pdo) {}

    /** @param array<string,mixed> $data @return array{protocol:string} */
    public function create(array $data): array
    {
        $protocol = 'ORC-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
        $stmt = $this->pdo->prepare("INSERT INTO leads (protocol,name,company,cnpj,email,phone,whatsapp,city,state,segment_id,product_id,voltage,power,quantity,desired_deadline,description,lgpd_consent,status,source,created_at,updated_at) VALUES (:protocol,:name,:company,:cnpj,:email,:phone,:whatsapp,:city,:state,:segment_id,:product_id,:voltage,:power,:quantity,:desired_deadline,:description,1,'new','website',NOW(),NOW())");
        $stmt->execute(['protocol'=>$protocol,'name'=>trim((string)$data['name']),'company'=>self::nullable($data['company']??null),'cnpj'=>self::nullable($data['cnpj']??null),'email'=>strtolower(trim((string)$data['email'])),'phone'=>trim((string)$data['phone']),'whatsapp'=>self::nullable($data['whatsapp']??null),'city'=>self::nullable($data['city']??null),'state'=>self::nullable($data['state']??null),'segment_id'=>self::intOrNull($data['segment_id']??null),'product_id'=>self::intOrNull($data['product_id']??null),'voltage'=>self::nullable($data['voltage']??null),'power'=>self::nullable($data['power']??null),'quantity'=>max(1,(int)($data['quantity']??1)),'desired_deadline'=>self::nullable($data['desired_deadline']??null),'description'=>trim((string)$data['description'])]);
        return ['protocol' => $protocol];
    }
    private static function nullable(mixed $value): ?string { $v = trim((string)$value); return $v === '' ? null : $v; }
    private static function intOrNull(mixed $value): ?int { return is_numeric($value) && (int)$value > 0 ? (int)$value : null; }
}

