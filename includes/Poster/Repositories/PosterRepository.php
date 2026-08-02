<?php

declare(strict_types=1);

namespace Dizzy\Events\Poster\Repositories;

use Dizzy\Events\Poster\Models\Poster;

defined('ABSPATH') || exit;

final class PosterRepository
{
    public function create(array $data): Poster
    {
        global $wpdb;

        $table = $wpdb->prefix . 'dizzy_event_posters';

        $now = current_time('mysql');

        $wpdb->insert(
            $table,
            [
                'event_id'   => $data['event_id'] ?? null,
                'prompt'     => $data['prompt'] ?? '',
                'image_url'  => $data['image_url'] ?? '',
                'provider'   => $data['provider'] ?? '',
                'status'     => $data['status'] ?? 'draft',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                '%d',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
            ]
        );

        return $this->find((int) $wpdb->insert_id);
    }

    public function find(int $id): Poster
    {
        global $wpdb;

        $table = $wpdb->prefix . 'dizzy_event_posters';

        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE id = %d",
                $id
            ),
            ARRAY_A
        );

        return new Poster(
            (int) ($row['id'] ?? 0),
            isset($row['event_id']) ? (int) $row['event_id'] : null,
            (string) ($row['prompt'] ?? ''),
            (string) ($row['image_url'] ?? ''),
            (string) ($row['status'] ?? 'draft'),
        );
    }
}
