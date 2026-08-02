<?php

declare(strict_types=1);

namespace Dizzy\Events\Admin;

use Dizzy\Events\Core\Container;
use Dizzy\Events\Poster\Services\PosterService;
use Dizzy\Events\Poster\Repositories\PosterRepository;
use Dizzy\Events\Repositories\OccurrenceRepository;
use Dizzy\Events\Reservations\ReservationRepository;
use Dizzy\Events\Services\OccurrenceService;

defined('ABSPATH') || exit;

final class AdminServiceProvider
{
    public function register(Container $container): void
    {
        $container->singleton(OccurrenceMetaBox::class, static function () use ($container): OccurrenceMetaBox {
            return new OccurrenceMetaBox($container->get(OccurrenceRepository::class), $container->get(OccurrenceService::class));
        });

        $container->singleton(EventDetailsMetaBox::class, static fn (): EventDetailsMetaBox => new EventDetailsMetaBox());
        $container->singleton(AdminAssets::class, static fn (): AdminAssets => new AdminAssets());
        $container->singleton(ReservationAdmin::class, static function () use ($container): ReservationAdmin {
            return new ReservationAdmin($container->get(ReservationRepository::class));
        });
        $container->singleton(PosterAdmin::class, static function () use ($container): PosterAdmin {
            return new PosterAdmin(
                $container->get(PosterService::class),
                $container->get(PosterRepository::class)
            );
        });
        $container->singleton(PosterSettings::class, static fn (): PosterSettings => new PosterSettings());

        $container->get(OccurrenceMetaBox::class)->register();
        $container->get(EventDetailsMetaBox::class)->register();
        $container->get(AdminAssets::class)->register();
        $container->get(ReservationAdmin::class)->register();
        $container->get(PosterAdmin::class)->register();
        $container->get(PosterSettings::class)->register();
    }
}
