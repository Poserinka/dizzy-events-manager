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
                    ORDER BY sort_order ASC, start_datetime ASC
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
     * Replace all occurrences for event.
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


                    $end =
                        $this->createDateTime(

                            $data['end_date'][$index],

                            $data['end_time'][$index]
                            ??
                            '00:00'

                        );

                }




                $sortOrder =
                    isset(
                        $data['sort_order'][$index]
                    )
                    ? (int)
                        $data['sort_order'][$index]
                    : $index;





                $wpdb->insert(

                    $this->table,

                    [

                        'event_id' =>
                            $eventId,


                        'start_datetime' =>
                            $start->format(
                                'Y-m-d H:i:s'
                            ),


                        'end_datetime' =>
                            $end
                                ? $end->format(
                                    'Y-m-d H:i:s'
                                )
                                : null,


                        'timezone' =>
                            'Europe/Amsterdam',


                        'sort_order' =>
                            $sortOrder,


                        'status' =>
                            'publish',


                        'created_at' =>
                            current_time(
                                'mysql',
                                true
                            ),


                        'updated_at' =>
                            current_time(
                                'mysql',
                                true
                            ),

                    ],


                    [

                        '%d',

                        '%s',

                        '%s',

                        '%s',

                        '%d',

                        '%s',

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
     * Create DateTime object.
     */
    private function createDateTime(
        string $date,
        string $time
    ): ?DateTimeImmutable {


        try {


            return new DateTimeImmutable(

                $date . ' ' . $time,

                new DateTimeZone(
                    'Europe/Amsterdam'
                )

            );


        } catch (\Exception) {


            return null;

        }

    }

}