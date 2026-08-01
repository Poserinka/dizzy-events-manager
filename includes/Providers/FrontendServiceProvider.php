<?php

declare(strict_types=1);

namespace Dizzy\Events\Providers;

use Dizzy\Events\Core\Container;
use Dizzy\Events\Frontend\ReservationController;

defined('ABSPATH') || exit;

final class FrontendServiceProvider
{
    public function register(Container $container): void
    {
        $container->singleton(
            ReservationController::class,
            static function () use ($container): ReservationController {
                return new ReservationController(
                    $container->get(\Dizzy\Events\Reservations\ReservationService::class)
                );
            }
        );

        add_action(
            'init',
            static function () use ($container): void {
                $container
                    ->get(ReservationController::class)
                    ->register();
            }
        );
    }
}
