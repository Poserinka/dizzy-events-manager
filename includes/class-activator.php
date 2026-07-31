<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


class Dizzy_Activator {


    /**
     * Plugin activation
     */
    public static function activate() {


        // Register post types before flushing rules
        self::register_post_types();


        // Refresh rewrite rules
        flush_rewrite_rules();


    }



    /**
     * Register CPTs during activation
     */
    private static function register_post_types() {


        if ( class_exists( 'Dizzy_Events_Post_Type' ) ) {

            $events = new Dizzy_Events_Post_Type();

        }


        if ( class_exists( 'Dizzy_Artists_Post_Type' ) ) {

            $artists = new Dizzy_Artists_Post_Type();

        }


    }


}
