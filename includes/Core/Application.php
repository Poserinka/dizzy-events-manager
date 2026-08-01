<?php

declare(strict_types=1);

namespace Dizzy\Events\Core;

use Dizzy\Events\Admin\AdminServiceProvider;
use Dizzy\Events\Providers\EventServiceProvider;

defined('ABSPATH') || exit;

/**
 * Application bootstrap.
 *
 * Creates and manages application services.
 *
 * @package Dizzy\Events\Core
 */
final class Application
{
    /**
     * Service container.
     */
    private Container $container;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->container = new Container();
    }

    /**
     * Boot application.
     */
    public function boot(): void
    {
        $this->registerProviders();
    }

    /**
     * Get container.
     */
    public function container(): Container
    {
        return $this->container;
    }

    /**
     * Register providers.
     */
    private function registerProviders(): void
    {
        $providers = [
            EventServiceProvider::class,
            AdminServiceProvider::class,
        ];

        foreach ($providers as $provider) {

            (new $provider())
                ->register($this->container);
        }
    }
}