<?php

declare(strict_types=1);

namespace Dizzy\Events\Social\Providers;

use Dizzy\Events\Core\Container;
use Dizzy\Events\Social\Services\SocialService;

defined('ABSPATH') || exit;

final class SocialServiceProvider
{
    public function register(Container $container): void
    {
        $container->singleton(
            SocialService::class,
            static function (): SocialService {
                return new SocialService();
            }
        );
    }
}
