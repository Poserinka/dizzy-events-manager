
<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


class Dizzy_Event_Meta_Boxes {


    public function __construct() {


        add_action(
            'add_meta_boxes',
            array(
                $this,
                'add_meta_boxes'
            )
        );


        add_action(
            'save_post_dizzy_event',
            array(
                $this,
                'save'
            )
        );


    }



    /**
     * Add Event Details box
     */
    public function add_meta_boxes() {


        add_meta_box(

            'dizzy_event_details',

            'Event Details',

            array(
                $this,
                'render'
            ),

            'dizzy_event',

            'normal',

            'high'

        );


    }



    /**
     * Render fields
     */
    public function render( $post ) {


        wp_nonce_field(
            'dizzy_event_save',
            'dizzy_event_nonce'
        );


        $date = get_post_meta(
            $post->ID,
            '_event_date',
            true
        );

        $start_time = get_post_meta(
    $post->ID,
    '_event_start_time',
    true
);


$end_time = get_post_meta(
    $post->ID,
    '_event_end_time',
    true
);


        $venue = get_post_meta(
            $post->ID,
            '_event_venue',
            true
        );


        if ( empty( $venue ) ) {

            $venue = dizzy_get_default_venue();

        }


        $address = get_post_meta(
            $post->ID,
            '_event_address',
            true
        );


        if ( empty( $address ) ) {

            $address = dizzy_get_default_address();

        }


        $maps = get_post_meta(
            $post->ID,
            '_event_maps',
            true
        );


        if ( empty( $maps ) ) {

            $maps = dizzy_get_default_maps_url();

        }


        ?>

        <p>
            <label>
                <strong>Event Date</strong>
            </label>
        </p>

        <input 
            type="date"
            name="event_date"
            value="<?php echo esc_attr($date); ?>"
        >


        <p>
            <label>
                <strong>Venue</strong>
            </label>
        </p>

        <input 
            type="text"
            name="event_venue"
            value="<?php echo esc_attr($venue); ?>"
            style="width:100%;"
        >



        <p>
            <label>
                <strong>Address</strong>
            </label>
        </p>

        <textarea
            name="event_address"
            style="width:100%;"
        ><?php echo esc_textarea($address); ?></textarea>



        <p>
            <label>
                <strong>Google Maps URL</strong>
            </label>
        </p>

        <input
            type="url"
            name="event_maps"
            value="<?php echo esc_attr($maps); ?>"
            style="width:100%;"
        >


        <?php

    }





    /**
     * Save fields
     */
    public function save( $post_id ) {


        if (
            ! isset($_POST['dizzy_event_nonce'])
            ||
            ! wp_verify_nonce(
                $_POST['dizzy_event_nonce'],
                'dizzy_event_save'
            )
        ) {

            return;

        }


        if (
            defined('DOING_AUTOSAVE')
            &&
            DOING_AUTOSAVE
        ) {

            return;

        }



        if (
            ! current_user_can(
                'edit_post',
                $post_id
            )
        ) {

            return;

        }



        $fields = array(

            'event_date',

            'event_venue',

            'event_address',

            'event_maps'

        );



        foreach($fields as $field){


            if(isset($_POST[$field])){


                update_post_meta(

                    $post_id,

                    '_'.$field,

                    sanitize_text_field(
                        $_POST[$field]
                    )

                );


            }


        }


    }


}
