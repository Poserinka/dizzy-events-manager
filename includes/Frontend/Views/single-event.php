<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

global $post;

if (! $post instanceof \WP_Post) {
    return;
}


$eventId = $post->ID;


$app =
    $GLOBALS['dizzy_events_application'] ?? null;


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


$details =
    \Dizzy\Events\Models\EventDetails::fromMeta(
        get_post_meta(
            $eventId
        )
    );

?>

<div class="dizzy-single-event">


<header class="dizzy-event-header">


<?php if ($details->featured): ?>

<span class="dizzy-event-featured">

<?php esc_html_e(
    'Featured Event',
    'dizzy-events-manager'
); ?>

</span>

<?php endif; ?>


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



<div class="dizzy-event-meta">


<?php if ($details->artist): ?>

<p>

<strong>
<?php esc_html_e(
    'Artist:',
    'dizzy-events-manager'
); ?>
</strong>

<?php echo esc_html(
    $details->artist
); ?>

</p>

<?php endif; ?>



<?php if ($details->genre): ?>

<p>

<strong>
<?php esc_html_e(
    'Genre:',
    'dizzy-events-manager'
); ?>
</strong>

<?php echo esc_html(
    $details->genre
); ?>

</p>

<?php endif; ?>



<?php if ($details->venue): ?>

<p>

<strong>
<?php esc_html_e(
    'Venue:',
    'dizzy-events-manager'
); ?>
</strong>

<?php echo esc_html(
    $details->venue
); ?>

</p>

<?php endif; ?>


</div>



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



<?php if (!empty($occurrences)): ?>


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



<?php if ($details->ticketPrice !== null): ?>

<p class="dizzy-event-price">

<strong>

<?php esc_html_e(
    'Price:',
    'dizzy-events-manager'
); ?>

</strong>


<?php echo esc_html(
    number_format(
        $details->ticketPrice,
        2
    )
); ?>

€

</p>

<?php endif; ?>



<?php if ($details->ticketUrl): ?>

<a
class="dizzy-event-ticket"
href="<?php echo esc_url(
    $details->ticketUrl
); ?>"
target="_blank"
rel="noopener"
>

<?php esc_html_e(
    'Buy Ticket',
    'dizzy-events-manager'
); ?>

</a>

<?php endif; ?>


</div>