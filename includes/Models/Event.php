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
     */
    public static function from(
        object $source
    ): static {

        $status =
            EventStatus::tryFrom(
                (string) ($source->status ?? 'draft')
            )
            ??
            EventStatus::Draft;



        return new self(

            id:
                (int) ($source->id ?? 0),


            title:
                (string) ($source->title ?? ''),


            slug:
                (string) ($source->slug ?? ''),


            content:
                (string) ($source->content ?? ''),


            status:
                $status,


            createdAt:
                new DateTimeImmutable(
                    $source->created_at
                    ??
                    'now'
                ),


            updatedAt:
                new DateTimeImmutable(
                    $source->updated_at
                    ??
                    'now'
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

            'id' =>
                $this->id,


            'title' =>
                $this->title,


            'slug' =>
                $this->slug,


            'content' =>
                $this->content,


            'status' =>
                $this->status->value,


            'created_at' =>
                $this->createdAt
                    ->format(
                        'Y-m-d H:i:s'
                    ),


            'updated_at' =>
                $this->updatedAt
                    ->format(
                        'Y-m-d H:i:s'
                    ),

        ];
    }
}