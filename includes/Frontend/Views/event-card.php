<?php

declare(strict_types=1);

/**
 * Event card template.
 *
 * @var \Dizzy\Events\Frontend\ViewModels\EventViewData $event
 */

defined('ABSPATH') || exit;

?>

<article class="dizzy-event-card">


    <?php if ($event->featured): ?>

        <span class="dizzy-event-featured">

            <?php esc_html_e(
                'Featured',
                'dizzy-events-manager'
            ); ?>

        </span>

    <?php endif; ?>



    <?php if ($event->image): ?>

        <a href="<?php echo esc_url($event->url); ?>">

            <img
                src="<?php echo esc_url($event->image); ?>"
                alt="<?php echo esc_attr($event->title); ?>"
            >

        </a>

    <?php endif; ?>



    <h3 class="dizzy-event-title">

        <a href="<?php echo esc_url($event->url); ?>">

            <?php echo esc_html($event->title); ?>

        </a>

    </h3>



    <?php if ($event->artist): ?>

        <p class="dizzy-event-artist">

            <?php echo esc_html(
                $event->artist
            ); ?>

        </p>

    <?php endif; ?>



    <?php if ($event->genre): ?>

        <p class="dizzy-event-genre">

            <?php echo esc_html(
                $event->genre
            ); ?>

        </p>

    <?php endif; ?>



    <?php if ($event->venue): ?>

        <p class="dizzy-event-venue">

            <?php echo esc_html(
                $event->venue
            ); ?>

        </p>

    <?php endif; ?>



    <?php if ($event->ticketPrice !== null): ?>

        <p class="dizzy-event-price">

            <?php echo esc_html(
                number_format(
                    $event->ticketPrice,
                    2
                )
            ); ?>

            €

        </p>

    <?php endif; ?>



    <?php if ($event->excerpt): ?>

        <div class="dizzy-event-excerpt">

            <?php echo wp_kses_post(
                $event->excerpt
            ); ?>

        </div>

    <?php endif; ?>



    <?php if ($event->ticketUrl): ?>

        <a
            class="dizzy-event-ticket"
            href="<?php echo esc_url($event->ticketUrl); ?>"
            target="_blank"
            rel="noopener"
        >

            <?php esc_html_e(
                'Buy Ticket',
                'dizzy-events-manager'
            ); ?>

        </a>

    <?php endif; ?>



    <a
        class="dizzy-event-link"
        href="<?php echo esc_url($event->url); ?>"
    >

        <?php esc_html_e(
            'Read more',
            'dizzy-events-manager'
        ); ?>

    </a>


</article>