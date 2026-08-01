<?php

declare(strict_types=1);

namespace Dizzy\Events\Frontend\ViewModels;

use Dizzy\Events\Models\Event;

defined('ABSPATH') || exit;

/**
 * Frontend event presentation data.
 *
 * @package Dizzy\Events\Frontend\ViewModels
 */
readonly class EventViewData
{
    public function __construct(
        public int $id,
        public string $title,
        public string $url,
        public string $image,
        public string $excerpt,
    ) {
    }


    /**
     * Create view data from Event model.
     */
    public static function from(
        Event $event
    ): self {

        return new self(

            id: $event->id,

            title: $event->title,

            url: get_permalink(
                $event->id
            ),

            image: get_the_post_thumbnail_url(
                $event->id,
                'large'
            ) ?: '',

            excerpt: $event->excerpt ?? '',

        );
    }
}