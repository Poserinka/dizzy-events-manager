<?php

declare(strict_types=1);

namespace Dizzy\Events\Reports\Repositories;

use wpdb;

defined('ABSPATH') || exit;

final class ReportRepository
{
    private wpdb $wpdb;

    private string $reservationTable;

    public function __construct(wpdb $wpdb)
    {
        $this->wpdb = $wpdb;
        $this->reservationTable = $wpdb->prefix . 'dizzy_event_reservations';
    }

    public function getReservations(): array
    {
        $results = $this->wpdb->get_results(
            "SELECT * FROM {$this->reservationTable} ORDER BY id DESC",
            ARRAY_A
        );

        return is_array($results) ? $results : [];
    }

    public function getAttendance(): array
    {
        return [];
    }
}
