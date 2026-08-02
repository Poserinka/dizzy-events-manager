<?php

declare(strict_types=1);

namespace Dizzy\Events\Reservations;

use RuntimeException;
use wpdb;

defined('ABSPATH') || exit;

final class ReservationRepository
{
    private wpdb $wpdb;

    private string $table;

    public function __construct(wpdb $wpdb)
    {
        $this->wpdb = $wpdb;
        $this->table = $wpdb->prefix . 'dizzy_event_reservations';
    }

    public function all(): array
    {
        $results = $this->wpdb->get_results(
            "SELECT * FROM {$this->table} ORDER BY id DESC",
            ARRAY_A
        );

        return is_array($results) ? $results : [];
    }

    public function find(int $reservationId): ?array
    {
        $result = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->table} WHERE id = %d LIMIT 1",
                $reservationId
            ),
            ARRAY_A
        );

        return is_array($result) ? $result : null;
    }

    public function save(array $data): int
    {
        $now = current_time('mysql', true);

        $record = [
            'event_id'      => (int) ($data['event_id'] ?? 0),
            'occurrence_id' => ! empty($data['occurrence_id'])
                ? (int) $data['occurrence_id']
                : null,
            'name'           => (string) ($data['name'] ?? ''),
            'email'          => (string) ($data['email'] ?? ''),
            'phone'          => isset($data['phone']) ? (string) $data['phone'] : null,
            'guests'         => max(1, (int) ($data['guests'] ?? 1)),
            'status'         => (string) ($data['status'] ?? 'pending'),
            'notes'          => isset($data['notes']) ? (string) $data['notes'] : null,
            'created_at'     => $now,
            'updated_at'     => $now,
        ];

        if ($this->wpdb->insert($this->table, $record) === false) {
            throw new RuntimeException(
                'Could not create reservation: ' . $this->wpdb->last_error
            );
        }

        return (int) $this->wpdb->insert_id;
    }

    public function update(int $reservationId, array $data): bool
    {
        $data['updated_at'] = current_time('mysql', true);

        return false !== $this->wpdb->update($this->table, $data, ['id' => $reservationId]);
    }

    public function delete(int $reservationId): bool
    {
        return false !== $this->wpdb->delete($this->table, ['id' => $reservationId]);
    }
}
