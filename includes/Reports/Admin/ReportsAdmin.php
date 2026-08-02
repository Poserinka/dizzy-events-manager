<?php

declare(strict_types=1);

namespace Dizzy\Events\Reports\Admin;

use Dizzy\Events\Reports\Services\ReportService;

defined('ABSPATH') || exit;

final class ReportsAdmin
{
    public function __construct(
        private readonly ReportService $service
    ) {
    }

    public function register(): void
    {
        add_action('admin_menu', [$this, 'registerMenu']);
    }

    public function registerMenu(): void
    {
        add_menu_page(
            'Reports',
            'Reports',
            'manage_options',
            'dizzy-reports',
            [$this, 'render'],
            'dashicons-chart-bar'
        );
    }

    public function render(): void
    {
        echo '<div class="wrap"><h1>Reports</h1>';
        echo '<p>Reports system migrated to the new domain architecture.</p>';
        echo '</div>';
    }
}
