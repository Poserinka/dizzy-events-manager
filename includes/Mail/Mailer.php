<?php

declare(strict_types=1);

namespace Dizzy\Events\Mail;

defined('ABSPATH') || exit;

final class Mailer
{
    public function send(
        string $to,
        string $subject,
        string $message,
        array $headers = []
    ): bool {
        $headers[] = 'Content-Type: text/html; charset=UTF-8';

        return wp_mail(
            $to,
            $subject,
            wpautop($message),
            $headers
        );
    }
}
