<?php

declare(strict_types=1);

namespace Dizzy\Events\Repositories;

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
            ! is_array($data['start'])
        ) {
            return;
        }



        foreach ($data['start'] as $index => $start) {


            if (
                empty($start)
            ) {
                continue;
            }


            $end =
                $data['end'][$index]
                ??
                null;



            $wpdb->insert(

                $this->table,

                [

                    'event_id' =>
                        $eventId,


                    'start_datetime' =>
                        sanitize_text_field(
                            $start
                        ),


                    'end_datetime' =>
                        $end
                        ? sanitize_text_field(
                            $end
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

    }

}