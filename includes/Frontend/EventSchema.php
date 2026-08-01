<?php

declare(strict_types=1);

namespace Dizzy\Events\Frontend;

use Dizzy\Events\Models\EventDetails;
use Dizzy\Events\Services\EventService;

defined('ABSPATH') || exit;

/**
 * Generates Schema.org Event data.
 *
 * @package Dizzy\Events\Frontend
 */
final class EventSchema
{
    public function __construct(
        private EventService $service
    ) {
    }


    /**
     * Register hooks.
     */
    public function register(): void
    {
        add_action(
            'wp_head',
            [
                $this,
                'render',
            ]
        );
    }


    /**
     * Output JSON-LD.
     */
    public function render(): void
    {
        if (
            ! is_singular('event')
        ) {
            return;
        }


        global $post;


        if (
            ! $post
        ) {
            return;
        }


        $data =
            $this->service
                ->getEvent(
                    $post->ID
                );


        if (! $data) {
            return;
        }


        $event =
            $data['event'];


        $occurrences =
            $data['occurrences'];


        $details =
            EventDetails::fromMeta(
                get_post_meta(
                    $event->id
                )
            );


        $schema = [

            '@context' =>
                'https://schema.org',


            '@type' =>
                'MusicEvent',


            'name' =>
                $event->title,


            'description' =>
                wp_strip_all_tags(
                    $event->content
                ),


            'image' =>
                get_the_post_thumbnail_url(
                    $event->id,
                    'large'
                ),


            'location' => [

                '@type' =>
                    'Place',

                'name' =>
                    $details->venue
                    ??
                    get_bloginfo('name'),

            ],

        ];



        if (
            ! empty($occurrences)
        ) {

            $schema['startDate'] =
                $occurrences[0]
                    ->startDateTime
                    ->format(
                        DATE_ATOM
                    );
        }



        if (
            $details->ticketUrl
        ) {

            $schema['offers'] = [

                '@type' =>
                    'Offer',

                'url' =>
                    $details->ticketUrl,


                'price' =>
                    $details->ticketPrice,

                'priceCurrency' =>
                    'EUR',

            ];
        }


        echo '<script type="application/ld+json">';

        echo wp_json_encode(
            $schema,
            JSON_UNESCAPED_SLASHES
        );

        echo '</script>';

    }
}