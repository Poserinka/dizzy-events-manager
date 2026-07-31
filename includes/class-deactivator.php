<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


class Dizzy_Deactivator {


    /**
     * Plugin deactivation
     */
    public static function deactivate() {


        /**
         * Clear rewrite rules
         *
         * This keeps WordPress URLs clean
         */
        flush_rewrite_rules();


    }


}
