<?php

declare(strict_types=1);

namespace Dizzy\Emails;

defined('ABSPATH') || exit;

final class Delivery
{
    public static function send(string $to, string $subject, string $message, array $headers = []): bool
    {
        return wp_mail($to, $subject, $message, $headers);
    }
}
