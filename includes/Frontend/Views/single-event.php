<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

global $post;

if (! $post instanceof \WP_Post) {
    return;
}


$eventId = $post->ID;


/**
 * Application instance.
 */
$app = $GLOBALS['dizzy_events_application'] ?? null;


if (! $app) {
    return;
}


$service =
    $app
        ->container()
        ->get(
            \Dizzy\Events\Services\EventService::class
        );


$data =
    $service
        ->getEvent($eventId);


if (! $data) {
    return;
}


$event =
    $data['event'];


$occurrences =
    $data['occurrences'];

?>

<div class="dizzy-single-event">


    <header class="dizzy-event-header">

        <?php if (has_post_thumbnail($eventId)): ?>

            <div class="dizzy-event-image">

                <?php

                echo get_the_post_thumbnail(
                    $eventId,
                    'large'
                );

                ?>

            </div>

        <?php endif; ?>


        <h1>

            <?php echo esc_html(
                $event->title
            ); ?>

        </h1>


    </header>



    <div class="dizzy-event-content">

        <?php

        echo wp_kses_post(
            apply_filters(
                'the_content',
                $event->content
            )
        );

        ?>

    </div>



    <?php if (! empty($occurrences)): ?>


        <section class="dizzy-event-dates">


            <h2>

                <?php esc_html_e(
                    'Dates',
                    'dizzy-events-manager'
                ); ?>

            </h2>


            <ul>


            <?php foreach ($occurrences as $occurrence): ?>


                <li>

                    <?php echo esc_html(
                        $occurrence
                            ->startDateTime
                            ->format(
                                'd F Y - H:i'
                            )
                    ); ?>


                </li>


            <?php endforeach; ?>


            </ul>


        </section>


    <?php endif; ?>


</div>