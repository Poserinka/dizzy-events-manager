<?php

declare(strict_types=1);

namespace Dizzy\Events\Repositories;

use Dizzy\Events\Models\Event;
use WP_Query;
use WP_Post;

defined('ABSPATH') || exit;

/**
 * Repository for WordPress events.
 *
 * Handles retrieval of event posts.
 *
 * @package Dizzy\Events\Repositories
 */
final class EventRepository extends AbstractRepository
{
    /**
     * Event post type.
     */
    private const POST_TYPE = 'event';

    /**
     * Table is not used for WordPress posts.
     */
    protected string $table = 'posts';

    /**
     * Model handled by repository.
     *
     * @return class-string<Event>
     */
    protected function modelClass(): string
    {
        return Event::class;
    }

    /**
     * Find event by ID.
     */
    public function findById(
        int $id
    ): ?Event {

        $post = get_post($id);

        if (! $post instanceof WP_Post) {
            return null;
        }

        return $this->hydrate(
            $this->convertPost($post)
        );
    }

    /**
     * Find published events.
     *
     * @return array<Event>
     */
    public function findPublished(
        int $limit = 20
    ): array {

        $query = new WP_Query(
            [
                'post_type'      => self::POST_TYPE,
                'post_status'    => 'publish',
                'posts_per_page' => $limit,
            ]
        );

        $events = [];

        foreach ($query->posts as $post) {

            if (! $post instanceof WP_Post) {
                continue;
            }

            $events[] = $this->hydrate(
                $this->convertPost($post)
            );
        }

        return $events;
    }

    /**
     * Convert WP_Post to source object.
     */
    private function convertPost(
        WP_Post $post
    ): object {

        return (object) [
            'id' => $post->ID,

            'title' => $post->post_title,

            'slug' => $post->post_name,

            'content' => $post->post_content,

            'status' => $post->post_status,

            'created_at' => $post->post_date,

            'updated_at' => $post->post_modified,
        ];
    }
}