<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Config\Env;
use App\Core\Logger;
use App\Core\Request;
use App\Core\Response;
use App\Exceptions\AuthException;
use App\Middleware\RateLimiter;
use App\Services\AuthService;
use App\Validators\AuthValidator;

final class AuthController
{
    private const COOKIE = 'painel_session';

    public function __construct(private readonly AuthService $service) {}

    public function register(Request $request): never
    {
        RateLimiter::enforce('auth-register', 5, 3600);
        $data = $request->body();
        $errors = AuthValidator::registration($data);
        if ($errors) Response::error('Revise os dados do cadastro.', 422, $errors);
        try {
            $result = $this->service->register($data, $_SERVER['HTTP_USER_AGENT'] ?? null);
            $this->setCookie($result['token'], $result['expires']);
            Logger::security('Customer account created', ['user_id'=>$result['user']['id']]);
            Response::success($result['user'], 'Conta criada com sucesso.', 201);
        } catch (AuthException $exception) {
            Logger::security('Customer registration rejected', ['status'=>$exception->status]);
            Response::error($exception->getMessage(), $exception->status);
        }
    }

    public function login(Request $request): never
    {
        RateLimiter::enforce('auth-customer-login', 20, 900);
        $data = $request->body();
        $errors = AuthValidator::login($data);
        if ($errors) Response::error('Revise os dados de acesso.', 422, $errors);
        try {
            $result = $this->service->login($data, $_SERVER['HTTP_USER_AGENT'] ?? null);
            $this->setCookie($result['token'], $result['expires']);
            Logger::security('Customer login succeeded', ['user_id'=>$result['user']['id']]);
            Response::success($result['user'], 'Acesso realizado com sucesso.');
        } catch (AuthException $exception) {
            Logger::security('Customer login rejected', ['status'=>$exception->status,'account_id'=>$this->accountId($data)]);
            Response::error($exception->getMessage(), $exception->status);
        }
    }

    public function adminLogin(Request $request): never
    {
        RateLimiter::enforce('auth-admin-login', 10, 900);
        $data = $request->body();
        $errors = AuthValidator::login($data);
        if ($errors) Response::error('Revise os dados de acesso.', 422, $errors);
        try {
            $result = $this->service->adminLogin($data, $_SERVER['HTTP_USER_AGENT'] ?? null);
            $this->setCookie($result['token'], $result['expires']);
            Logger::security('Administrative login succeeded', ['user_id'=>$result['user']['id'],'role'=>$result['user']['role']]);
            Response::success($result['user'], 'Acesso administrativo realizado com sucesso.');
        } catch (AuthException $exception) {
            Logger::security('Administrative login rejected', ['status'=>$exception->status,'account_id'=>$this->accountId($data)]);
            Response::error($exception->getMessage(), $exception->status);
        }
    }

    public function adminMe(Request $request): never
    {
        try { Response::success($this->service->currentAdmin($request->cookie(self::COOKIE)), 'Sessão administrativa ativa.'); }
        catch (AuthException $exception) { Response::error($exception->getMessage(), $exception->status); }
    }

    public function me(Request $request): never
    {
        try { Response::success($this->service->currentUser($request->cookie(self::COOKIE)), 'Sessão ativa.'); }
        catch (AuthException $exception) {
            if ($exception->status === 401) Response::success(null, 'Nenhuma sessão de cliente ativa.');
            Response::error($exception->getMessage(), $exception->status);
        }
    }

    public function logout(Request $request): never
    {
        $this->service->logout($request->cookie(self::COOKIE));
        $this->clearCookie();
        Logger::security('Session logged out');
        Response::success(null, 'Sessão encerrada.');
    }

    public function updateProfile(Request $request): never
    {
        $data = $request->body();
        $errors = AuthValidator::profile($data);
        if ($errors) Response::error('Revise seus dados pessoais e o endereço.', 422, $errors);
        try { Response::success($this->service->updateProfile($request->cookie(self::COOKIE), $data), 'Dados atualizados com sucesso.'); }
        catch (AuthException $exception) { Response::error($exception->getMessage(), $exception->status); }
    }

    public function forgotPassword(Request $request): never
    {
        RateLimiter::enforce('auth-forgot-password', 5, 900);
        $data = $request->body();
        $errors = AuthValidator::forgotPassword($data);
        if ($errors) Response::error('Informe um e-mail válido.', 422, $errors);
        $result = $this->service->requestPasswordReset((string)$data['email']);
        Logger::security('Password reset requested', ['mail_dispatched'=>$result['email_sent']]);
        $responseData = Env::get('APP_ENV') === 'development' && $result['email_found'] ? ['development_reset_url'=>$result['reset_url'],'email_sent'=>$result['email_sent']] : null;
        Response::success($responseData, 'Se este e-mail estiver cadastrado, você receberá as instruções para criar uma nova senha.');
    }

    public function resetPassword(Request $request): never
    {
        RateLimiter::enforce('auth-reset-password', 10, 900);
        $data = $request->body();
        $errors = AuthValidator::resetPassword($data);
        if ($errors) Response::error('Revise os dados da nova senha.', 422, $errors);
        try {
            $this->service->resetPassword((string)$data['token'], (string)$data['password']);
            Logger::security('Password reset completed and sessions revoked');
            Response::success(null, 'Senha alterada com sucesso. Entre com sua nova senha.');
        } catch (AuthException $exception) {
            Logger::security('Password reset rejected', ['status'=>$exception->status]);
            Response::error($exception->getMessage(), $exception->status);
        }
    }

    private function setCookie(string $token, int $expires): void
    {
        setcookie(self::COOKIE, $token, ['expires'=>$expires,'path'=>'/','secure'=>$this->secureCookie(),'httponly'=>true,'samesite'=>'Lax']);
    }

    private function clearCookie(): void
    {
        setcookie(self::COOKIE, '', ['expires'=>time()-3600,'path'=>'/','secure'=>$this->secureCookie(),'httponly'=>true,'samesite'=>'Lax']);
    }

    private function secureCookie(): bool
    {
        return Env::get('APP_ENV') === 'production' || (($_SERVER['HTTPS'] ?? '') !== '' && ($_SERVER['HTTPS'] ?? '') !== 'off');
    }

    /** @param array<string,mixed> $data */
    private function accountId(array $data): string
    {
        return substr(hash('sha256', strtolower(trim((string)($data['email'] ?? '')))), 0, 12);
    }
}
