<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


class Dizzy_Plugin {


    /**
     * Start plugin
     */
    public static function run() {


        /**
         * Load admin functionality
         */
        if ( is_admin() ) {

            new Dizzy_Admin_Menu();

        }


        /**
         * Register post types
         */
        new Dizzy_Events_Post_Type();

        new Dizzy_Artists_Post_Type();

        new Dizzy_Event_Meta_Boxes();


        /**
         * Load text domain
         */
        add_action(
            'plugins_loaded',
            array(
                __CLASS__,
                'load_textdomain'
            )
        );


    }



    /**
     * Load translations
     */
    public static function load_textdomain() {


        load_plugin_textdomain(

            DIZZY_EVENTS_TEXTDOMAIN,

            false,

            dirname(
                DIZZY_EVENTS_BASENAME
            ) . '/languages/'

        );


    }


}
