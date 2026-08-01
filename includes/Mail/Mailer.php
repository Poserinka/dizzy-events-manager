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
        return wp_mail(
            $to,
            $subject,
            $message,
            $headers
        );
    }
}
