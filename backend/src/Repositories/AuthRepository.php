<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;
use RuntimeException;

final class AuthRepository
{
    public function __construct(private readonly PDO $pdo) {}

    /** @return array<string,mixed>|null */
    public function findByEmail(string $email): ?array
    {
        $statement = $this->pdo->prepare('SELECT id,name,email,password_hash,status,failed_login_attempts,locked_until FROM users WHERE email = :email AND deleted_at IS NULL LIMIT 1');
        $statement->execute(['email' => $email]);
        $user = $statement->fetch();
        return is_array($user) ? $user : null;
    }

    public function createCustomer(string $name, string $email, string $passwordHash, ?string $company, string $phone, string $now): int
    {
        $statement = $this->pdo->prepare('INSERT INTO users (name,email,password_hash,status,created_at,updated_at) VALUES (:name,:email,:password_hash,\'active\',:created_at,:updated_at)');
        $statement->execute(['name'=>$name,'email'=>$email,'password_hash'=>$passwordHash,'created_at'=>$now,'updated_at'=>$now]);
        $userId = (int)$this->pdo->lastInsertId();
        $roleId = $this->pdo->query("SELECT id FROM roles WHERE slug = 'customer' AND status = 'active' LIMIT 1")->fetchColumn();
        if (!$roleId) throw new RuntimeException('O papel de cliente não foi configurado. Execute as migrações.');
        $role = $this->pdo->prepare('INSERT INTO user_roles (user_id,role_id,created_at) VALUES (:user_id,:role_id,:created_at)');
        $role->execute(['user_id'=>$userId,'role_id'=>(int)$roleId,'created_at'=>$now]);
        $profile = $this->pdo->prepare('INSERT INTO customer_profiles (user_id,company_name,phone,lgpd_consent_at,created_at,updated_at) VALUES (:user_id,:company_name,:phone,:consent_at,:created_at,:updated_at)');
        $profile->execute(['user_id'=>$userId,'company_name'=>$company,'phone'=>$phone,'consent_at'=>$now,'created_at'=>$now,'updated_at'=>$now]);
        return $userId;
    }

    public function isCustomer(int $userId): bool
    {
        $statement = $this->pdo->prepare("SELECT 1 FROM user_roles ur INNER JOIN roles r ON r.id=ur.role_id WHERE ur.user_id=:user_id AND r.slug='customer' AND r.status='active' LIMIT 1");
        $statement->execute(['user_id'=>$userId]);
        return (bool)$statement->fetchColumn();
    }

    public function updateFailedLogin(int $userId, int $attempts, ?string $lockedUntil, string $now): void
    {
        $statement = $this->pdo->prepare('UPDATE users SET failed_login_attempts=:attempts,locked_until=:locked_until,updated_at=:updated_at WHERE id=:id');
        $statement->execute(['attempts'=>$attempts,'locked_until'=>$lockedUntil,'updated_at'=>$now,'id'=>$userId]);
    }

    public function recordSuccessfulLogin(int $userId, string $now): void
    {
        $statement = $this->pdo->prepare('UPDATE users SET failed_login_attempts=0,locked_until=NULL,last_login_at=:last_login_at,updated_at=:updated_at WHERE id=:id');
        $statement->execute(['last_login_at'=>$now,'updated_at'=>$now,'id'=>$userId]);
    }

    public function createSession(int $userId, string $tokenHash, string $expiresAt, string $now, ?string $userAgent): void
    {
        $statement = $this->pdo->prepare('INSERT INTO user_sessions (user_id,token_hash,expires_at,last_used_at,user_agent,created_at) VALUES (:user_id,:token_hash,:expires_at,:last_used_at,:user_agent,:created_at)');
        $statement->execute(['user_id'=>$userId,'token_hash'=>$tokenHash,'expires_at'=>$expiresAt,'last_used_at'=>$now,'user_agent'=>$userAgent,'created_at'=>$now]);
    }

    /** @return array<string,mixed>|null */
    public function findCustomerBySession(string $tokenHash, string $now): ?array
    {
        $statement = $this->pdo->prepare("SELECT u.id,u.name,u.email,cp.company_name AS company,cp.phone,a.recipient_name,a.postal_code,a.street,a.number,a.complement,a.district,a.city,a.state FROM user_sessions s INNER JOIN users u ON u.id=s.user_id INNER JOIN customer_profiles cp ON cp.user_id=u.id INNER JOIN user_roles ur ON ur.user_id=u.id INNER JOIN roles r ON r.id=ur.role_id AND r.slug='customer' LEFT JOIN customer_addresses a ON a.user_id=u.id AND a.is_default=1 WHERE s.token_hash=:token_hash AND s.revoked_at IS NULL AND s.expires_at>:now AND u.status='active' AND u.deleted_at IS NULL ORDER BY a.id DESC LIMIT 1");
        $statement->execute(['token_hash'=>$tokenHash,'now'=>$now]);
        $user = $statement->fetch();
        if (!is_array($user)) return null;
        $touch = $this->pdo->prepare('UPDATE user_sessions SET last_used_at=:now WHERE token_hash=:token_hash');
        $touch->execute(['now'=>$now,'token_hash'=>$tokenHash]);
        $address = $user['postal_code'] === null ? null : ['recipientName'=>(string)$user['recipient_name'],'postalCode'=>(string)$user['postal_code'],'street'=>(string)$user['street'],'number'=>(string)$user['number'],'complement'=>$user['complement'] !== null ? (string)$user['complement'] : null,'district'=>(string)$user['district'],'city'=>(string)$user['city'],'state'=>(string)$user['state']];
        return ['id'=>(int)$user['id'],'name'=>(string)$user['name'],'email'=>(string)$user['email'],'company'=>$user['company'] !== null ? (string)$user['company'] : null,'phone'=>(string)$user['phone'],'address'=>$address];
    }

    /** @param array<string,mixed>|null $address */
    public function updateCustomer(int $userId, string $name, ?string $company, string $phone, ?array $address, string $now): void
    {
        $user = $this->pdo->prepare('UPDATE users SET name=:name,updated_at=:updated_at WHERE id=:id');
        $user->execute(['name'=>$name,'updated_at'=>$now,'id'=>$userId]);
        $profile = $this->pdo->prepare('UPDATE customer_profiles SET company_name=:company,phone=:phone,updated_at=:updated_at WHERE user_id=:user_id');
        $profile->execute(['company'=>$company,'phone'=>$phone,'updated_at'=>$now,'user_id'=>$userId]);
        if ($address === null) return;
        $existing = $this->pdo->prepare('SELECT id FROM customer_addresses WHERE user_id=:user_id AND is_default=1 ORDER BY id DESC LIMIT 1');
        $existing->execute(['user_id'=>$userId]);
        $addressId = $existing->fetchColumn();
        $values = ['user_id'=>$userId,'recipient_name'=>$name,'postal_code'=>$address['postalCode'],'street'=>$address['street'],'number'=>$address['number'],'complement'=>$address['complement'],'district'=>$address['district'],'city'=>$address['city'],'state'=>$address['state'],'updated_at'=>$now];
        if ($addressId) {
            $statement = $this->pdo->prepare('UPDATE customer_addresses SET recipient_name=:recipient_name,postal_code=:postal_code,street=:street,number=:number,complement=:complement,district=:district,city=:city,state=:state,updated_at=:updated_at WHERE id=:id AND user_id=:user_id');
            $statement->execute([...$values,'id'=>(int)$addressId]);
        } else {
            $statement = $this->pdo->prepare("INSERT INTO customer_addresses (user_id,label,recipient_name,postal_code,street,number,complement,district,city,state,is_default,created_at,updated_at) VALUES (:user_id,'Principal',:recipient_name,:postal_code,:street,:number,:complement,:district,:city,:state,1,:created_at,:updated_at)");
            $statement->execute([...$values,'created_at'=>$now]);
        }
    }

    public function revokeSession(string $tokenHash, string $now): void
    {
        $statement = $this->pdo->prepare('UPDATE user_sessions SET revoked_at=:now WHERE token_hash=:token_hash AND revoked_at IS NULL');
        $statement->execute(['now'=>$now,'token_hash'=>$tokenHash]);
    }

    public function begin(): void { $this->pdo->beginTransaction(); }
    public function commit(): void { $this->pdo->commit(); }
    public function rollback(): void { if ($this->pdo->inTransaction()) $this->pdo->rollBack(); }
}
