<?php

declare(strict_types=1);

namespace Dizzy\Events\Models;

use DateTimeImmutable;
use Dizzy\Events\Contracts\HydratesFromRow;
use Dizzy\Events\Enums\EventStatus;

defined('ABSPATH') || exit;

/**
 * Immutable event model.
 *
 * Represents a Dizzy event entity.
 *
 * @package Dizzy\Events\Models
 */
readonly class Event implements HydratesFromRow
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
     * Create event from database row.
     *
     * @param object $row Database row.
     */
    public static function fromRow(object $row): static
    {
        return new self(
            id: (int) $row->id,

            title: (string) $row->title,

            slug: (string) $row->slug,

            content: (string) ($row->content ?? ''),

            status: EventStatus::from(
                (string) $row->status
            ),

            createdAt: new DateTimeImmutable(
                $row->created_at
            ),

            updatedAt: new DateTimeImmutable(
                $row->updated_at
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

            'created_at' =>
                $this->createdAt->format('Y-m-d H:i:s'),

            'updated_at' =>
                $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}