<?php

declare(strict_types=1);

namespace Dizzy\Events\Admin;

use Dizzy\Events\Reservations\ReservationRepository;

defined('ABSPATH') || exit;

final class ReservationAdmin
{
    public function __construct(
        private readonly ReservationRepository $repository
    ) {
    }

    public function register(): void
    {
        add_action('admin_menu', [$this, 'registerMenu']);
    }

    public function registerMenu(): void
    {
        add_submenu_page(
            'edit.php?post_type=dizzy_event',
            'Reservations',
            'Reservations',
            'manage_options',
            'dizzy-reservations',
            [$this, 'render']
        );
    }

    public function render(): void
    {
        if (! current_user_can('manage_options')) {
            return;
        }

        $reservations = $this->repository->all();

        echo '<div class="wrap">';
        echo '<h1>Reservations</h1>';
        echo '<table class="widefat fixed striped">';
        echo '<thead><tr><th>Name</th><th>Email</th><th>Event</th><th>Guests</th><th>Status</th></tr></thead><tbody>';

        foreach ($reservations as $reservation) {
            echo '<tr>';
            echo '<td>' . esc_html((string) ($reservation['name'] ?? '')) . '</td>';
            echo '<td>' . esc_html((string) ($reservation['email'] ?? '')) . '</td>';
            echo '<td>' . esc_html((string) ($reservation['event_id'] ?? '')) . '</td>';
            echo '<td>' . esc_html((string) ($reservation['guests'] ?? '')) . '</td>';
            echo '<td>' . esc_html((string) ($reservation['status'] ?? 'pending')) . '</td>';
            echo '</tr>';
        }

        echo '</tbody></table></div>';
    }
}
