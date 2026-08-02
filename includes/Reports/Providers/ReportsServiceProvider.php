<?php

declare(strict_types=1);

namespace Dizzy\Events\Reports\Providers;

use Dizzy\Events\Core\Container;
use Dizzy\Events\Reports\Repositories\ReportRepository;
use Dizzy\Events\Reports\Services\ReportService;

defined('ABSPATH') || exit;

final class ReportsServiceProvider
{
    public function register(Container $container): void
    {
        $container->singleton(
            ReportRepository::class,
            static function (): ReportRepository {
                return new ReportRepository();
            }
        );

        $container->singleton(
            ReportService::class,
            static function (Container $container): ReportService {
                return new ReportService(
                    $container->get(ReportRepository::class)
                );
            }
        );
    }
}
