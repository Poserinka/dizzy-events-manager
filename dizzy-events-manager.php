<?php
/**
 * Plugin Name:       Dizzy Events Manager
 * Plugin URI:        https://github.com/Poserinka/dizzy-events-manager
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

defined( 'ABSPATH' ) || exit;

/*
|--------------------------------------------------------------------------
| Plugin Constants
|--------------------------------------------------------------------------
*/

define( 'DIZZY_EVENTS_VERSION', '1.0.0-dev' );
define( 'DIZZY_EVENTS_DB_VERSION', '1.0.0' );

define( 'DIZZY_EVENTS_FILE', __FILE__ );
define( 'DIZZY_EVENTS_BASENAME', plugin_basename( DIZZY_EVENTS_FILE ) );
define( 'DIZZY_EVENTS_PATH', plugin_dir_path( DIZZY_EVENTS_FILE ) );
define( 'DIZZY_EVENTS_URL', plugin_dir_url( DIZZY_EVENTS_FILE ) );
define( 'DIZZY_EVENTS_TEXTDOMAIN', 'dizzy-events-manager' );

/*
|--------------------------------------------------------------------------
| Load Autoloader
|--------------------------------------------------------------------------
*/

require_once DIZZY_EVENTS_PATH . 'includes/Core/Autoloader.php';

DizzyEvents\Core\Autoloader::register();

/*
|--------------------------------------------------------------------------
| Activation / Deactivation
|--------------------------------------------------------------------------
*/

register_activation_hook(
	DIZZY_EVENTS_FILE,
	array(
		DizzyEvents\Core\Activator::class,
		'activate',
	)
);

register_deactivation_hook(
	DIZZY_EVENTS_FILE,
	array(
		DizzyEvents\Core\Deactivator::class,
		'deactivate',
	)
);

/*
|--------------------------------------------------------------------------
| Bootstrap Plugin
|--------------------------------------------------------------------------
*/

DizzyEvents\Core\Plugin::run();