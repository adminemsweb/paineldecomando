<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Config\Env;
use App\Core\Logger;
use App\Core\Request;
use App\Core\Response;
use App\Exceptions\AuthException;
use App\Services\AuthService;

final class AdminUploadController
{
    private const COOKIE = 'painel_session';
    private const IMAGE_TYPES = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif'];
    private const VIDEO_TYPES = ['video/mp4'=>'mp4','video/webm'=>'webm','video/quicktime'=>'mov'];

    public function __construct(private readonly AuthService $auth) {}

    public function store(Request $request): never
    {
        try { $admin = $this->auth->currentAdmin($request->cookie(self::COOKIE)); }
        catch (AuthException $exception) { Response::error($exception->getMessage(), $exception->status); }

        $file = $_FILES['file'] ?? null;
        if (!is_array($file) || !isset($file['tmp_name'], $file['error'], $file['size'], $file['name'])) Response::error('Selecione uma imagem ou um vídeo.', 422);
        $error = (int)$file['error'];
        if ($error !== UPLOAD_ERR_OK) Response::error($this->uploadError($error), 422);
        $temporary = (string)$file['tmp_name'];
        if (!is_uploaded_file($temporary)) Response::error('O arquivo enviado não é válido.', 422);

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($temporary) ?: '';
        $types = self::IMAGE_TYPES + self::VIDEO_TYPES;
        if (!isset($types[$mime])) {
            Logger::security('Upload rejected by MIME validation', ['mime_type'=>$mime,'size_bytes'=>(int)$file['size']]);
            Response::error('Formato não aceito. Use JPG, PNG, WEBP, GIF, MP4, WEBM ou MOV.', 422);
        }
        $kind = isset(self::IMAGE_TYPES[$mime]) ? 'image' : 'video';
        $imageLimit = (int)Env::get('UPLOAD_MAX_BYTES', '10485760');
        $videoLimit = (int)Env::get('VIDEO_UPLOAD_MAX_BYTES', '20971520');
        $limit = $kind === 'image' ? $imageLimit : $videoLimit;
        if ((int)$file['size'] > $limit) Response::error(sprintf('O arquivo excede o limite de %d MB.', (int)ceil($limit / 1048576)), 422);

        $relativeDirectory = 'uploads/products/' . date('Y/m');
        $directory = BASE_PATH . '/public/' . $relativeDirectory;
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) Response::error('Não foi possível preparar a pasta de upload.', 500);
        $filename = bin2hex(random_bytes(18)) . '.' . $types[$mime];
        $destination = $directory . '/' . $filename;
        if (!move_uploaded_file($temporary, $destination)) Response::error('Não foi possível salvar o arquivo.', 500);

        Logger::security('Administrative upload accepted', ['admin_id'=>$admin['id'],'kind'=>$kind,'mime_type'=>$mime,'size_bytes'=>(int)$file['size']]);
        Response::success([
            'url'=>'/' . $relativeDirectory . '/' . $filename,
            'kind'=>$kind,
            'mime_type'=>$mime,
            'original_name'=>basename((string)$file['name']),
            'size_bytes'=>(int)$file['size'],
        ], 'Arquivo enviado com sucesso.', 201);
    }

    private function uploadError(int $error): string
    {
        return match ($error) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'O arquivo é maior que o limite permitido pelo servidor.',
            UPLOAD_ERR_PARTIAL => 'O envio foi interrompido. Tente novamente.',
            UPLOAD_ERR_NO_FILE => 'Selecione um arquivo.',
            default => 'Não foi possível receber o arquivo.',
        };
    }
}
