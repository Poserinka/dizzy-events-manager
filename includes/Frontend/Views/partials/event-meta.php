<?php

declare(strict_types=1);

defined('ABSPATH') || exit;


$details =
    $args['details'] ?? null;



if (
    ! $details
) {
    return;
}

?>

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