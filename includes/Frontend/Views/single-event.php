<?php

declare(strict_types=1);

defined('ABSPATH') || exit;


$data =
    get_query_var(
        'dizzy_event_data'
    );


if (
    ! is_array($data)
) {
    return;
}



$event =
    $data['event'] ?? null;


$details =
    $data['details'] ?? null;


$upcomingOccurrences =
    $data['upcomingOccurrences']
    ?? [];


$pastOccurrences =
    $data['pastOccurrences']
    ?? [];



if (
    ! $event
    ||
    ! $details
) {
    return;
}

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



<?php if (has_post_thumbnail($event->id)): ?>

<div class="dizzy-event-image">

<?php

echo get_the_post_thumbnail(
    $event->id,
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







<?php if (! empty($upcomingOccurrences)): ?>


<section class="dizzy-event-dates">


<h2>

<?php esc_html_e(
    'Upcoming Dates',
    'dizzy-events-manager'
); ?>

</h2>



<ul>


<?php foreach ($upcomingOccurrences as $occurrence): ?>


<li>

<?php echo esc_html(

    wp_date(

        'd F Y - H:i',

        $occurrence
            ->startDateTime
            ->getTimestamp()

    )

); ?>

</li>


<?php endforeach; ?>


</ul>


</section>


<?php endif; ?>







<?php if (! empty($pastOccurrences)): ?>


<section class="dizzy-event-past-dates">


<h2>

<?php esc_html_e(
    'Past Dates',
    'dizzy-events-manager'
); ?>

</h2>



<ul>


<?php foreach ($pastOccurrences as $occurrence): ?>


<li>

<?php echo esc_html(

    wp_date(

        'd F Y - H:i',

        $occurrence
            ->startDateTime
            ->getTimestamp()

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