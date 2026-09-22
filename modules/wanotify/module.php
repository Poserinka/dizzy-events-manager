<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

define('DIZZY_WANOTIFY_VERSION', '1.1.1');
define('DIZZY_WANOTIFY_FILE', __FILE__);
define('DIZZY_WANOTIFY_PATH', __DIR__ . '/');
define('DIZZY_WANOTIFY_URL', DIZZY_EVENTS_URL . 'modules/wanotify/');

require_once DIZZY_WANOTIFY_PATH . 'includes/Settings.php';
require_once DIZZY_WANOTIFY_PATH . 'includes/WhatsAppClient.php';
require_once DIZZY_WANOTIFY_PATH . 'includes/Integrations.php';
require_once DIZZY_WANOTIFY_PATH . 'includes/Admin.php';

add_action('init', static function (): void {
    load_plugin_textdomain('dizzy-wanotify-manager', false, dirname(plugin_basename(DIZZY_EVENTS_FILE)) . '/modules/wanotify/languages');
}, 5);

add_filter('cron_schedules', static function (array $schedules): array {
    $schedules['dizzy_wanotify_five_minutes'] = ['interval' => 300, 'display' => __('Every five minutes', 'dizzy-wanotify-manager')];
    return $schedules;
});

add_action('plugins_loaded', static function (): void {
    $settings = new \Dizzy\WAnotify\Settings();
    $client = new \Dizzy\WAnotify\WhatsAppClient($settings);
    (new \Dizzy\WAnotify\Integrations($settings, $client))->register();
    if (is_admin()) {
        (new \Dizzy\WAnotify\Admin($settings, $client))->register();
    }
});
