<?php

declare(strict_types=1);

namespace Dizzy\Events\Admin;

use Dizzy\Events\Poster\Repositories\PosterRepository;
use Dizzy\Events\Poster\Services\PosterService;
use WP_Post;

defined('ABSPATH') || exit;

final class PosterAdmin
{
    public function __construct(
        private readonly PosterService $service,
        private readonly PosterRepository $repository,
    ) {
    }

    public function register(): void
    {
        add_meta_box(
            'dizzy_event_poster_generator',
            esc_html__('AI Poster Generator', 'dizzy-events-manager'),
            [$this, 'render'],
            'event',
            'side'
        );

        add_action(
            'admin_post_dizzy_generate_poster',
            [$this, 'generate']
        );
    }

    public function render(WP_Post $post): void
    {
        $poster = $this->repository->findByEvent($post->ID);

        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';

        wp_nonce_field(
            'dizzy_generate_poster_' . $post->ID,
            'dizzy_poster_nonce'
        );

        echo '<input type="hidden" name="action" value="dizzy_generate_poster">';
        echo '<input type="hidden" name="post_id" value="' . esc_attr((string) $post->ID) . '">';

        echo '<p>' . esc_html__('Generate an AI poster for this event.', 'dizzy-events-manager') . '</p>';

        if ($poster && $poster->imageUrl !== '') {
            echo '<img src="' . esc_url($poster->imageUrl) . '" style="width:100%;height:auto;" alt="">';
        }

        submit_button(
            esc_html__('Generate Poster', 'dizzy-events-manager'),
            'primary',
            'submit',
            false
        );

        echo '</form>';
    }

    public function generate(): void
    {
        if (! current_user_can('edit_posts')) {
            wp_die(esc_html__('Permission denied.', 'dizzy-events-manager'));
        }

        $postId = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;

        if (! $postId || ! isset($_POST['dizzy_poster_nonce'])) {
            wp_safe_redirect(admin_url());
            exit;
        }

        check_admin_referer(
            'dizzy_generate_poster_' . $postId,
            'dizzy_poster_nonce'
        );

        $this->service->create([
            'event_id' => $postId,
            'prompt' => $this->buildPrompt($postId),
        ]);

        wp_safe_redirect(get_edit_post_link($postId, ''));
        exit;
    }

    private function buildPrompt(int $postId): string
    {
        $title = get_the_title($postId);
        $content = wp_strip_all_tags((string) get_post_field('post_content', $postId));

        return sprintf(
            'Create a professional event poster for Jazzcafé Dizzy Rotterdam. Event: %s. Details: %s. Style: modern jazz club, premium atmosphere, live music promotion.',
            $title,
            $content
        );
    }
}
