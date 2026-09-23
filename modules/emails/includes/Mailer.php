<?php

declare(strict_types=1);

namespace Dizzy\Emails;

use RuntimeException;

defined('ABSPATH') || exit;

final class Mailer
{
    public function send(string $email, string $subject, string $message): bool
    {
        return $this->deliver($email, $subject, wp_kses_post($message));
    }

    public function sendTemplate(string $email, string $subject, string $template, array $data): bool
    {
        $key = match ($template) {
            'ticket-confirmed' => 'ticket',
            'reservation-confirmed' => 'reservation',
            default => '',
        };
        if ($key !== '') {
            if (! Settings::enabled($key)) {
                return true;
            }
            $subject = Settings::subject($key, $subject);
            $data['email_message'] = Settings::message($key);
            $data['event_image_url'] = EventImage::url((int) ($data['event_id'] ?? 0), Settings::imageSource($key));
        }
        if (! preg_match('/^[a-z0-9-]+$/', $template)) {
            throw new RuntimeException('Invalid email template name.');
        }

        $path = DIZZY_EMAILS_PATH . 'includes/Templates/' . $template . '.php';

        if (! is_file($path)) {
            throw new RuntimeException('Email template was not found: ' . $template);
        }

        $data = array_merge([
            'site_name' => wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES),
            'site_url' => home_url('/'),
        ], $data);

        extract($data, EXTR_SKIP);
        ob_start();
        include $path;
        $html = (string) ob_get_clean();

        return $this->deliver($email, $subject, $html);
    }
    private function deliver(string $email, string $subject, string $html): bool
    {
        $fromEmail = static fn (string $from): string => 'info@dizzy.nl';
        $fromName = static fn (string $name): string => 'Jazzcafe Dizzy';

        add_filter('wp_mail_from', $fromEmail);
        add_filter('wp_mail_from_name', $fromName);

        try {
            return Delivery::send(
                $email,
                $subject,
                $html,
                ['Content-Type: text/html; charset=UTF-8']
            );
        } finally {
            remove_filter('wp_mail_from', $fromEmail);
            remove_filter('wp_mail_from_name', $fromName);
        }
    }

}
