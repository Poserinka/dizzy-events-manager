<?php

namespace Dizzy\Events\Core;

defined( 'ABSPATH' ) || exit;

class Database {

    /**
     * Database version.
     */
    const VERSION = '1.0.0';

    /**
     * Table names.
     */
    public static function tables() {

        global $wpdb;

        return array(
            'events'      => $wpdb->prefix . 'dizzy_events',
            'occurrences' => $wpdb->prefix . 'dizzy_event_occurrences',
            'venues'      => $wpdb->prefix . 'dizzy_venues',
            'artists'     => $wpdb->prefix . 'dizzy_artists',
        );

    }

    /**
     * Install database.
     */
    public static function install() {

        self::create_tables();

        update_option(
            'dizzy_events_db_version',
            self::VERSION
        );

    }

    /**
     * Upgrade database if required.
     */
    public static function maybe_upgrade() {

        $installed = get_option(
            'dizzy_events_db_version'
        );

        if ( version_compare( $installed, self::VERSION, '<' ) ) {

            self::create_tables();

            update_option(
                'dizzy_events_db_version',
                self::VERSION
            );

        }

    }

    /**
     * Create tables.
     */
    protected static function create_tables() {

        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset = $wpdb->get_charset_collate();

        $tables = self::tables();

        $sql = [];

        $sql[] = "
        CREATE TABLE {$tables['occurrences']} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            event_id BIGINT UNSIGNED NOT NULL,
            start_datetime DATETIME NOT NULL,
            end_datetime DATETIME NULL,
            status VARCHAR(20) DEFAULT 'publish',
            PRIMARY KEY (id),
            KEY event_id (event_id),
            KEY start_datetime (start_datetime)
        ) {$charset};
        ";

        foreach ( $sql as $query ) {
            dbDelta( $query );
        }

    }

}