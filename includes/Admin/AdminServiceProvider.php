<?php

declare(strict_types=1);

namespace Dizzy\Events\Admin;

use Dizzy\Events\Core\Container;
use Dizzy\Events\Repositories\OccurrenceRepository;
use Dizzy\Events\Services\OccurrenceService;

defined('ABSPATH') || exit;

/**
 * Registers admin services.
 *
 * @package Dizzy\Events\Admin
 */
final class AdminServiceProvider
{
    /**
     * Register admin services.
     */
    public function register(Container $container): void
    {
        $container->singleton(
            EventMetaBox::class,
            static function (): EventMetaBox {
                return new EventMetaBox();
            }
        );

        $container->singleton(
            OccurrenceMetaBox::class,
            static function () use ($container): OccurrenceMetaBox {
                return new OccurrenceMetaBox(
                    $container->get(
                        OccurrenceRepository::class
                    ),
                    $container->get(
                        OccurrenceService::class
                    )
                );
            }
        );

        $container->singleton(
            OccurrenceTable::class,
            static function () use ($container): OccurrenceTable {
                return new OccurrenceTable(
                    $container->get(
                        OccurrenceRepository::class
                    )
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

        add_action(
            'admin_init',
            static function () use ($container): void {
                $container
                    ->get(EventMetaBox::class)
                    ->register();

                $container
                    ->get(OccurrenceMetaBox::class)
                    ->register();

                $container
                    ->get(OccurrenceTable::class)
                    ->register();

                $container
                    ->get(EventDetailsMetaBox::class)
                    ->register();

                $container
                    ->get(AdminAssets::class)
                    ->register();
            }
        );
    }
}