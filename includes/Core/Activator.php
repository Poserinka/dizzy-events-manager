<?php

namespace Dizzy\Events\Core;

defined( 'ABSPATH' ) || exit;

class Activator {

    /**
     * Plugin activation.
     *
     * @return void
     */
    public static function activate(): void {

        // Create or update database tables.
        Database::install();

        // Register scheduled events.
        Scheduler::register();

        // Store plugin version.
        update_option(
            'dizzy_events_version',
            DIZZY_EVENTS_VERSION
        );

        // Store installation timestamp.
        if ( ! get_option( 'dizzy_events_installed_at' ) ) {
            update_option(
                'dizzy_events_installed_at',
                current_time( 'mysql', true )
            );
        }

        // Flush rewrite rules.
        flush_rewrite_rules();
    }
}