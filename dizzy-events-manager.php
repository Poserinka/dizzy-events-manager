<?php
/**
 * Plugin Name: Dizzy Events Manager
 * Plugin URI: https://dizzy.nl
 * Description: Professional event management system for Jazzcafé Dizzy.
 * Version: 0.1-alpha.1
 * Author: Poserinka Design
 * Author URI: https://poserinka.com
 * Text Domain: dizzy-events-manager
 * Domain Path: /languages
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/**
 * Plugin constants
 */

define( 'DIZZY_EVENTS_VERSION', '0.1-alpha.1' );

define(
    'DIZZY_EVENTS_PATH',
    plugin_dir_path( __FILE__ )
);

define(
    'DIZZY_EVENTS_URL',
    plugin_dir_url( __FILE__ )
);

define(
    'DIZZY_EVENTS_BASENAME',
    plugin_basename( __FILE__ )
);

define(
    'DIZZY_EVENTS_TEXTDOMAIN',
    'dizzy-events-manager'
);


/**
 * Load core files
 */

require_once DIZZY_EVENTS_PATH . 'includes/class-loader.php';


/**
 * Initialize loader
 */

Dizzy_Loader::init();


/**
 * Activation / Deactivation
 */

register_activation_hook(
    __FILE__,
    array(
        'Dizzy_Activator',
        'activate'
    )
);


register_deactivation_hook(
    __FILE__,
    array(
        'Dizzy_Deactivator',
        'deactivate'
    )
);


/**
 * Start plugin
 */

Dizzy_Plugin::run();
