<?php

declare(strict_types=1);

define('ABSPATH', __DIR__ . '/wordpress/');
define('WP_PLUGIN_DIR', dirname(__DIR__, 2));
define('MINUTE_IN_SECONDS', 60);
define('DAY_IN_SECONDS', 86400);

function plugin_dir_path(string $file): string { return dirname($file) . '/'; }
function plugin_dir_url(string $file): string { return 'https://example.test/plugins/' . basename(dirname($file)) . '/'; }
function plugin_basename(string $file): string { return basename(dirname($file)) . '/' . basename($file); }
function add_action(string $hook, mixed $callback, int $priority = 10, int $acceptedArgs = 1): bool { return true; }
function add_filter(string $hook, mixed $callback, int $priority = 10, int $acceptedArgs = 1): bool { return true; }
function register_activation_hook(string $file, mixed $callback): void {}
function register_deactivation_hook(string $file, mixed $callback): void {}
function get_option(string $option, mixed $default = false): mixed { return $option === 'active_plugins' ? [] : $default; }
function get_site_option(string $option, mixed $default = false): mixed { return $default; }
function is_multisite(): bool { return false; }
function __(string $text, ?string $domain = null): string { return $text; }
function sanitize_key(string $key): string { return strtolower((string) preg_replace('/[^a-z0-9_\-]/i', '', $key)); }

require dirname(__DIR__) . '/dizzy-events-manager.php';

$expected = [
    'DIZZY_EVENTS_VERSION',
    'DIZZY_NL_VERSION',
    'DIZZY_RESERVATIONS_VERSION',
    'DIZZY_SCHEDULE_VERSION',
    'DIZZY_SOCIAL_VERSION',
    'DIZZY_TICKETS_VERSION',
    'DIZZY_WANOTIFY_VERSION',
];

foreach ($expected as $constant) {
    if (! defined($constant)) {
        throw new RuntimeException('Missing module constant: ' . $constant);
    }
}

foreach ([
    \Dizzy\Reservations\EventGateway::class,
    \Dizzy\Reservations\TicketGateway::class,
    \Dizzy\Reservations\Plugin::class,
] as $class) {
    if (! class_exists($class)) {
        throw new RuntimeException('Missing bundled Reservations class: ' . $class);
    }
}

echo "Dizzy Suite bootstrap smoke test passed.\n";
