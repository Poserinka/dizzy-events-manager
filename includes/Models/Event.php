<?php

declare(strict_types=1);

namespace Dizzy\Events\Models;

use DateTimeImmutable;
use Dizzy\Events\Contracts\Hydrates;
use Dizzy\Events\Enums\EventStatus;

defined('ABSPATH') || exit;

/**
 * Immutable event model.
 *
 * Represents a Dizzy event entity.
 *
 * @package Dizzy\Events\Models
 */
readonly class Event implements Hydrates
{
    public function __construct(
        public int $id,
        public string $title,
        public string $slug,
        public string $content,
        public EventStatus $status,
        public DateTimeImmutable $createdAt,
        public DateTimeImmutable $updatedAt,
    ) {
    }

    /**
     * Create event from source object.
     *
     * @param object $source Source object.
     */
    public static function from(object $source): static
    {
        return new self(
            id: (int) $source->id,

            title: (string) $source->title,

            slug: (string) $source->slug,

            content: (string) ($source->content ?? ''),

            status: EventStatus::from(
                (string) $source->status
            ),

            createdAt: new DateTimeImmutable(
                $source->created_at
            ),

            updatedAt: new DateTimeImmutable(
                $source->updated_at
            ),
        );
    }

    /**
     * Convert event to array.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,

            'title' => $this->title,

            'slug' => $this->slug,

            'content' => $this->content,

            'status' => $this->status->value,

            'created_at' => $this->createdAt
                ->format('Y-m-d H:i:s'),

            'updated_at' => $this->updatedAt
                ->format('Y-m-d H:i:s'),
        ];
    }
}