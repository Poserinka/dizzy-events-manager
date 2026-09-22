<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

define('DIZZY_SOCIAL_VERSION', '1.10.16');
define('DIZZY_SOCIAL_PATH', __DIR__ . '/');
define('DIZZY_SOCIAL_URL', DIZZY_EVENTS_URL . 'modules/social-media/');

require_once DIZZY_SOCIAL_PATH . 'includes/Core/Autoloader.php';
\Dizzy\SocialMedia\Core\Autoloader::register();

add_action('init', static function (): void {
    \Dizzy\SocialMedia\Database\Migrations::run();
    (new \Dizzy\SocialMedia\Core\Application())->boot();
}, 20);
