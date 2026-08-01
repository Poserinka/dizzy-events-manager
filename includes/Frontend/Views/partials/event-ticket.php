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