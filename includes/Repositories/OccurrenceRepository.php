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
     * Replace occurrences.
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
                empty(
                    $data['start_date']
                )
                ||
                ! is_array(
                    $data['start_date']
                )
            ) {

                $wpdb->query(
                    'COMMIT'
                );

                return;

            }



            foreach (
                $data['start_date']
                as $index => $date
            ) {


                if (
                    empty($date)
                ) {
                    continue;
                }



                $startTime =
                    $data['start_time'][$index]
                    ??
                    '00:00';



                $start =
                    $this->createDateTime(
                        $date,
                        $startTime
                    );



                if (
                    ! $start
                ) {
                    continue;
                }



                $end = null;



                if (
                    ! empty(
                        $data['end_date'][$index]
                    )
                ) {


                    $endDate =
                        $data['end_date'][$index];



                    $endTime =
                        $data['end_time'][$index]
                        ??
                        '00:00';



                    $end =
                        $this->createDateTime(
                            $endDate,
                            $endTime
                        );

                }



                $wpdb->insert(

                    $this->table,

                    [

                        'event_id' =>
                            $eventId,


                        'start_datetime' =>
                            $start
                                ->format(
                                    'Y-m-d H:i:s'
                                ),


                        'end_datetime' =>
                            $end
                                ? $end->format(
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
     * Create datetime object.
     */
    private function createDateTime(
        string $date,
        string $time
    ): ?DateTimeImmutable {


        try {


            return new DateTimeImmutable(

                $date .
                ' ' .
                $time,

                new DateTimeZone(
                    'Europe/Amsterdam'
                )

            );


        } catch (\Exception) {


            return null;

        }

    }

}