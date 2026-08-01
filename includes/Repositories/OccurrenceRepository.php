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



            $dates =
                isset(
                    $data['start_date']
                )
                &&
                is_array(
                    $data['start_date']
                )
                    ? $data['start_date']
                    : [];



            foreach (
                $dates as $index => $date
            ) {


                $date =
                    sanitize_text_field(
                        (string) $date
                    );



                if (
                    empty($date)
                ) {
                    continue;
                }



                $time =
                    isset(
                        $data['start_time'][$index]
                    )
                        ? sanitize_text_field(
                            (string)
                            $data['start_time'][$index]
                        )
                        : '00:00';



                $start =
                    $this->createDateTime(
                        $date,
                        $time
                    );



                if (
                    ! $start
                ) {
                    continue;
                }



                $end = null;



                $endDate =
                    $data['end_date'][$index]
                    ??
                    '';



                $endTime =
                    $data['end_time'][$index]
                    ??
                    '00:00';



                if (
                    ! empty($endDate)
                ) {


                    $end =
                        $this->createDateTime(

                            sanitize_text_field(
                                (string) $endDate
                            ),

                            sanitize_text_field(
                                (string) $endTime
                            )

                        );

                }



                $sortOrder =
                    isset(
                        $data['sort_order'][$index]
                    )
                        ? absint(
                            $data['sort_order'][$index]
                        )
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


                        'all_day' =>
                            0,


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
                        '%d',
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