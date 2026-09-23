<?php

declare(strict_types=1);

namespace Dizzy\Emails;

defined('ABSPATH') || exit;

final class ShiftReminders
{
    public const HOOK = 'dizzy_emails_check_shift_reminders';

    public function register(): void
    {
        add_action(self::HOOK, [$this, 'sendDue']);
    }

    public function sendDue(): void
    {
        if (! Settings::enabled('schedule')) {
            return;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'dizzy_schedule_shifts';
        if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table)) !== $table) {
            return;
        }

        $now = new \DateTimeImmutable('now', wp_timezone());
        $from = $now->modify('+115 minutes');
        $to = $now->modify('+125 minutes');
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT s.*,u.display_name,u.user_email
             FROM {$table} s
             INNER JOIN {$wpdb->users} u ON u.ID=s.employee_id
             WHERE s.status='published'
             AND TIMESTAMP(
                 DATE_ADD(s.shift_date, INTERVAL IF(s.start_time <= '02:00:00',1,0) DAY),
                 s.start_time
             ) BETWEEN %s AND %s",
            $from->format('Y-m-d H:i:s'),
            $to->format('Y-m-d H:i:s')
        ), ARRAY_A) ?: [];

        foreach ($rows as $row) {
            $email = sanitize_email((string) ($row['user_email'] ?? ''));
            if (! is_email($email)) {
                continue;
            }
            $marker = 'dizzy_email_shift_' . (int) $row['id'] . '_' . md5((string) $row['updated_at']);
            if (! add_option($marker, 'processing', '', false)) {
                continue;
            }
            $data = [
                'employee_name' => (string) $row['display_name'],
                'shift_date' => wp_date('d/m/Y', strtotime((string) $row['shift_date']), wp_timezone()),
                'start_time' => substr((string) $row['start_time'], 0, 5),
                'end_time' => substr((string) $row['end_time'], 0, 5),
                'position' => (string) $row['position'],
                'message' => Settings::message('schedule'),
            ];
            $bufferLevel = ob_get_level();
            try {
                ob_start();
                include DIZZY_EMAILS_PATH . 'includes/Templates/shift-reminder.php';
                $html = (string) ob_get_clean();
                $fromEmail = static fn (string $from): string => 'info@dizzy.nl';
                $fromName = static fn (string $name): string => 'Jazzcafe Dizzy';
                add_filter('wp_mail_from', $fromEmail);
                add_filter('wp_mail_from_name', $fromName);
                try {
                    $sent = Delivery::send(
                        $email,
                        Settings::subject('schedule', __('Shift reminder', 'dizzy-events-manager')),
                        $html,
                        ['Content-Type: text/html; charset=UTF-8']
                    );
                } finally {
                    remove_filter('wp_mail_from', $fromEmail);
                    remove_filter('wp_mail_from_name', $fromName);
                }
            } catch (\Throwable $error) {
                if (ob_get_level() > $bufferLevel) {
                    ob_end_clean();
                }
                delete_option($marker);
                error_log('Dizzy shift email: ' . $error->getMessage());
                continue;
            }
            if ($sent) {
                update_option($marker, 'sent', false);
            } else {
                delete_option($marker);
            }
        }
    }
}
