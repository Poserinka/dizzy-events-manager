
<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/**
 * Get event default venue
 *
 * @return string
 */
function dizzy_get_default_venue() {

    return 'Jazzcafé Dizzy';

}


/**
 * Get event default address
 *
 * @return string
 */
function dizzy_get_default_address() {

    return "'s-Gravendijkwal 127, 3021 EK Rotterdam";

}


/**
 * Get event default Google Maps URL
 *
 * @return string
 */
function dizzy_get_default_maps_url() {

    return 'https://maps.app.goo.gl/t73PkgDRtb6RvKFMA';

}


/**
 * Format ticket price
 *
 * @param float $price
 *
 * @return string
 */
function dizzy_format_price( $price ) {


    if ( empty( $price ) ) {

        return '';

    }


    return '€' . number_format(
        (float) $price,
        2,
        ',',
        '.'
    );


}

/**
 * Get available event time options
 *
 * @return array
 */
function dizzy_get_time_options() {


    return array(

        '14:00',
        '14:30',

        '15:00',
        '15:30',

        '16:00',
        '16:30',

        '17:00',
        '17:30',

        '18:00',
        '18:30',

        '19:00',
        '19:30',

        '20:00',
        '20:30',

        '21:00',
        '21:30',

        '22:00',
        '22:30',

        '23:00',
        '23:30',

        '00:00'

    );


}
