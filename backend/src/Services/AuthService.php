<?php
declare(strict_types=1);

namespace App\Services;

use App\Exceptions\AuthException;
use App\Repositories\AuthRepository;
use DateTimeImmutable;
use PDOException;
use Throwable;

final class AuthService
{
    private const SESSION_DAYS = 7;

    public function __construct(private readonly AuthRepository $repository) {}

    /** @param array<string,mixed> $data @return array{user:array{id:int,name:string,email:string,company:?string,phone:string},token:string,expires:int} */
    public function register(array $data, ?string $userAgent = null): array
    {
        $email = strtolower(trim((string)$data['email']));
        if ($this->repository->findByEmail($email)) throw new AuthException('Já existe uma conta com este e-mail.', 409);
        $now = $this->now();
        $company = trim((string)($data['company'] ?? ''));
        $phone = preg_replace('/\D/', '', (string)$data['phone']) ?? '';
        try {
            $this->repository->begin();
            $userId = $this->repository->createCustomer(
                trim((string)$data['name']),
                $email,
                password_hash((string)$data['password'], PASSWORD_DEFAULT),
                $company !== '' ? $company : null,
                $phone,
                $now,
            );
            $session = $this->newSession($userId, $userAgent, $now);
            $this->repository->commit();
        } catch (PDOException $exception) {
            $this->repository->rollback();
            if ($exception->getCode() === '23000') throw new AuthException('Já existe uma conta com este e-mail.', 409);
            throw $exception;
        } catch (Throwable $exception) {
            $this->repository->rollback();
            throw $exception;
        }
        return ['user'=>['id'=>$userId,'name'=>trim((string)$data['name']),'email'=>$email,'company'=>$company !== '' ? $company : null,'phone'=>$phone,'address'=>null],'token'=>$session['token'],'expires'=>$session['expires']];
    }

    /** @param array<string,mixed> $data @return array{user:array{id:int,name:string,email:string,company:?string,phone:string},token:string,expires:int} */
    public function login(array $data, ?string $userAgent = null): array
    {
        $email = strtolower(trim((string)$data['email']));
        $user = $this->repository->findByEmail($email);
        $now = new DateTimeImmutable();
        if ($user && $user['locked_until'] && new DateTimeImmutable((string)$user['locked_until']) > $now) {
            throw new AuthException('Muitas tentativas. Aguarde 15 minutos e tente novamente.', 429);
        }
        $passwordValid = password_verify((string)$data['password'], $user ? (string)$user['password_hash'] : '$2y$10$wH/G67Q0i5YhMtz0PkVJWe1vUa9j0A3XxO2e0pLEmArMZG6huQuvu');
        if (!$user || !$passwordValid) {
            if ($user) {
                $attempts = (int)$user['failed_login_attempts'] + 1;
                $this->repository->updateFailedLogin((int)$user['id'], $attempts >= 5 ? 0 : $attempts, $attempts >= 5 ? $now->modify('+15 minutes')->format('Y-m-d H:i:s') : null, $now->format('Y-m-d H:i:s'));
            }
            throw new AuthException('E-mail ou senha incorretos.', 401);
        }
        if ((string)$user['status'] !== 'active' || !$this->repository->isCustomer((int)$user['id'])) throw new AuthException('Esta conta não possui acesso à área de clientes.', 403);

        $nowString = $now->format('Y-m-d H:i:s');
        $this->repository->recordSuccessfulLogin((int)$user['id'], $nowString);
        $session = $this->newSession((int)$user['id'], $userAgent, $nowString);
        $customer = $this->repository->findCustomerBySession(hash('sha256', $session['token']), $nowString);
        if ($customer === null) throw new AuthException('Não foi possível iniciar sua sessão.', 500);
        return ['user'=>$customer,'token'=>$session['token'],'expires'=>$session['expires']];
    }

    /** @param array<string,mixed> $data @return array{user:array{id:int,name:string,email:string,role:string},token:string,expires:int} */
    public function adminLogin(array $data, ?string $userAgent = null): array
    {
        $email = strtolower(trim((string)$data['email']));
        $user = $this->repository->findByEmail($email);
        $now = new DateTimeImmutable();
        if ($user && $user['locked_until'] && new DateTimeImmutable((string)$user['locked_until']) > $now) {
            throw new AuthException('Muitas tentativas. Aguarde 15 minutos e tente novamente.', 429);
        }
        $passwordValid = password_verify((string)$data['password'], $user ? (string)$user['password_hash'] : '$2y$10$wH/G67Q0i5YhMtz0PkVJWe1vUa9j0A3XxO2e0pLEmArMZG6huQuvu');
        if (!$user || !$passwordValid) {
            if ($user) {
                $attempts = (int)$user['failed_login_attempts'] + 1;
                $this->repository->updateFailedLogin((int)$user['id'], $attempts >= 5 ? 0 : $attempts, $attempts >= 5 ? $now->modify('+15 minutes')->format('Y-m-d H:i:s') : null, $now->format('Y-m-d H:i:s'));
            }
            throw new AuthException('E-mail ou senha incorretos.', 401);
        }
        if ((string)$user['status'] !== 'active' || !$this->repository->isAdmin((int)$user['id'])) throw new AuthException('Esta conta não possui acesso administrativo.', 403);
        $nowString = $now->format('Y-m-d H:i:s');
        $this->repository->recordSuccessfulLogin((int)$user['id'], $nowString);
        $session = $this->newSession((int)$user['id'], $userAgent, $nowString);
        $admin = $this->repository->findAdminBySession(hash('sha256', $session['token']), $nowString);
        if ($admin === null) throw new AuthException('Não foi possível iniciar sua sessão.', 500);
        return ['user'=>$admin,'token'=>$session['token'],'expires'=>$session['expires']];
    }

    /** @return array{id:int,name:string,email:string,role:string} */
    public function currentAdmin(string $token): array
    {
        if (!$this->validToken($token)) throw new AuthException('Sessão administrativa não autenticada.', 401);
        $user = $this->repository->findAdminBySession(hash('sha256', $token), $this->now());
        if ($user === null) throw new AuthException('Sua sessão expirou. Entre novamente.', 401);
        return $user;
    }

    /** @return array{id:int,name:string,email:string,company:?string,phone:string} */
    public function currentUser(string $token): array
    {
        if (!$this->validToken($token)) throw new AuthException('Sessão não autenticada.', 401);
        $user = $this->repository->findCustomerBySession(hash('sha256', $token), $this->now());
        if ($user === null) throw new AuthException('Sua sessão expirou. Entre novamente.', 401);
        return $user;
    }

    public function logout(string $token): void
    {
        if ($this->validToken($token)) $this->repository->revokeSession(hash('sha256', $token), $this->now());
    }

    /** @return array{reset_url:?string,email_found:bool,email_sent:bool} */
    public function requestPasswordReset(string $email): array
    {
        $email = strtolower(trim($email));
        $user = $this->repository->findByEmail($email);
        if (!$user || (string)$user['status'] !== 'active') return ['reset_url'=>null,'email_found'=>false,'email_sent'=>false];
        $token = bin2hex(random_bytes(32));
        $now = $this->now();
        $expires = (new DateTimeImmutable($now))->modify('+1 hour')->format('Y-m-d H:i:s');
        $this->repository->createPasswordReset((int)$user['id'], hash('sha256', $token), $expires, $now);
        $url = rtrim((string)\App\Config\Env::get('FRONTEND_URL', 'http://localhost:5173'), '/') . '/conta/redefinir-senha?token=' . $token;
        $sent = (new PasswordResetMailer())->send($email, $url);
        return ['reset_url'=>$url,'email_found'=>true,'email_sent'=>$sent];
    }

    public function resetPassword(string $token, string $password): void
    {
        $reset = $this->repository->findPasswordReset(hash('sha256', $token), $this->now());
        if ($reset === null) throw new AuthException('Este link é inválido, já foi utilizado ou expirou.', 422);
        $now = $this->now();
        try {
            $this->repository->begin();
            $this->repository->completePasswordReset($reset['id'], $reset['user_id'], password_hash($password, PASSWORD_DEFAULT), $now);
            $this->repository->commit();
        } catch (Throwable $exception) {
            $this->repository->rollback();
            throw $exception;
        }
    }

    /** @param array<string,mixed> $data @return array<string,mixed> */
    public function updateProfile(string $token, array $data): array
    {
        $current = $this->currentUser($token);
        $now = $this->now();
        $company = trim((string)($data['company'] ?? ''));
        $address = is_array($data['address'] ?? null) ? $data['address'] : null;
        $normalized = $address === null ? null : [
            'postalCode'=>preg_replace('/\D/', '', (string)($address['postalCode'] ?? '')) ?? '',
            'street'=>trim((string)($address['street'] ?? '')),
            'number'=>trim((string)($address['number'] ?? '')),
            'complement'=>trim((string)($address['complement'] ?? '')) ?: null,
            'district'=>trim((string)($address['district'] ?? '')),
            'city'=>trim((string)($address['city'] ?? '')),
            'state'=>strtoupper(trim((string)($address['state'] ?? ''))),
        ];
        try {
            $this->repository->begin();
            $this->repository->updateCustomer((int)$current['id'], trim((string)$data['name']), $company !== '' ? $company : null, preg_replace('/\D/', '', (string)$data['phone']) ?? '', $normalized, $now);
            $this->repository->commit();
        } catch (Throwable $exception) {
            $this->repository->rollback();
            throw $exception;
        }
        return $this->currentUser($token);
    }

    /** @return array{token:string,expires:int} */
    private function newSession(int $userId, ?string $userAgent, string $now): array
    {
        $token = bin2hex(random_bytes(32));
        $expires = (new DateTimeImmutable($now))->modify('+' . self::SESSION_DAYS . ' days');
        $this->repository->createSession($userId, hash('sha256', $token), $expires->format('Y-m-d H:i:s'), $now, $userAgent !== null ? substr($userAgent, 0, 500) : null);
        return ['token'=>$token,'expires'=>$expires->getTimestamp()];
    }

    private function validToken(string $token): bool { return strlen($token) === 64 && ctype_xdigit($token); }
    private function now(): string { return (new DateTimeImmutable())->format('Y-m-d H:i:s'); }
}
