<?php

declare(strict_types=1);

namespace Dizzy\Events\Models;

defined('ABSPATH') || exit;

/**
 * Event additional information.
 *
 * Represents non-core event data.
 *
 * @package Dizzy\Events\Models
 */
readonly class EventDetails
{
    public function __construct(

        public ?string $artist,

        public ?string $genre,

        public ?string $venue,

        public ?string $ticketUrl,

        public ?float $ticketPrice,

        public bool $featured,

    ) {
    }


    /**
     * Create from metadata.
     *
     * @param array<string,mixed> $meta
     */
    public static function fromMeta(
        array $meta
    ): self {

        return new self(

            artist:
                self::stringValue(
                    $meta['artist'] ?? null
                ),


            genre:
                self::stringValue(
                    $meta['genre'] ?? null
                ),


            venue:
                self::stringValue(
                    $meta['venue'] ?? null
                ),


            ticketUrl:
                self::stringValue(
                    $meta['ticket_url'] ?? null
                ),


            ticketPrice:
                isset($meta['ticket_price'])
                    ? (float) $meta['ticket_price']
                    : null,


            featured:
                ! empty(
                    $meta['featured']
                ),

        );
    }


    /**
     * Normalize string values.
     */
    private static function stringValue(
        mixed $value
    ): ?string {

        if (
            ! is_string($value)
            ||
            trim($value) === ''
        ) {
            return null;
        }


        return trim($value);
    }
}