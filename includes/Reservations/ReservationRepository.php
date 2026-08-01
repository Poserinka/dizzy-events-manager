<?php

declare(strict_types=1);

namespace Dizzy\Events\Reservations;

use wpdb;

defined('ABSPATH') || exit;

final class ReservationRepository
{
    private wpdb $wpdb;

    private string $table;

    public function __construct(wpdb $wpdb)
    {
        $this->wpdb  = $wpdb;
        $this->table = $wpdb->prefix . 'dizzy_event_reservations';
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
        $this->wpdb->insert(
            $this->table,
            $data
        );

        return (int) $this->wpdb->insert_id;
    }

    public function update(int $reservationId, array $data): bool
    {
        return false !== $this->wpdb->update(
            $this->table,
            $data,
            [
                'id' => $reservationId,
            ]
        );
    }

    public function delete(int $reservationId): bool
    {
        return false !== $this->wpdb->delete(
            $this->table,
            [
                'id' => $reservationId,
            ]
        );
    }
}
