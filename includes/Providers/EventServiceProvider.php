<?php

declare(strict_types=1);

namespace Dizzy\Events\Providers;

use Dizzy\Events\Core\Container;
use Dizzy\Events\Repositories\EventRepository;
use Dizzy\Events\Repositories\OccurrenceRepository;
use Dizzy\Events\Services\EventService;

defined('ABSPATH') || exit;

/**
 * Registers event manager services.
 *
 * @package Dizzy\Events\Providers
 */
final class EventServiceProvider
{
    /**
     * Register services.
     */
    public function register(
        Container $container
    ): void {

        $container->singleton(
            EventRepository::class,
            static function (): EventRepository {

                return new EventRepository();
            }
        );

        $container->singleton(
            OccurrenceRepository::class,
            static function (): OccurrenceRepository {

                return new OccurrenceRepository();
            }
        );

        $container->singleton(
            EventService::class,
            static function () use ($container): EventService {

                return new EventService(
                    $container->get(
                        EventRepository::class
                    ),
                    $container->get(
                        OccurrenceRepository::class
                    )
                );
            }
        );
    }
}