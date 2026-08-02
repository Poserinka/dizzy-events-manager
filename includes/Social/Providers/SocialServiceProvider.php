<?php

declare(strict_types=1);

namespace Dizzy\Events\Social\Providers;

use Dizzy\Events\Core\Container;
use Dizzy\Events\Social\Listeners\PosterCreatedListener;
use Dizzy\Events\Social\Repositories\SocialRepository;
use Dizzy\Events\Social\Services\SocialService;

defined('ABSPATH') || exit;

final class SocialServiceProvider
{
    public function register(Container $container): void
    {
        $container->singleton(
            SocialRepository::class,
            static function (): SocialRepository {
                return new SocialRepository();
            }
        );

        $container->singleton(
            SocialService::class,
            static function (Container $container): SocialService {
                return new SocialService(
                    $container->get(SocialRepository::class)
                );
            }
        );

        (new PosterCreatedListener(
            $container->get(SocialService::class)
        ))->register();
    }
}
