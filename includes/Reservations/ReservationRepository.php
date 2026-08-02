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

    private string $occurrencesTable;

    public function __construct(wpdb $wpdb)
    {
        $this->wpdb = $wpdb;
        $this->table = $wpdb->prefix . 'dizzy_event_reservations';
        $this->occurrencesTable = $wpdb->prefix . 'dizzy_event_occurrences';
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
        $occurrenceId = (int) ($data['occurrence_id'] ?? 0);

        if ($occurrenceId <= 0) {
            return $this->insert($data);
        }

        if ($this->wpdb->query('START TRANSACTION') === false) {
            throw new RuntimeException('Could not start reservation transaction.');
        }

        try {
            $occurrence = $this->wpdb->get_row(
                $this->wpdb->prepare(
                    "SELECT event_id, capacity FROM {$this->occurrencesTable} WHERE id = %d FOR UPDATE",
                    $occurrenceId
                ),
                ARRAY_A
            );

            if (! is_array($occurrence) || (int) $occurrence['event_id'] !== (int) ($data['event_id'] ?? 0)) {
                throw new RuntimeException('The selected event date is not available.');
            }

            $capacity = (int) ($occurrence['capacity'] ?? 0);

            if ($capacity > 0) {
                $reserved = (int) $this->wpdb->get_var(
                    $this->wpdb->prepare(
                        "SELECT COALESCE(SUM(guests), 0) FROM {$this->table} WHERE occurrence_id = %d AND status IN (%s, %s)",
                        $occurrenceId,
                        'pending',
                        'confirmed'
                    )
                );

                if ($reserved + (int) ($data['guests'] ?? 1) > $capacity) {
                    $data['status'] = 'waitlisted';
                }
            }

            $reservationId = $this->insert($data);

            if ($this->wpdb->query('COMMIT') === false) {
                throw new RuntimeException('Could not commit reservation transaction.');
            }

            return $reservationId;
        } catch (\Throwable $exception) {
            $this->wpdb->query('ROLLBACK');
            throw $exception;
        }
    }

    private function insert(array $data): int
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

    public function updateStatus(int $reservationId, string $status): bool
    {
        $reservation = $this->find($reservationId);

        if ($reservation === null) {
            return false;
        }

        $occurrenceId = (int) ($reservation['occurrence_id'] ?? 0);

        if ($occurrenceId <= 0 || ! in_array($status, ['pending', 'confirmed'], true)) {
            return $this->update($reservationId, ['status' => $status]);
        }

        if ($this->wpdb->query('START TRANSACTION') === false) {
            throw new RuntimeException('Could not start reservation status transaction.');
        }

        try {
            $occurrence = $this->wpdb->get_row(
                $this->wpdb->prepare(
                    "SELECT capacity FROM {$this->occurrencesTable} WHERE id = %d FOR UPDATE",
                    $occurrenceId
                ),
                ARRAY_A
            );

            if (! is_array($occurrence)) {
                $this->wpdb->query('ROLLBACK');
                return false;
            }

            $capacity = (int) ($occurrence['capacity'] ?? 0);

            if ($capacity > 0) {
                $reserved = (int) $this->wpdb->get_var(
                    $this->wpdb->prepare(
                        "SELECT COALESCE(SUM(guests), 0) FROM {$this->table} WHERE occurrence_id = %d AND id <> %d AND status IN (%s, %s)",
                        $occurrenceId,
                        $reservationId,
                        'pending',
                        'confirmed'
                    )
                );

                if ($reserved + (int) ($reservation['guests'] ?? 1) > $capacity) {
                    $this->wpdb->query('ROLLBACK');
                    return false;
                }
            }

            $updated = $this->update($reservationId, ['status' => $status]);

            if (! $updated || $this->wpdb->query('COMMIT') === false) {
                throw new RuntimeException('Could not commit reservation status transaction.');
            }

            return true;
        } catch (\Throwable $exception) {
            $this->wpdb->query('ROLLBACK');
            throw $exception;
        }
    }

    public function delete(int $reservationId): bool
    {
        return false !== $this->wpdb->delete($this->table, ['id' => $reservationId]);
    }
}

