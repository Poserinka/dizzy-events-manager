<?php

declare(strict_types=1);

namespace Dizzy\Events\Mail\Services;

defined('ABSPATH') || exit;

final class MailService
{
    public function send(string $to, string $subject, string $message, array $headers = []): bool
    {
        return wp_mail($to, $subject, $message, $headers);
    }

    public function sendTemplate(string $to, string $subject, string $template, array $data = []): bool
    {
        $message = $this->renderTemplate($template, $data);

        return $this->send($to, $subject, $message);
    }

    private function renderTemplate(string $template, array $data): string
    {
        return '';
    }
}
