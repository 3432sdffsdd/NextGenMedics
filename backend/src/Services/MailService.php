<?php
namespace App\Services;

class MailService
{
    public function send(string $to, string $subject, string $body): bool
    {
        $config = require __DIR__ . '/../../config/config.php';
        $headers = [
            'From: ' . $config['mail']['from_name'] . ' <' . $config['mail']['from'] . '>',
            'Content-Type: text/html; charset=UTF-8',
            'MIME-Version: 1.0',
        ];
        return @mail($to, $subject, $body, implode("\r\n", $headers));
    }

    public function sendNotification(string $to, string $title, string $message): bool
    {
        $body = "<html><body><h2>{$title}</h2><p>{$message}</p></body></html>";
        return $this->send($to, $title, $body);
    }
}
