<?php

declare(strict_types=1);

/**
 * Event card template.
 *
 * Variables:
 *
 * @var \Dizzy\Events\Frontend\ViewModels\EventViewData $event
 */

defined('ABSPATH') || exit;

?>

<article class="dizzy-event-card">

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


    <?php if ($event->excerpt): ?>

        <div class="dizzy-event-excerpt">

            <?php echo wp_kses_post(
                $event->excerpt
            ); ?>

        </div>

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