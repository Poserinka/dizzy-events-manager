
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
