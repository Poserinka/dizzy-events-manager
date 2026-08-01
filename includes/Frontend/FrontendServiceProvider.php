<?php

declare(strict_types=1);

namespace Dizzy\Events\Frontend;

use Dizzy\Events\Core\Container;
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

        $container->singleton(
            EventShortcode::class,
            static function () use ($container): EventShortcode {

                return new EventShortcode(
                    $container->get(
                        EventService::class
                    )
                );
            }
        );


        add_action(
            'init',
            static function () use ($container): void {

                $container
                    ->get(EventShortcode::class)
                    ->register();

            }
        );
    }
}