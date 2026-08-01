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
    /**
     * Create event details.
     */
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
     * Create from WordPress metadata.
     *
     * Supports both normalized keys and raw get_post_meta() output.
     *
     * @param array<string, mixed> $meta Event metadata.
     */
    public static function fromMeta(array $meta): self
    {
        return new self(
            artist: self::stringValue(
                self::metaValue($meta, 'artist')
            ),
            genre: self::stringValue(
                self::metaValue($meta, 'genre')
            ),
            venue: self::stringValue(
                self::metaValue($meta, 'venue')
            ),
            ticketUrl: self::stringValue(
                self::metaValue($meta, 'ticket_url')
            ),
            ticketPrice: self::floatValue(
                self::metaValue($meta, 'ticket_price')
            ),
            featured: self::boolValue(
                self::metaValue($meta, 'featured')
            ),
        );
    }

    /**
     * Get a normalized metadata value.
     *
     * @param array<string, mixed> $meta Metadata values.
     */
    private static function metaValue(array $meta, string $key): mixed
    {
        $value = $meta[$key] ?? $meta['_dizzy_' . $key] ?? null;

        if (is_array($value)) {
            return $value[0] ?? null;
        }

        return $value;
    }

    /**
     * Normalize a string value.
     */
    private static function stringValue(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    /**
     * Normalize a float value.
     */
    private static function floatValue(mixed $value): ?float
    {
        if (! is_scalar($value) || $value === '') {
            return null;
        }

        return is_numeric($value) ? (float) $value : null;
    }

    /**
     * Normalize a boolean value.
     */
    private static function boolValue(mixed $value): bool
    {
        return in_array(
            $value,
            [true, 1, '1', 'true', 'yes', 'on'],
            true
        );
    }
}
