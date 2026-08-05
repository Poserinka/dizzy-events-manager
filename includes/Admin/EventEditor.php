<?php

declare(strict_types=1);

namespace Dizzy\Events\Admin;

use Dizzy\Events\Core\Config;
use WP_Post;

defined('ABSPATH') || exit;

final class EventEditor
{
    public function register(): void
    {
        add_filter('use_block_editor_for_post_type', [$this, 'disableBlockEditor'], 20, 2);
        add_filter('theme_' . Config::POST_TYPE_EVENT . '_templates', [$this, 'eventTemplates'], 20, 4);
        add_action('wp_insert_post', [$this, 'assignDefaultTemplate'], 20, 3);
        add_filter('admin_body_class', [$this, 'adminBodyClass']);
    }

    public function disableBlockEditor(bool $useBlockEditor, string $postType): bool
    {
        return $postType === Config::POST_TYPE_EVENT ? false : $useBlockEditor;
    }

    /** @param array<string,string> $templates @return array<string,string> */
    public function eventTemplates(array $templates, mixed $theme = null, mixed $post = null, string $postType = ''): array
    {
        return array_merge(wp_get_theme()->get_page_templates(null, 'page'), $templates);
    }

    public function assignDefaultTemplate(int $postId, WP_Post $post, bool $update): void
    {
        if ($post->post_type !== Config::POST_TYPE_EVENT || wp_is_post_revision($postId) !== false) {
            return;
        }

        $current = (string) get_post_meta($postId, '_wp_page_template', true);
        if ($current !== '' && $current !== 'default') {
            return;
        }

        $template = $this->findEventFullWidthTemplate();
        if ($template !== '') {
            update_post_meta($postId, '_wp_page_template', $template);
        }
    }

    public function adminBodyClass(string $classes): string
    {
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        return $screen && $screen->post_type === Config::POST_TYPE_EVENT
            ? $classes . ' dizzy-event-editor-screen'
            : $classes;
    }

    private function findEventFullWidthTemplate(): string
    {
        foreach (wp_get_theme()->get_page_templates(null, 'page') as $file => $name) {
            $normalizedName = sanitize_title((string) $name);
            $normalizedFile = sanitize_title((string) pathinfo((string) $file, PATHINFO_FILENAME));
            if ($normalizedName === 'event-full-width' || (str_contains($normalizedFile, 'event') && str_contains($normalizedFile, 'full'))) {
                return (string) $file;
            }
        }

        return '';
    }
}
