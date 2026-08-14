<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Config\Env;
use App\Core\Request;
use App\Core\Response;
use App\Exceptions\AuthException;
use App\Services\AuthService;
use App\Validators\AuthValidator;

final class AuthController
{
    private const COOKIE = 'painel_session';

    public function __construct(private readonly AuthService $service) {}

    public function register(Request $request): never
    {
        $data = $request->body();
        $errors = AuthValidator::registration($data);
        if ($errors) Response::error('Revise os dados do cadastro.', 422, $errors);
        try {
            $result = $this->service->register($data, $_SERVER['HTTP_USER_AGENT'] ?? null);
            $this->setCookie($result['token'], $result['expires']);
            Response::success($result['user'], 'Conta criada com sucesso.', 201);
        } catch (AuthException $exception) {
            Response::error($exception->getMessage(), $exception->status);
        }
    }

    public function login(Request $request): never
    {
        $data = $request->body();
        $errors = AuthValidator::login($data);
        if ($errors) Response::error('Revise os dados de acesso.', 422, $errors);
        try {
            $result = $this->service->login($data, $_SERVER['HTTP_USER_AGENT'] ?? null);
            $this->setCookie($result['token'], $result['expires']);
            Response::success($result['user'], 'Acesso realizado com sucesso.');
        } catch (AuthException $exception) {
            Response::error($exception->getMessage(), $exception->status);
        }
    }

    public function me(Request $request): never
    {
        try { Response::success($this->service->currentUser($request->cookie(self::COOKIE)), 'Sessão ativa.'); }
        catch (AuthException $exception) { Response::error($exception->getMessage(), $exception->status); }
    }

    public function logout(Request $request): never
    {
        $this->service->logout($request->cookie(self::COOKIE));
        $this->clearCookie();
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
}
