<?php

declare(strict_types=1);

namespace Dizzy\Events\Reports\Services;

use Dizzy\Events\Reports\Repositories\ReportRepository;

defined('ABSPATH') || exit;

final class ReportService
{
    public function __construct(
        private readonly ReportRepository $repository
    ) {
    }

    public function reservations(): array
    {
        return $this->repository->getReservations();
    }

    public function attendance(): array
    {
        return $this->repository->getAttendance();
    }
}
