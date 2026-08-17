<?php
declare(strict_types=1);

namespace App\Services;

use App\Config\Env;

final class PasswordResetMailer
{
    public function send(string $recipient, string $resetUrl): bool
    {
        $from = Env::get('MAIL_FROM', 'sistema@paineldecomando.com.br');
        $fromName = Env::get('MAIL_FROM_NAME', 'Painel de Comando');
        $subject = 'Recuperação de senha — Painel de Comando';
        $publicSiteUrl = rtrim((string)Env::get('PUBLIC_SITE_URL', 'https://paineldecomando.com.br'), '/');

        $template = <<<'HTML'
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Recuperação de senha</title>
</head>
<body style="margin:0;padding:0;background:#edf2f5;font-family:Arial,Helvetica,sans-serif;color:#172f3f">
  <div style="display:none;max-height:0;overflow:hidden;opacity:0">Seu link seguro para criar uma nova senha na Painel de Comando.</div>
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#edf2f5">
    <tr><td align="center" style="padding:42px 16px">
      <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:680px;background:#ffffff;border:1px solid #d8e1e6;border-radius:14px;overflow:hidden;box-shadow:0 16px 40px rgba(23,47,63,.10)">
        <tr><td style="height:7px;background:#f5bd00;font-size:0;line-height:0">&nbsp;</td></tr>
        <tr><td style="padding:25px 38px;background:#ffffff;border-bottom:1px solid #e4eaed">
          <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"><tr>
            <td valign="middle"><img src="cid:painel-logo" width="218" alt="Painel de Comando" style="display:block;width:218px;max-width:100%;height:auto;border:0"></td>
            <td align="right" valign="middle" style="color:#667983;font-size:10px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase">Segurança da conta</td>
          </tr></table>
        </td></tr>
        <tr><td style="padding:48px 46px 40px">
          <div style="margin:0 0 16px;color:#a47700;font-size:11px;font-weight:700;letter-spacing:1.7px;text-transform:uppercase">Recuperação de acesso</div>
          <h1 style="margin:0 0 20px;color:#172f3f;font-size:34px;line-height:1.16;letter-spacing:-.8px">Vamos criar uma nova senha?</h1>
          <p style="margin:0 0 12px;color:#425968;font-size:16px;line-height:1.65">Olá,</p>
          <p style="margin:0;color:#425968;font-size:16px;line-height:1.65">Recebemos uma solicitação para redefinir a senha da sua conta. Clique no botão abaixo para continuar com segurança.</p>
          <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin:32px 0"><tr><td style="border-radius:7px;background:#079447;box-shadow:0 5px 14px rgba(7,148,71,.18)">
            <a href="{{RESET_URL}}" style="display:inline-block;padding:17px 29px;color:#ffffff;font-size:15px;font-weight:700;text-decoration:none">Redefinir minha senha&nbsp;&nbsp;→</a>
          </td></tr></table>
          <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin:0 0 30px;background:#f5f8f9;border:1px solid #dce5e9;border-left:4px solid #f5bd00;border-radius:8px"><tr>
            <td style="padding:18px 20px"><strong style="display:block;margin-bottom:5px;color:#172f3f;font-size:14px">Este link expira em 1 hora</strong><span style="color:#667983;font-size:12px;line-height:1.6">Ele só pode ser usado uma vez. Após a alteração, sua senha anterior deixa de funcionar.</span></td>
          </tr></table>
          <p style="margin:0 0 9px;color:#667983;font-size:12px;line-height:1.6">Se o botão não funcionar, copie e cole este endereço no navegador:</p>
          <p style="margin:0;padding:12px 14px;background:#f6f8f9;border-radius:5px;color:#245577;font-size:11px;line-height:1.5;word-break:break-all">{{RESET_URL}}</p>
          <p style="margin:30px 0 0;padding-top:25px;border-top:1px solid #e4e9ec;color:#667983;font-size:12px;line-height:1.65"><strong style="color:#314a59">Não reconhece esta solicitação?</strong><br>Ignore esta mensagem. Sua senha atual continuará funcionando e nenhuma alteração será realizada.</p>
        </td></tr>
        <tr><td style="padding:25px 46px;background:#172f3f">
          <p style="margin:0 0 9px;color:#ffffff;font-size:12px;font-weight:700">Painel de Comando</p>
          <p style="margin:0;color:#afbec7;font-size:11px;line-height:1.65">Este é um e-mail automático de segurança. Por proteção, não encaminhe nem compartilhe este link.</p>
          <p style="margin:15px 0 0;font-size:11px;line-height:1.6"><a href="{{PRIVACY_URL}}" style="color:#f5bd00;text-decoration:none">Política de Privacidade</a><span style="color:#718895">&nbsp;&nbsp;•&nbsp;&nbsp;</span><a href="{{TERMS_URL}}" style="color:#f5bd00;text-decoration:none">Termos de Uso</a></p>
        </td></tr>
      </table>
      <p style="margin:19px 0 0;color:#7d909b;font-size:10px;line-height:1.6">© {{YEAR}} Painel de Comando. Todos os direitos reservados.<br>sistema@paineldecomando.com.br</p>
    </td></tr>
  </table>
</body>
</html>
HTML;
        $html = str_replace(
            ['{{RESET_URL}}', '{{PRIVACY_URL}}', '{{TERMS_URL}}', '{{YEAR}}'],
            [
                htmlspecialchars($resetUrl, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                htmlspecialchars($publicSiteUrl . '/politica-de-privacidade', ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                htmlspecialchars($publicSiteUrl . '/termos-de-uso', ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                date('Y'),
            ],
            $template
        );

        [$messageBody, $contentTypeHeaders] = $this->buildMessageBody($html);
        $headers = array_merge($contentTypeHeaders, [
            'From: ' . $fromName . ' <' . $from . '>',
            'Reply-To: ' . $from,
            'X-Mailer: PainelDeComando',
        ]);
        $smtpHost = Env::get('SMTP_HOST', '');
        $smtpPassword = Env::get('SMTP_PASSWORD', '');
        $sent = $smtpHost !== '' && $smtpPassword !== ''
            ? $this->sendSmtp($recipient, $subject, $messageBody, $headers, $from)
            : @mail($recipient, $subject, $messageBody, implode("\r\n", $headers));
        if (!$sent && Env::get('APP_ENV') === 'development') {
            $this->writeDevelopmentCopy($recipient, $subject, $resetUrl);
        }
        return $sent;
    }

    /** @return array{string, list<string>} */
    private function buildMessageBody(string $html): array
    {
        $logoPath = dirname(BASE_PATH) . '/frontend/public/brand/painel-de-comando-logo-v2.png';
        if (!is_file($logoPath)) {
            return [$html, ['MIME-Version: 1.0', 'Content-Type: text/html; charset=UTF-8']];
        }

        $boundary = 'related_' . bin2hex(random_bytes(12));
        $logo = chunk_split(base64_encode((string)file_get_contents($logoPath)));
        $body = '--' . $boundary . "\r\n"
            . "Content-Type: text/html; charset=UTF-8\r\n"
            . "Content-Transfer-Encoding: 8bit\r\n\r\n"
            . $html . "\r\n"
            . '--' . $boundary . "\r\n"
            . "Content-Type: image/png; name=\"painel-de-comando.png\"\r\n"
            . "Content-Transfer-Encoding: base64\r\n"
            . "Content-ID: <painel-logo>\r\n"
            . "Content-Disposition: inline; filename=\"painel-de-comando.png\"\r\n\r\n"
            . $logo . "\r\n"
            . '--' . $boundary . '--';

        return [$body, ['MIME-Version: 1.0', 'Content-Type: multipart/related; boundary="' . $boundary . '"']];
    }

    private function writeDevelopmentCopy(string $recipient, string $subject, string $resetUrl): void
    {
        $directory = BASE_PATH . '/storage/logs';
        if (!is_dir($directory)) mkdir($directory, 0775, true);
        file_put_contents($directory . '/mail-' . date('Y-m-d') . '.log', sprintf("[%s] Para: %s | %s | %s\n", date('c'), $recipient, $subject, $resetUrl), FILE_APPEND | LOCK_EX);
    }

    /** @param list<string> $headers */
    private function sendSmtp(string $recipient, string $subject, string $messageBody, array $headers, string $from): bool
    {
        $host = (string)Env::get('SMTP_HOST', '');
        $port = (int)Env::get('SMTP_PORT', '465');
        $encryption = strtolower((string)Env::get('SMTP_ENCRYPTION', 'ssl'));
        $username = (string)Env::get('SMTP_USERNAME', $from);
        $password = (string)Env::get('SMTP_PASSWORD', '');
        $target = ($encryption === 'ssl' ? 'ssl://' : '') . $host . ':' . $port;
        $socket = @stream_socket_client($target, $errorNumber, $errorMessage, 20, STREAM_CLIENT_CONNECT);
        if (!is_resource($socket)) return false;
        stream_set_timeout($socket, 20);
        try {
            if (!$this->expect($socket, [220])) return false;
            if (!$this->command($socket, 'EHLO paineldecomando.com.br', [250])) return false;
            if ($encryption === 'tls') {
                if (!$this->command($socket, 'STARTTLS', [220])) return false;
                if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) return false;
                if (!$this->command($socket, 'EHLO paineldecomando.com.br', [250])) return false;
            }
            if (!$this->command($socket, 'AUTH LOGIN', [334])) return false;
            if (!$this->command($socket, base64_encode($username), [334])) return false;
            if (!$this->command($socket, base64_encode($password), [235])) return false;
            if (!$this->command($socket, 'MAIL FROM:<' . $from . '>', [250])) return false;
            if (!$this->command($socket, 'RCPT TO:<' . $recipient . '>', [250, 251])) return false;
            if (!$this->command($socket, 'DATA', [354])) return false;
            $messageHeaders = array_merge([
                'To: <' . $recipient . '>',
                'Subject: =?UTF-8?B?' . base64_encode($subject) . '?=',
                'Date: ' . date(DATE_RFC2822),
                'Message-ID: <' . bin2hex(random_bytes(12)) . '@paineldecomando.com.br>',
            ], $headers);
            $body = preg_replace('/^\./m', '..', $messageBody) ?? $messageBody;
            fwrite($socket, implode("\r\n", $messageHeaders) . "\r\n\r\n" . $body . "\r\n.\r\n");
            if (!$this->expect($socket, [250])) return false;
            $this->command($socket, 'QUIT', [221]);
            return true;
        } finally {
            fclose($socket);
        }
    }

    /** @param resource $socket @param list<int> $codes */
    private function command($socket, string $command, array $codes): bool
    {
        fwrite($socket, $command . "\r\n");
        return $this->expect($socket, $codes);
    }

    /** @param resource $socket @param list<int> $codes */
    private function expect($socket, array $codes): bool
    {
        $response = '';
        while (($line = fgets($socket, 515)) !== false) {
            $response .= $line;
            if (strlen($line) < 4 || $line[3] !== '-') break;
        }
        return in_array((int)substr($response, 0, 3), $codes, true);
    }
}
