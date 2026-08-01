<?php

declare(strict_types=1);

namespace Dizzy\Events\Admin;

use Dizzy\Events\Repositories\OccurrenceRepository;

defined('ABSPATH') || exit;

/**
 * Displays occurrences in admin.
 *
 * @package Dizzy\Events\Admin
 */
final class OccurrenceTable
{
    public function __construct(
        private OccurrenceRepository $repository
    ) {
    }


    /**
     * Register hooks.
     */
    public function register(): void
    {
        add_action(
            'add_meta_boxes',
            [
                $this,
                'addMetaBox',
            ]
        );
    }


    /**
     * Add occurrences box.
     */
    public function addMetaBox(): void
    {
        add_meta_box(
            'dizzy_occurrence_list',
            __('Scheduled Dates', 'dizzy-events-manager'),
            [
                $this,
                'render',
            ],
            'event',
            'normal',
            'default'
        );
    }


    /**
     * Render occurrences.
     */
    public function render(
        \WP_Post $post
    ): void {

        $occurrences =
            $this->repository
                ->findByEventId(
                    $post->ID
                );


        if (empty($occurrences)) {

            echo '<p>';

            esc_html_e(
                'No dates added yet.',
                'dizzy-events-manager'
            );

            echo '</p>';

            return;
        }


        echo '<table class="widefat">';

        echo '<thead>';
        echo '<tr>';

        echo '<th>';
        esc_html_e(
            'Start',
            'dizzy-events-manager'
        );
        echo '</th>';

        echo '<th>';
        esc_html_e(
            'End',
            'dizzy-events-manager'
        );
        echo '</th>';

        echo '<th>';
        esc_html_e(
            'Status',
            'dizzy-events-manager'
        );
        echo '</th>';

        echo '</tr>';
        echo '</thead>';


        echo '<tbody>';

        foreach ($occurrences as $occurrence) {

            echo '<tr>';

            echo '<td>';

            echo esc_html(
                $occurrence
                    ->startDateTime
                    ->format(
                        'd-m-Y H:i'
                    )
            );

            echo '</td>';


            echo '<td>';

            echo esc_html(
                $occurrence
                    ->endDateTime?
                    ->format(
                        'd-m-Y H:i'
                    )
            );

            echo '</td>';


            echo '<td>';

            echo esc_html(
                $occurrence->status
            );

            echo '</td>';

            echo '</tr>';
        }

        echo '</tbody>';

        echo '</table>';
    }
}