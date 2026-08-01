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
        add_action('admin_menu', [$this, 'registerMenu']);
        add_action('admin_post_dizzy_reservation_status', [$this, 'updateStatus']);
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
        echo '<table class="widefat"><thead><tr><th>Name</th><th>Email</th><th>Guests</th><th>Status</th><th>Actions</th></tr></thead><tbody>';

        foreach ($reservations as $reservation) {
            echo '<tr>';
            echo '<td>' . esc_html((string) $reservation['name']) . '</td>';
            echo '<td>' . esc_html((string) $reservation['email']) . '</td>';
            echo '<td>' . esc_html((string) $reservation['guests']) . '</td>';
            echo '<td>' . esc_html((string) $reservation['status']) . '</td>';
            echo '<td>';
            $this->actionLink((int) $reservation['id'], 'confirmed');
            $this->actionLink((int) $reservation['id'], 'cancelled');
            echo '</td>';
            echo '</tr>';
        }

        echo '</tbody></table></div>';
    }

    private function actionLink(int $id, string $status): void
    {
        $url = wp_nonce_url(
            admin_url('admin-post.php?action=dizzy_reservation_status&id=' . $id . '&status=' . $status),
            'dizzy_reservation_status_' . $id
        );

        echo '<a href="' . esc_url($url) . '">' . esc_html(ucfirst($status)) . '</a> ';
    }

    public function updateStatus(): void
    {
        if (! current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        $id = absint($_GET['id'] ?? 0);
        $status = sanitize_key($_GET['status'] ?? '');

        if ($id > 0 && in_array($status, ['confirmed', 'cancelled'], true)) {
            check_admin_referer('dizzy_reservation_status_' . $id);
            $this->repository->update($id, ['status' => $status]);
        }

        wp_safe_redirect(admin_url('admin.php?page=dizzy-reservations'));
        exit;
    }
}
