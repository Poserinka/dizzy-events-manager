<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Dizzy_Loader {


    /**
     * Load required classes
     */
    public static function init() {


        $classes = array(

            'class-activator.php',

            'class-deactivator.php',

            'class-plugin.php',

            'class-events-post-type.php',

            'class-artists-post-type.php',

            'class-admin-menu.php',

            'class-event-meta-boxes.php',

        );


        foreach ( $classes as $class ) {


            $file = DIZZY_EVENTS_PATH . 'includes/' . $class;


            if ( file_exists( $file ) ) {

                require_once $file;

            }

        }


    }

}
