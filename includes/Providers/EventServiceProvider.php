<?php

declare(strict_types=1);

namespace Dizzy\Events\Providers;

use Dizzy\Events\Core\Container;
use Dizzy\Events\Core\DB;
use Dizzy\Events\Repositories\EventRepository;
use Dizzy\Events\Repositories\OccurrenceRepository;
use Dizzy\Events\Services\EventService;
use Dizzy\Events\Services\OccurrenceService;

defined('ABSPATH') || exit;

/**
 * Registers event manager services.
 *
 * @package Dizzy\Events\Providers
 */
final class EventServiceProvider
{
    /**
     * Register event services.
     */
    public function register(Container $container): void
    {
        $container->singleton(
            EventRepository::class,
            static function (): EventRepository {
                return new EventRepository();
            }
        );

        $container->singleton(
            OccurrenceRepository::class,
            static function (): OccurrenceRepository {
                $database = DB::instance();

                return new OccurrenceRepository(
                    $database->prefix . 'dizzy_event_occurrences'
                );
            }
        );

        $container->singleton(
            OccurrenceService::class,
            static function () use ($container): OccurrenceService {
                return new OccurrenceService(
                    $container->get(
                        OccurrenceRepository::class
                    )
                );
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