<?php

declare(strict_types=1);

namespace Dizzy\Events\Poster\Providers;

use Dizzy\Events\Core\Container;
use Dizzy\Events\Poster\Repositories\PosterRepository;
use Dizzy\Events\Poster\Services\PosterService;

defined('ABSPATH') || exit;

final class PosterServiceProvider
{
    public function register(Container $container): void
    {
        $container->singleton(
            PosterRepository::class,
            static function (): PosterRepository {
                return new PosterRepository();
            }
        );

        $container->singleton(
            PosterService::class,
            static function (Container $container): PosterService {
                return new PosterService(
                    $container->get(PosterRepository::class)
                );
            }
        );
    }
}
