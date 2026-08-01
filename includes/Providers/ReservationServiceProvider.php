<?php

declare(strict_types=1);

namespace Dizzy\Events\Providers;

use Dizzy\Events\Core\Container;
use Dizzy\Events\Reservations\ReservationRepository;
use Dizzy\Events\Reservations\ReservationService;

defined('ABSPATH') || exit;

final class ReservationServiceProvider
{
    public function register(Container $container): void
    {
        $container->singleton(
            ReservationRepository::class,
            static function (): ReservationRepository {
                global $wpdb;

                return new ReservationRepository($wpdb);
            }
        );

        $container->singleton(
            ReservationService::class,
            static function () use ($container): ReservationService {
                return new ReservationService(
                    $container->get(ReservationRepository::class)
                );
            }
        );
    }
}
