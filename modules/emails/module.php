<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

define('DIZZY_EMAILS_VERSION', '1.0.2');
define('DIZZY_EMAILS_PATH', __DIR__ . '/');
define('DIZZY_EMAILS_URL', DIZZY_EVENTS_URL . 'modules/emails/');

require_once DIZZY_EMAILS_PATH . 'includes/Settings.php';
require_once DIZZY_EMAILS_PATH . 'includes/Delivery.php';
require_once DIZZY_EMAILS_PATH . 'includes/EventImage.php';
require_once DIZZY_EMAILS_PATH . 'includes/Mailer.php';
require_once DIZZY_EMAILS_PATH . 'includes/Admin.php';
require_once DIZZY_EMAILS_PATH . 'includes/ShiftReminders.php';

add_filter('cron_schedules', static function (array $schedules): array {
    $schedules['dizzy_emails_five_minutes'] = ['interval' => 300, 'display' => __('Every five minutes', 'dizzy-events-manager')];
    return $schedules;
});

add_action('init', static function (): void {
    (new \Dizzy\Emails\ShiftReminders())->register();
    if (is_admin()) {
        (new \Dizzy\Emails\Admin())->register();
    }
}, 21);
