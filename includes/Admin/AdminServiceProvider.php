<?php

declare(strict_types=1);

namespace Dizzy\Events\Admin;

use Dizzy\Events\Core\Container;
use Dizzy\Events\Repositories\OccurrenceRepository;
use Dizzy\Events\Reservations\ReservationRepository;
use Dizzy\Events\Services\OccurrenceService;

defined('ABSPATH') || exit;

/**
 * Registers admin services.
 *
 * @package Dizzy\Events\Admin
 */
final class AdminServiceProvider
{
    public function register(Container $container): void
    {
        $container->singleton(
            OccurrenceMetaBox::class,
            static function () use ($container): OccurrenceMetaBox {
                return new OccurrenceMetaBox(
                    $container->get(OccurrenceRepository::class),
                    $container->get(OccurrenceService::class)
                );
            }
        );

        $container->singleton(
            EventDetailsMetaBox::class,
            static function (): EventDetailsMetaBox {
                return new EventDetailsMetaBox();
            }
        );

        $container->singleton(
            AdminAssets::class,
            static function (): AdminAssets {
                return new AdminAssets();
            }
        );

        $container->singleton(
            ReservationAdmin::class,
            static function () use ($container): ReservationAdmin {
                return new ReservationAdmin(
                    $container->get(ReservationRepository::class)
                );
            }
        );

        add_action(
            'admin_init',
            static function () use ($container): void {
                $container->get(OccurrenceMetaBox::class)->register();
                $container->get(EventDetailsMetaBox::class)->register();
                $container->get(AdminAssets::class)->register();
                $container->get(ReservationAdmin::class)->register();
            }
        );
    }
}
