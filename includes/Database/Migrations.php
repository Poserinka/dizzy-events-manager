<?php

declare(strict_types=1);

namespace Dizzy\Events\Database;

use wpdb;

defined('ABSPATH') || exit;

/**
 * Handles database migrations.
 *
 * @package Dizzy\Events\Database
 */
final class Migrations
{
    /**
     * Current database version.
     */
    private const VERSION = '1.0.0';

    /**
     * Option key.
     */
    private const OPTION = 'dizzy_events_db_version';

    /**
     * Run migrations.
     */
    public static function run(): void
    {
        $installed = get_option(
            self::OPTION,
            '0.0.0'
        );

        if (version_compare(
            $installed,
            self::VERSION,
            '>='
        )) {
            return;
        }

        self::createOccurrencesTable();

        update_option(
            self::OPTION,
            self::VERSION
        );
    }

    /**
     * Create occurrences table.
     */
    private static function createOccurrencesTable(): void
    {
        global $wpdb;

        $table = $wpdb->prefix .
            'dizzy_event_occurrences';

        $charset = $wpdb->get_charset_collate();

        $sql = sprintf(
            "
            CREATE TABLE %s (
                %s
            ) %s;
            ",
            $table,
            Schema::occurrences(),
            $charset
        );

        require_once ABSPATH .
            'wp-admin/includes/upgrade.php';

        dbDelta($sql);
    }
}