<?php

declare(strict_types=1);

namespace Dizzy\Events\Core;

defined('ABSPATH') || exit;

/**
 * Database installer and table registry.
 *
 * @package Dizzy\Events\Core
 */
final class Database
{
    /**
     * Database schema version.
     */
    public const VERSION = '1.0.2';

    /**
     * Table registry.
     *
     * @return array<string, string>
     */
    public static function tables(): array
    {
        global $wpdb;

        return [
            Config::TABLE_OCCURRENCES => $wpdb->prefix . 'dizzy_event_occurrences',
            Config::TABLE_ARTISTS     => $wpdb->prefix . 'dizzy_artists',
            Config::TABLE_EVENT_ARTIST => $wpdb->prefix . 'dizzy_event_artists',
            Config::TABLE_LOGS        => $wpdb->prefix . 'dizzy_logs',
        ];
    }

    /**
     * Return a registered table name.
     */
    public static function table(string $key): string
    {
        $tables = self::tables();

        return $tables[$key] ?? '';
    }

    /**
     * Install or upgrade database tables.
     */
    public static function install(): void
    {
        $installed = get_option(Config::OPTION_DB_VERSION);

        if ($installed === self::VERSION) {
            return;
        }

        self::createTables();
        self::migrate(is_string($installed) ? $installed : null);

        update_option(
            Config::OPTION_DB_VERSION,
            self::VERSION
        );
    }

    /**
     * Create plugin tables.
     */
    private static function createTables(): void
    {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset     = $wpdb->get_charset_collate();
        $occurrences = self::table(Config::TABLE_OCCURRENCES);

        dbDelta(
            "CREATE TABLE {$occurrences} (
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                event_id BIGINT(20) UNSIGNED NOT NULL,
                start_datetime DATETIME NOT NULL,
                end_datetime DATETIME NULL,
                all_day TINYINT(1) NOT NULL DEFAULT 0,
                timezone VARCHAR(64) NOT NULL DEFAULT 'Europe/Amsterdam',
                sort_order INT(11) NOT NULL DEFAULT 0,
                status VARCHAR(32) NOT NULL DEFAULT 'publish',
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                PRIMARY KEY (id),
                KEY event_id (event_id),
                KEY start_datetime (start_datetime),
                KEY sort_order (sort_order),
                KEY status (status),
                KEY event_status_start (event_id, status, start_datetime),
                KEY event_status_end (event_id, status, end_datetime),
                KEY status_start_event (status, start_datetime, event_id),
                KEY status_end_event (status, end_datetime, event_id)
            ) {$charset};"
        );

        self::createOtherTables($charset);
    }

    /**
     * Create remaining plugin tables.
     */
    private static function createOtherTables(string $charset): void
    {
        $artists   = self::table(Config::TABLE_ARTISTS);
        $relations = self::table(Config::TABLE_EVENT_ARTIST);
        $logs      = self::table(Config::TABLE_LOGS);

        dbDelta(
            "CREATE TABLE {$artists} (
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                slug VARCHAR(255) NOT NULL,
                image TEXT NULL,
                spotify TEXT NULL,
                instagram TEXT NULL,
                facebook TEXT NULL,
                website TEXT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY slug (slug)
            ) {$charset};"
        );

        dbDelta(
            "CREATE TABLE {$relations} (
                event_id BIGINT(20) UNSIGNED NOT NULL,
                artist_id BIGINT(20) UNSIGNED NOT NULL,
                role VARCHAR(100) NULL,
                PRIMARY KEY (event_id, artist_id),
                KEY artist_id (artist_id)
            ) {$charset};"
        );

        dbDelta(
            "CREATE TABLE {$logs} (
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                level VARCHAR(20) NOT NULL,
                module VARCHAR(100) NULL,
                message TEXT NOT NULL,
                context LONGTEXT NULL,
                created_at DATETIME NOT NULL,
                PRIMARY KEY (id),
                KEY level (level),
                KEY created_at (created_at)
            ) {$charset};"
        );
    }

    /**
     * Run versioned database migrations.
     */
    private static function migrate(?string $installed): void
    {
        global $wpdb;

        if (version_compare((string) $installed, '1.0.1', '<')) {
            $table = self::table(Config::TABLE_OCCURRENCES);
            $column = $wpdb->get_results(
                "SHOW COLUMNS FROM {$table} LIKE 'sort_order'"
            );

            if (empty($column)) {
                $wpdb->query(
                    "ALTER TABLE {$table}
                    ADD sort_order INT(11) NOT NULL DEFAULT 0
                    AFTER timezone"
                );
            }
        }
    }
}
