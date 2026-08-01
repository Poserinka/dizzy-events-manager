<?php

declare(strict_types=1);

namespace Dizzy\Events\Services;

use DateTimeImmutable;
use DateTimeZone;
use Dizzy\Events\Repositories\OccurrenceRepository;

defined('ABSPATH') || exit;

/**
 * Handles event occurrence business operations.
 *
 * @package Dizzy\Events\Services
 */
final class OccurrenceService
{
    /**
     * Occurrence service constructor.
     */
    public function __construct(
        private OccurrenceRepository $repository
    ) {
    }

    /**
     * Replace all occurrences belonging to an event.
     *
     * @param array<string, mixed> $data Submitted occurrence data.
     */
    public function replaceForEvent(
        int $eventId,
        array $data
    ): void {
        if ($eventId <= 0) {
            return;
        }

        $this->repository->replaceForEvent(
            $eventId,
            $this->normalizeOccurrences($data)
        );
    }

    /**
     * Normalize submitted occurrence rows.
     *
     * @param array<string, mixed> $data Submitted occurrence data.
     *
     * @return array<int, array{
     *     start_datetime: string,
     *     end_datetime: string|null,
     *     all_day: int,
     *     timezone: string,
     *     sort_order: int,
     *     status: string
     * }>
     */
    private function normalizeOccurrences(array $data): array
    {
        $startDates = $this->getArrayValue($data, 'start_date');
        $startTimes = $this->getArrayValue($data, 'start_time');
        $endDates   = $this->getArrayValue($data, 'end_date');
        $endTimes   = $this->getArrayValue($data, 'end_time');
        $sortOrders = $this->getArrayValue($data, 'sort_order');

        $timezone     = wp_timezone();
        $timezoneName = $timezone->getName();
        $occurrences  = [];

        foreach ($startDates as $index => $startDateValue) {
            $startDate = $this->sanitizeValue($startDateValue);
            $startTime = $this->sanitizeValue($startTimes[$index] ?? '');

            if ($startDate === '' && $startTime === '') {
                continue;
            }

            if ($startDate === '' || $startTime === '') {
                continue;
            }

            $startDateTime = $this->createDateTime(
                $startDate,
                $startTime,
                $timezone
            );

            if ($startDateTime === null) {
                continue;
            }

            $endDate = $this->sanitizeValue($endDates[$index] ?? '');
            $endTime = $this->sanitizeValue($endTimes[$index] ?? '');

            if (($endDate === '') !== ($endTime === '')) {
                continue;
            }

            $endDateTime = null;

            if ($endDate !== '' && $endTime !== '') {
                $endDateTime = $this->createDateTime(
                    $endDate,
                    $endTime,
                    $timezone
                );

                if ($endDateTime === null) {
                    continue;
                }
            }

            if (
                $endDateTime !== null
                && $endDateTime < $startDateTime
            ) {
                continue;
            }

            $sortOrder = isset($sortOrders[$index])
                ? absint($sortOrders[$index])
                : (int) $index;

            $occurrences[] = [
                'start_datetime' => $startDateTime->format('Y-m-d H:i:s'),
                'end_datetime'   => $endDateTime?->format('Y-m-d H:i:s'),
                'all_day'        => 0,
                'timezone'       => $timezoneName,
                'sort_order'     => $sortOrder,
                'status'         => 'publish',
            ];
        }

        return $occurrences;
    }

    /**
     * Create a validated date and time value.
     */
    private function createDateTime(
        string $date,
        string $time,
        DateTimeZone $timezone
    ): ?DateTimeImmutable {
        $dateTime = DateTimeImmutable::createFromFormat(
            '!Y-m-d H:i',
            $date . ' ' . $time,
            $timezone
        );

        if ($dateTime === false) {
            return null;
        }

        $errors = DateTimeImmutable::getLastErrors();

        if (
            is_array($errors)
            && (
                $errors['warning_count'] > 0
                || $errors['error_count'] > 0
            )
        ) {
            return null;
        }

        if (
            $dateTime->format('Y-m-d') !== $date
            || $dateTime->format('H:i') !== $time
        ) {
            return null;
        }

        return $dateTime;
    }

    /**
     * Sanitize one submitted scalar value.
     */
    private function sanitizeValue(mixed $value): string
    {
        if (! is_scalar($value)) {
            return '';
        }

        return sanitize_text_field((string) $value);
    }

    /**
     * Get an array value from submitted data.
     *
     * @param array<string, mixed> $data Submitted occurrence data.
     *
     * @return array<int|string, mixed>
     */
    private function getArrayValue(
        array $data,
        string $key
    ): array {
        if (
            ! isset($data[$key])
            || ! is_array($data[$key])
        ) {
            return [];
        }

        return $data[$key];
    }
}
