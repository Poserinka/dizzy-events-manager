<?php

declare(strict_types=1);

namespace Dizzy\Events\Frontend;

use Dizzy\Events\Core\Container;
use Dizzy\Events\Frontend\Builders\EventPresentationBuilder;
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
    public function register(Container $container): void
    {
        $container->singleton(
            FrontendAssets::class,
            static function (): FrontendAssets {
                return new FrontendAssets();
            }
        );

        $container->singleton(
            EventPresentationBuilder::class,
            static function (): EventPresentationBuilder {
                return new EventPresentationBuilder();
            }
        );

        $container->singleton(
            EventShortcode::class,
            static function () use ($container): EventShortcode {
                return new EventShortcode(
                    $container->get(EventService::class)
                );
            }
        );

        $container->singleton(
            SingleEvent::class,
            static function () use ($container): SingleEvent {
                return new SingleEvent(
                    $container->get(EventService::class)
                );
            }
        );

        $container->singleton(
            EventSchema::class,
            static function () use ($container): EventSchema {
                return new EventSchema(
                    $container->get(EventService::class)
                );
            }
        );

        add_action(
            'init',
            static function () use ($container): void {
                $container->get(EventShortcode::class)->register();
            }
        );

        add_action(
            'wp',
            static function () use ($container): void {
                $container->get(FrontendAssets::class)->register();
                $container->get(EventSchema::class)->register();
            }
        );

        add_action(
            'template_redirect',
            static function () use ($container): void {
                $container->get(SingleEvent::class)->register();
            }
        );
    }
}
