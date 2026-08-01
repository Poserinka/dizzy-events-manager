<?php

declare(strict_types=1);

namespace Dizzy\Events\Reservations\Admin;

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
        add_action(
            'admin_menu',
            [$this, 'registerMenu']
        );
    }

    public function registerMenu(): void
    {
        add_menu_page(
            'Reservations',
            'Reservations',
            'manage_options',
            'dizzy-reservations',
            [$this, 'render'],
            'dashicons-calendar-alt'
        );
    }

    public function render(): void
    {
        $reservations = $this->repository->all();

        echo '<div class="wrap"><h1>Reservations</h1>';
        echo '<table class="widefat"><thead><tr><th>Name</th><th>Email</th><th>Guests</th><th>Status</th></tr></thead><tbody>';

        foreach ($reservations as $reservation) {
            echo '<tr>';
            echo '<td>' . esc_html((string) $reservation['name']) . '</td>';
            echo '<td>' . esc_html((string) $reservation['email']) . '</td>';
            echo '<td>' . esc_html((string) $reservation['guests']) . '</td>';
            echo '<td>' . esc_html((string) $reservation['status']) . '</td>';
            echo '</tr>';
        }

        echo '</tbody></table></div>';
    }
}
