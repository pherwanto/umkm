<?php
class SmtpMailer {
    private array $cfg;
    public function __construct(array $cfg) { $this->cfg = $cfg; }
    private function read($socket): string {
        $data = '';
        while ($str = fgets($socket, 515)) {
            $data .= $str;
            if (strlen($str) < 4 || $str[3] === ' ') break;
        }
        return $data;
    }
    private function cmd($socket, string $cmd, array $okCodes = ['250']): void {
        fwrite($socket, $cmd . "
");
        $resp = $this->read($socket);
        foreach ($okCodes as $code) { if (str_starts_with($resp, $code)) return; }
        throw new Exception('SMTP error: ' . trim($resp));
    }
    public function send(string $to, string $subject, string $html, string $text=''): bool {
        $host = $this->cfg['host']; $port=(int)$this->cfg['port']; $timeout=20;
        $socket = stream_socket_client("tcp://{$host}:{$port}", $errno, $errstr, $timeout);
        if (!$socket) throw new Exception("SMTP connection failed: $errstr ($errno)");
        stream_set_timeout($socket, $timeout);
        $this->read($socket);
        $this->cmd($socket, 'EHLO localhost');
        if (($this->cfg['secure'] ?? '') === 'tls') {
            $this->cmd($socket, 'STARTTLS', ['220']);
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) throw new Exception('Gagal mengaktifkan TLS SMTP.');
            $this->cmd($socket, 'EHLO localhost');
        }
        if (!empty($this->cfg['username'])) {
            $this->cmd($socket, 'AUTH LOGIN', ['334']);
            $this->cmd($socket, base64_encode($this->cfg['username']), ['334']);
            $this->cmd($socket, base64_encode($this->cfg['password']), ['235']);
        }
        $fromEmail = $this->cfg['from_email']; $fromName = $this->cfg['from_name'] ?? 'Mailer';
        $this->cmd($socket, 'MAIL FROM:<' . $fromEmail . '>');
        $this->cmd($socket, 'RCPT TO:<' . $to . '>');
        $this->cmd($socket, 'DATA', ['354']);
        $boundary = 'b' . md5((string)microtime(true));
        $headers = [
            'MIME-Version: 1.0',
            'From: ' . sprintf('%s <%s>', $fromName, $fromEmail),
            'To: <' . $to . '>',
            'Subject: =?UTF-8?B?' . base64_encode($subject) . '?=',
            'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
        ];
        $plain = $text ?: strip_tags($html);
        $body = implode("
", $headers) . "

" .
            '--' . $boundary . "
" .
            "Content-Type: text/plain; charset=UTF-8

" . $plain . "
" .
            '--' . $boundary . "
" .
            "Content-Type: text/html; charset=UTF-8

" . $html . "
" .
            '--' . $boundary . "--
.";
        fwrite($socket, $body . "
");
        $resp = $this->read($socket);
        if (!str_starts_with($resp, '250')) throw new Exception('SMTP DATA gagal: ' . trim($resp));
        $this->cmd($socket, 'QUIT', ['221']);
        fclose($socket);
        return true;
    }
}
