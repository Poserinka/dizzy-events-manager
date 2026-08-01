<?php

declare(strict_types=1);

namespace Dizzy\Events\Admin;

use Dizzy\Events\Core\Container;
use Dizzy\Events\Repositories\OccurrenceRepository;

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


        /**
         * Event meta box.
         */
        $container->singleton(
            EventMetaBox::class,
            static function (): EventMetaBox {

                return new EventMetaBox();

            }
        );



        /**
         * Occurrence meta box.
         */
        $container->singleton(
            OccurrenceMetaBox::class,
            static function () use ($container): OccurrenceMetaBox {

                return new OccurrenceMetaBox(

                    $container->get(
                        OccurrenceRepository::class
                    )

                );

            }
        );



        /**
         * Occurrence table.
         */
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



        /**
         * Event details meta box.
         */
        $container->singleton(
            EventDetailsMetaBox::class,
            static function (): EventDetailsMetaBox {

                return new EventDetailsMetaBox();

            }
        );



        /**
         * Admin assets.
         */
        $container->singleton(
            AdminAssets::class,
            static function (): AdminAssets {

                return new AdminAssets();

            }
        );



        /**
         * Register admin hooks.
         */
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