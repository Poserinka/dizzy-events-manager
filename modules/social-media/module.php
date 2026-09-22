<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

define('DIZZY_SOCIAL_VERSION', '1.10.21');
define('DIZZY_SOCIAL_PATH', __DIR__ . '/');
define('DIZZY_SOCIAL_URL', DIZZY_EVENTS_URL . 'modules/social-media/');

add_action('admin_enqueue_scripts', static function (): void {
    $page = sanitize_key((string) ($_GET['page'] ?? ''));
    if (! in_array($page, ['dizzy-social-media', 'dizzy-poster-settings', 'dizzy-social-accounts', 'dizzy-social-templates', 'dizzy-social-autopost'], true)) {
        return;
    }
    wp_enqueue_style('dizzy-social-admin-layout', DIZZY_SOCIAL_URL . 'assets/admin-layout.css', [], DIZZY_SOCIAL_VERSION);
});

require_once DIZZY_SOCIAL_PATH . 'includes/Core/Autoloader.php';
\Dizzy\SocialMedia\Core\Autoloader::register();

add_action('init', static function (): void {
    \Dizzy\SocialMedia\Database\Migrations::run();
    (new \Dizzy\SocialMedia\Core\Application())->boot();
}, 20);
