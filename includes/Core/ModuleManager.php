<?php

declare(strict_types=1);

namespace Dizzy\Events\Core;

defined('ABSPATH') || exit;

final class ModuleManager
{
    /** @var array<string, array{standalone:string,bootstrap:string}> */
    private const MODULES = [
        'emails' => ['standalone' => '', 'bootstrap' => 'modules/emails/module.php'],
        'newsletter' => ['standalone' => 'dizzy-newsletter/dizzy-newsletter.php', 'bootstrap' => 'modules/newsletter/module.php'],
        'reservations' => ['standalone' => 'dizzy-reservations-manager/dizzy-reservations-manager.php', 'bootstrap' => 'modules/reservations/module.php'],
        'schedule' => ['standalone' => 'dizzy-schedule-manager/dizzy-schedule-manager.php', 'bootstrap' => 'modules/schedule/module.php'],
        'social-media' => ['standalone' => 'dizzy-social-media-manager/dizzy-social-media-manager.php', 'bootstrap' => 'modules/social-media/module.php'],
        'tickets' => ['standalone' => 'dizzy-ticket-manager/dizzy-ticket-manager.php', 'bootstrap' => 'modules/tickets/module.php'],
        'wanotify' => ['standalone' => 'dizzy-wanotify-manager/dizzy-wanotify-manager.php', 'bootstrap' => 'modules/wanotify/module.php'],
    ];

    /** @var array<string, bool> */
    private static array $loaded = [];

    public static function load(): void
    {
        foreach (self::MODULES as $id => $module) {
            if (self::standaloneIsActive($module['standalone'])) {
                continue;
            }

            $bootstrap = DIZZY_EVENTS_PATH . $module['bootstrap'];
            if (is_readable($bootstrap)) {
                require_once $bootstrap;
                self::$loaded[$id] = true;
            }
        }
    }

    public static function registerMaintenance(): void
    {
        add_action('init', [self::class, 'maintain'], 1);
        add_action('admin_menu', [self::class, 'registerAdminPage'], 90);
        add_action('admin_notices', [self::class, 'standaloneNotice']);
    }

    public static function registerAdminPage(): void
    {
        add_submenu_page(
            null,
            __('Dizzy Suite Modules', 'dizzy-events-manager'),
            __('Dizzy Suite', 'dizzy-events-manager'),
            'manage_options',
            'dizzy-suite-modules',
            [self::class, 'renderAdminPage']
        );
    }

    public static function renderAdminPage(): void
    {
        if (! current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to view this page.', 'dizzy-events-manager'));
        }

        echo '<div class="wrap"><h1>' . esc_html__('Dizzy Suite Modules', 'dizzy-events-manager') . '</h1>';
        echo '<p>' . esc_html__('All modules keep their existing database tables, settings, API routes and stored data.', 'dizzy-events-manager') . '</p>';
        echo '<table class="widefat striped"><thead><tr><th>' . esc_html__('Module', 'dizzy-events-manager') . '</th><th>' . esc_html__('Status', 'dizzy-events-manager') . '</th></tr></thead><tbody>';
        foreach (self::MODULES as $id => $module) {
            $bundled = isset(self::$loaded[$id]);
            $status = $bundled
                ? __('Bundled module active', 'dizzy-events-manager')
                : __('Standalone plugin active; bundled module paused', 'dizzy-events-manager');
            echo '<tr><td><strong>' . esc_html(ucwords(str_replace('-', ' ', $id))) . '</strong></td><td>' . esc_html($status) . '</td></tr>';
        }
        echo '</tbody></table></div>';
    }

    public static function standaloneNotice(): void
    {
        if (! current_user_can('manage_options')) {
            return;
        }

        $active = [];
        foreach (self::MODULES as $id => $module) {
            if (self::standaloneIsActive($module['standalone'])) {
                $active[] = ucwords(str_replace('-', ' ', $id));
            }
        }
        if ($active === []) {
            return;
        }

        echo '<div class="notice notice-warning"><p><strong>' . esc_html__('Dizzy Suite migration:', 'dizzy-events-manager') . '</strong> ';
        echo esc_html(sprintf(__('Deactivate the old standalone plugins to enable their bundled modules: %s. Existing data and settings will be preserved.', 'dizzy-events-manager'), implode(', ', $active)));
        echo '</p></div>';
    }

    public static function maintain(): void
    {
        if ((string) get_option('dizzy_events_suite_version', '') !== DIZZY_EVENTS_VERSION) {
            self::activate();
            return;
        }

        self::ensureSchedules();
    }

    public static function activate(): void
    {
        if (isset(self::$loaded['newsletter'])) {
            \Dizzy\Newsletter\Plugin::activate();
        }
        if (isset(self::$loaded['reservations'])) {
            \Dizzy\Reservations\Database\Migrations::run();
        }
        if (isset(self::$loaded['schedule'])) {
            \Dizzy\Schedule\EmployeeRole::activate();
            \Dizzy\Schedule\Database::migrate();
        }
        if (isset(self::$loaded['social-media'])) {
            \Dizzy\SocialMedia\Database\Migrations::run();
        }
        if (isset(self::$loaded['tickets'])) {
            \Dizzy\Tickets\Database\Migrations::run();
        }
        self::ensureSchedules();

        update_option('dizzy_events_suite_version', DIZZY_EVENTS_VERSION);
    }

    public static function deactivate(): void
    {
        if (isset(self::$loaded['newsletter'])) {
            \Dizzy\Newsletter\Plugin::deactivate();
        }
        if (isset(self::$loaded['wanotify'])) {
            wp_clear_scheduled_hook('dizzy_wanotify_check_shift_reminders');
        }
        if (isset(self::$loaded['emails'])) {
            wp_clear_scheduled_hook(\Dizzy\Emails\ShiftReminders::HOOK);
        }
    }

    private static function standaloneIsActive(string $plugin): bool
    {
        if ($plugin === '') {
            return false;
        }
        $active = (array) get_option('active_plugins', []);
        if (in_array($plugin, $active, true) || self::containsPluginFile($active, $plugin)) {
            return true;
        }

        if (is_multisite()) {
            $networkActive = array_keys((array) get_site_option('active_sitewide_plugins', []));
            return in_array($plugin, $networkActive, true) || self::containsPluginFile($networkActive, $plugin);
        }

        return false;
    }

    /** @param array<int, string> $active */
    private static function containsPluginFile(array $active, string $plugin): bool
    {
        $file = basename($plugin);
        foreach ($active as $activePlugin) {
            if (basename((string) $activePlugin) === $file) {
                return true;
            }
        }
        return false;
    }

    private static function ensureSchedules(): void
    {
        if (isset(self::$loaded['newsletter'])) {
            if (! wp_next_scheduled('dizzy_nl_process_queue')) {
                wp_schedule_event(time() + 60, 'dizzy_nl_five_minutes', 'dizzy_nl_process_queue');
            }
            if (! wp_next_scheduled('dizzy_nl_cleanup')) {
                wp_schedule_event(time() + DAY_IN_SECONDS, 'daily', 'dizzy_nl_cleanup');
            }
        }
        if (isset(self::$loaded['wanotify']) && ! wp_next_scheduled('dizzy_wanotify_check_shift_reminders')) {
            wp_schedule_event(time() + 60, 'dizzy_wanotify_five_minutes', 'dizzy_wanotify_check_shift_reminders');
        }
        if (isset(self::$loaded['emails']) && ! wp_next_scheduled(\Dizzy\Emails\ShiftReminders::HOOK)) {
            wp_schedule_event(time() + 60, 'dizzy_emails_five_minutes', \Dizzy\Emails\ShiftReminders::HOOK);
        }
    }
}
