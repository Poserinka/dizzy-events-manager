<?php

declare(strict_types=1);

namespace Dizzy\Events\Admin;

use Dizzy\Events\Core\Container;

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
    public function register(
        Container $container
    ): void {

        $container->singleton(
            EventMetaBox::class,
            static function (): EventMetaBox {

                return new EventMetaBox();
            }
        );

        add_action(
            'admin_init',
            static function () use ($container): void {

                $container
                    ->get(EventMetaBox::class)
                    ->register();

            }
        );
    }
}