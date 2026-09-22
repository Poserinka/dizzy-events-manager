<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

define('DIZZY_TICKETS_VERSION', '1.11.0');
define('DIZZY_TICKETS_PATH', __DIR__ . '/');

require_once DIZZY_TICKETS_PATH . 'includes/Autoloader.php';
\Dizzy\Tickets\Autoloader::register();

add_action('init', static function (): void {
    \Dizzy\Tickets\Database\Migrations::run();
    (new \Dizzy\Tickets\Plugin())->boot();
}, 20);
