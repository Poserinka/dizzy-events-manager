<?php

declare(strict_types=1);

namespace Dizzy\Emails;

defined('ABSPATH') || exit;

final class Settings
{
    public const OPTION = 'dizzy_email_templates';

    public static function templates(): array
    {
        $saved = (array) get_option(self::OPTION, []);
        $defaults = [
            'ticket' => ['enabled' => '1', 'subject' => 'Your event tickets', 'message' => ''],
            'reservation' => ['enabled' => '1', 'subject' => 'Reservation confirmed', 'message' => ''],
            'schedule' => ['enabled' => '0', 'subject' => 'Shift reminder', 'message' => 'This is a reminder for your upcoming shift at Jazzcafe Dizzy.'],
        ];
        foreach ($defaults as $key => $default) {
            $saved[$key] = wp_parse_args((array) ($saved[$key] ?? []), $default);
        }
        return $saved;
    }

    public static function enabled(string $key): bool
    {
        return (string) (self::templates()[$key]['enabled'] ?? '0') === '1';
    }

    public static function subject(string $key, string $fallback): string
    {
        $subject = trim((string) (self::templates()[$key]['subject'] ?? ''));
        return $subject !== '' ? $subject : $fallback;
    }

    public static function message(string $key): string
    {
        return (string) (self::templates()[$key]['message'] ?? '');
    }

    public static function sanitize(mixed $input): array
    {
        $input = is_array($input) ? $input : [];
        $clean = [];
        foreach (self::templates() as $key => $current) {
            $item = is_array($input[$key] ?? null) ? $input[$key] : [];
            $subject = sanitize_text_field((string) ($item['subject'] ?? ''));
            $clean[$key] = [
                'enabled' => isset($item['enabled']) ? '1' : '0',
                'subject' => $subject !== '' ? $subject : (string) $current['subject'],
                'message' => sanitize_textarea_field((string) ($item['message'] ?? '')),
            ];
        }
        return $clean;
    }
}
