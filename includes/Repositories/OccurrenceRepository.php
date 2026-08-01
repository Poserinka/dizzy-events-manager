<?php

declare(strict_types=1);

namespace Dizzy\Events\Repositories;

use DateTimeImmutable;
use DateTimeZone;
use Dizzy\Events\Models\Occurrence;

defined('ABSPATH') || exit;

/**
 * Handles event occurrences persistence.
 *
 * @package Dizzy\Events\Repositories
 */
final class OccurrenceRepository
{
    public function __construct(
        private string $table
    ) {
    }



    /**
     * Find occurrences by event.
     *
     * @return array<Occurrence>
     */
    public function findByEventId(
        int $eventId
    ): array {

        global $wpdb;


        $rows =
            $wpdb->get_results(
                $wpdb->prepare(
                    "
                    SELECT *
                    FROM {$this->table}
                    WHERE event_id = %d
                    ORDER BY start_datetime ASC
                    ",
                    $eventId
                )
            );


        return array_map(
            static function ($row): Occurrence {

                return Occurrence::hydrateFromRow(
                    $row
                );

            },
            $rows
        );
    }



    /**
     * Replace all occurrences.
     *
     * @param array<string,mixed> $data
     */
    public function replaceForEvent(
        int $eventId,
        array $data
    ): void {


        global $wpdb;


        $wpdb->query(
            'START TRANSACTION'
        );


        try {


            $wpdb->delete(
                $this->table,
                [
                    'event_id' => $eventId,
                ],
                [
                    '%d',
                ]
            );



            if (
                empty($data['start'])
                ||
                ! is_array(
                    $data['start']
                )
            ) {

                $wpdb->query(
                    'COMMIT'
                );

                return;
            }



            foreach ($data['start'] as $index => $start) {


                if (
                    empty($start)
                ) {
                    continue;
                }


                $startDate =
                    $this->parseDate(
                        $start
                    );


                if (
                    ! $startDate
                ) {
                    continue;
                }



                $endDate = null;



                if (
                    ! empty(
                        $data['end'][$index]
                    )
                ) {

                    $endDate =
                        $this->parseDate(
                            $data['end'][$index]
                        );

                }



                $wpdb->insert(

                    $this->table,

                    [

                        'event_id' =>
                            $eventId,


                        'start_datetime' =>
                            $startDate
                                ->format(
                                    'Y-m-d H:i:s'
                                ),


                        'end_datetime' =>
                            $endDate
                                ? $endDate->format(
                                    'Y-m-d H:i:s'
                                )
                                : null,

                    ],


                    [

                        '%d',
                        '%s',
                        '%s',

                    ]

                );

            }


            $wpdb->query(
                'COMMIT'
            );


        } catch (\Throwable $e) {


            $wpdb->query(
                'ROLLBACK'
            );


            throw $e;

        }

    }




    /**
     * Convert input datetime.
     */
    private function parseDate(
        string $value
    ): ?DateTimeImmutable {


        try {


            return new DateTimeImmutable(

                $value,

                new DateTimeZone(
                    'Europe/Amsterdam'
                )

            );


        } catch (\Exception) {


            return null;

        }

    }

}