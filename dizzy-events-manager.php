<?php
/**
 * Plugin Name:       Dizzy Events Manager
 * Plugin URI:        https://dizzy.nl
 * Description:       Professional event management system for Jazzcafé Dizzy.
 * Version:           1.0.0-dev
 * Requires at least: 6.5
 * Requires PHP:      8.1
 * Author:            Poserinka Design
 * Author URI:        https://poserinka.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       dizzy-events-manager
 * Domain Path:       /languages
 *
 * @package DizzyEventsManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ------------------------------------------------------------------------
 * Plugin Constants
 * ------------------------------------------------------------------------
 */

define( 'DIZZY_EVENTS_VERSION', '1.0.0-dev' );

/**
 * Database schema version.
 *
 * Increase this only when database structure changes.
 */
define( 'DIZZY_EVENTS_DB_VERSION', '1.0.0' );

/**
 * Main plugin file.
 */
define( 'DIZZY_EVENTS_FILE', __FILE__ );

/**
 * Absolute plugin path.
 */
define( 'DIZZY_EVENTS_PATH', plugin_dir_path( DIZZY_EVENTS_FILE ) );

/**
 * Plugin URL.
 */
define( 'DIZZY_EVENTS_URL', plugin_dir_url( DIZZY_EVENTS_FILE ) );

/**
 * Plugin basename.
 */
define( 'DIZZY_EVENTS_BASENAME', plugin_basename( DIZZY_EVENTS_FILE ) );

/**
 * Plugin text domain.
 */
define( 'DIZZY_EVENTS_TEXTDOMAIN', 'dizzy-events-manager' );

/**
 * ------------------------------------------------------------------------
 * Load Core
 * ------------------------------------------------------------------------
 */

require_once DIZZY_EVENTS_PATH . 'includes/class-loader.php';

/**
 * Initialize plugin loader.
 */
Dizzy_Loader::init();

/**
 * ------------------------------------------------------------------------
 * Activation / Deactivation Hooks
 * ------------------------------------------------------------------------
 */

register_activation_hook(
	DIZZY_EVENTS_FILE,
	array( 'Dizzy_Activator', 'activate' )
);

register_deactivation_hook(
	DIZZY_EVENTS_FILE,
	array( 'Dizzy_Deactivator', 'deactivate' )
);

/**
 * ------------------------------------------------------------------------
 * Bootstrap Plugin
 * ------------------------------------------------------------------------
 */

Dizzy_Plugin::run();