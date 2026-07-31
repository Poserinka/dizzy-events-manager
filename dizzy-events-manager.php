<?php

/**
 * Plugin Name: Dizzy Events Manager
 * Plugin URI: https://github.com/Poserinka/dizzy-events-manager
 * Description: Advanced event management system for Dizzy Rotterdam.
 * Version: 0.1.0
 * Author: Poserinka Design
 * Author URI: https://poserinka.com
 * Text Domain: dizzy-events-manager
 * Requires PHP: 8.2
 */

declare(strict_types=1);

defined('ABSPATH') || exit;


/**
 * Plugin constants.
 */
define(
    'DIZZY_EVENTS_VERSION',
    '0.1.0'
);

define(
    'DIZZY_EVENTS_PATH',
    plugin_dir_path(__FILE__)
);

define(
    'DIZZY_EVENTS_URL',
    plugin_dir_url(__FILE__)
);


/**
 * Composer autoload.
 */
$autoload = DIZZY_EVENTS_PATH . 'vendor/autoload.php';

if (file_exists($autoload)) {
    require_once $autoload;
}


/**
 * Bootstrap application.
 */
add_action(
    'plugins_loaded',
    static function (): void {

        $application = new \Dizzy\Events\Core\Application();

        $application->boot();

        /**
         * Store application instance.
         */
        $GLOBALS['dizzy_events_application'] = $application;
    }
);