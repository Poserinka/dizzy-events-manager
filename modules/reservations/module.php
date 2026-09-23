<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

define('DIZZY_RESERVATIONS_VERSION', '3.12.5');
define('DIZZY_RESERVATIONS_PATH', __DIR__ . '/');
define('DIZZY_RESERVATIONS_URL', DIZZY_EVENTS_URL . 'modules/reservations/');

require_once DIZZY_RESERVATIONS_PATH . 'includes/Autoloader.php';
\Dizzy\Reservations\Autoloader::register();

// Load runtime dependencies deterministically. This also protects production
// installations where an opcode cache does not immediately discover new files.
foreach ([
    'ControllerRole.php',
    'ReservationRepository.php',
    'TableRepository.php',
    'EventGateway.php',
    'TicketGateway.php',
    'ReservationService.php',
    'FrontendController.php',
    'MobileApiController.php',
    'AdminController.php',
    'TablesAdminController.php',
    'Plugin.php',
] as $dependency) {
    require_once DIZZY_RESERVATIONS_PATH . 'includes/' . $dependency;
}

add_action('init', static function (): void {
    \Dizzy\Reservations\Database\Migrations::run();
    (new \Dizzy\Reservations\Plugin())->boot();
}, 20);
