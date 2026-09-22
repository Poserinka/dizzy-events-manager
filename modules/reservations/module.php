<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

define('DIZZY_RESERVATIONS_VERSION', '3.12.1');
define('DIZZY_RESERVATIONS_PATH', __DIR__ . '/');
define('DIZZY_RESERVATIONS_URL', DIZZY_EVENTS_URL . 'modules/reservations/');

require_once DIZZY_RESERVATIONS_PATH . 'includes/Autoloader.php';
\Dizzy\Reservations\Autoloader::register();

add_action('init', static function (): void {
    \Dizzy\Reservations\Database\Migrations::run();
    (new \Dizzy\Reservations\Plugin())->boot();
}, 20);
