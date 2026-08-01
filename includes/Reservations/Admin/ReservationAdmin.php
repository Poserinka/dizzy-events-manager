<?php

declare(strict_types=1);

namespace Dizzy\Events\Reservations\Admin;

defined('ABSPATH') || exit;

/**
 * Registers reservation related admin functionality.
 *
 * @package Dizzy\Events\Reservations\Admin
 */
final class ReservationAdmin
{
    /**
     * Register admin hooks.
     */
    public function register(): void
    {
        add_action(
            'admin_menu',
            [$this, 'registerMenu']
        );
    }

    /**
     * Register reservation admin menu.
     */
    public function registerMenu(): void
    {
        // Reservation admin pages will be registered here.
    }
}
