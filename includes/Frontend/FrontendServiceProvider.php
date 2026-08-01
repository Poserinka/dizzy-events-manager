<?php

declare(strict_types=1);

namespace Dizzy\Events\Frontend;

use Dizzy\Events\Core\Container;
use Dizzy\Events\Repositories\OccurrenceRepository;
use Dizzy\Events\Services\EventService;

defined('ABSPATH') || exit;

/**
 * Registers frontend services.
 *
 * @package Dizzy\Events\Frontend
 */
final class FrontendServiceProvider
{
    /**
     * Register frontend services.
     */
    public function register(
        Container $container
    ): void {


        /**
         * Event shortcode.
         */
        $container->singleton(
            EventShortcode::class,
            static function () use ($container): EventShortcode {

                return new EventShortcode(

                    $container->get(
                        EventService::class
                    ),

                    $container->get(
                        OccurrenceRepository::class
                    )

                );
            }
        );



        /**
         * Single event renderer.
         */
        $container->singleton(
            SingleEvent::class,
            static function () use ($container): SingleEvent {

                return new SingleEvent(

                    $container->get(
                        EventService::class
                    )

                );
            }
        );



        /**
         * Event schema JSON-LD.
         */
        $container->singleton(
            EventSchema::class,
            static function () use ($container): EventSchema {

                return new EventSchema(

                    $container->get(
                        EventService::class
                    )

                );
            }
        );



        /**
         * Register shortcode.
         */
        add_action(
            'init',
            static function () use ($container): void {

                $container
                    ->get(EventShortcode::class)
                    ->register();

            }
        );



        /**
         * Register single event template.
         */
        add_action(
            'template_redirect',
            static function () use ($container): void {

                $container
                    ->get(SingleEvent::class)
                    ->register();

            }
        );



        /**
         * Register SEO schema.
         */
        add_action(
            'wp',
            static function () use ($container): void {

                $container
                    ->get(EventSchema::class)
                    ->register();

            }
        );

    }
}