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
        ob_start();
        ?>
        <form method="post" class="dizzy-reservation-form">
            <?php wp_nonce_field('dizzy_reservation_submit', 'dizzy_reservation_nonce'); ?>

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
            'name' => sanitize_text_field(wp_unslash($_POST['name'] ?? '')),
            'email' => sanitize_email(wp_unslash($_POST['email'] ?? '')),
            'guests' => absint($_POST['guests'] ?? 1),
        ]);
    }
}
