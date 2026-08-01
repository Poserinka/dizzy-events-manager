<?php

declare(strict_types=1);

defined('ABSPATH') || exit;


$upcomingOccurrences =
    $args['upcomingOccurrences']
    ?? [];


$pastOccurrences =
    $args['pastOccurrences']
    ?? [];

?>





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