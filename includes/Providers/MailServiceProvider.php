<?php

declare(strict_types=1);

namespace Dizzy\Events\Providers;

use Dizzy\Events\Core\Container;
use Dizzy\Events\Mail\Mailer;

defined('ABSPATH') || exit;

final class MailServiceProvider
{
    public function register(Container $container): void
    {
        $container->singleton(
            Mailer::class,
            static function (): Mailer {
                return new Mailer();
            }
        );
    }
}
