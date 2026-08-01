<?php

declare(strict_types=1);

defined('ABSPATH') || exit;


$event =
    $args['event'] ?? null;


$details =
    $args['details'] ?? null;



if (
    ! $event
    ||
    ! $details
) {
    return;
}

?>

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