<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

define('DIZZY_NL_VERSION', '1.0.19');
define('DIZZY_NL_FILE', __FILE__);
define('DIZZY_NL_DIR', __DIR__ . '/');
define('DIZZY_NL_URL', DIZZY_EVENTS_URL . 'modules/newsletter/');

add_filter('cron_schedules', static function (array $schedules): array {
    $schedules['dizzy_nl_five_minutes'] = ['interval' => 5 * MINUTE_IN_SECONDS, 'display' => __('Every five minutes', 'dizzy-newsletter')];
    return $schedules;
});

require_once DIZZY_NL_DIR . 'includes/Database.php';
require_once DIZZY_NL_DIR . 'includes/Repository.php';
require_once DIZZY_NL_DIR . 'includes/CampaignSender.php';
require_once DIZZY_NL_DIR . 'includes/Frontend.php';
require_once DIZZY_NL_DIR . 'includes/Admin.php';
require_once DIZZY_NL_DIR . 'includes/Plugin.php';

add_action('plugins_loaded', static function (): void { \Dizzy\Newsletter\Plugin::boot(); });
