<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

define('DIZZY_SCHEDULE_VERSION', '2.2.2');
define('DIZZY_SCHEDULE_FILE', __FILE__);
define('DIZZY_SCHEDULE_PATH', __DIR__ . '/');
define('DIZZY_SCHEDULE_URL', DIZZY_EVENTS_URL . 'modules/schedule/');

require_once DIZZY_SCHEDULE_PATH . 'includes/EmployeeRole.php';
require_once DIZZY_SCHEDULE_PATH . 'includes/Database.php';
require_once DIZZY_SCHEDULE_PATH . 'includes/ShiftRepository.php';
require_once DIZZY_SCHEDULE_PATH . 'includes/AjaxController.php';
require_once DIZZY_SCHEDULE_PATH . 'includes/PositionSettings.php';
require_once DIZZY_SCHEDULE_PATH . 'includes/Admin/SchedulePage.php';
require_once DIZZY_SCHEDULE_PATH . 'includes/Admin/ReportsPage.php';
require_once DIZZY_SCHEDULE_PATH . 'includes/Admin/SettingsPage.php';

add_action('init', static function (): void {
    load_plugin_textdomain('dizzy-schedule-manager', false, dirname(plugin_basename(DIZZY_EVENTS_FILE)) . '/modules/schedule/languages');
}, 5);

add_action('plugins_loaded', static function (): void {
    $role = new \Dizzy\Schedule\EmployeeRole();
    $role->register();
    add_action('init', [\Dizzy\Schedule\Database::class, 'migrate'], 6);
    $repository = new \Dizzy\Schedule\ShiftRepository();
    $positions = new \Dizzy\Schedule\PositionSettings();
    (new \Dizzy\Schedule\AjaxController($repository, $positions))->register();
    if (is_admin()) {
        (new \Dizzy\Schedule\Admin\SchedulePage($role, $positions))->register();
        (new \Dizzy\Schedule\Admin\ReportsPage($repository))->register();
        (new \Dizzy\Schedule\Admin\SettingsPage($positions))->register();
    }
});
