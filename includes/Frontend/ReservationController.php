<?php

declare(strict_types=1);

namespace Dizzy\Events\Frontend;

use Dizzy\Events\Reservations\ReservationService;


defined('ABSPATH') || exit;

final class ReservationController
{
    public function __construct(
        private readonly ReservationService $service
    ) {
    }

    public function register(): void
    {
        add_shortcode(
            'dizzy_reservation_form',
            [$this, 'render']
        );

        add_action(
            'init',
            [$this, 'handle']
        );
    }

    public function render(): string
    {
        $message = '';

        if (isset($_GET['reservation'])) {
            $message = 'Reservation request received.';
        }

        ob_start();
        ?>
        <?php if ($message !== '') : ?>
            <div class="dizzy-reservation-message">
                <?php echo esc_html($message); ?>
            </div>
        <?php endif; ?>

        <form method="post" class="dizzy-reservation-form">
            <?php wp_nonce_field('dizzy_reservation_submit', 'dizzy_reservation_nonce'); ?>

            <input type="hidden" name="event_id" value="<?php echo esc_attr((string) get_the_ID()); ?>">
            <input type="hidden" name="occurrence_id" value="<?php echo esc_attr((string) ($_GET['occurrence_id'] ?? 0)); ?>">

            <input type="text" name="name" required placeholder="Name">
            <input type="email" name="email" required placeholder="Email">
            <input type="number" name="guests" min="1" required placeholder="Guests">

            <button type="submit" name="dizzy_reservation_submit">
                Reserve
            </button>
        </form>
        <?php

        return (string) ob_get_clean();
    }

    public function handle(): void
    {
        if (! isset($_POST['dizzy_reservation_submit'])) {
            return;
        }

        if (! isset($_POST['dizzy_reservation_nonce']) || ! wp_verify_nonce(
            sanitize_text_field(wp_unslash($_POST['dizzy_reservation_nonce'])),
            'dizzy_reservation_submit'
        )) {
            return;
        }

        $this->service->create([
            'event_id' => absint($_POST['event_id'] ?? 0),
            'occurrence_id' => absint($_POST['occurrence_id'] ?? 0),
            'name' => sanitize_text_field(wp_unslash($_POST['name'] ?? '')),
            'email' => sanitize_email(wp_unslash($_POST['email'] ?? '')),
            'guests' => absint($_POST['guests'] ?? 1),
        ]);

        wp_safe_redirect(add_query_arg('reservation', 'success'));
        exit;
    }
}
