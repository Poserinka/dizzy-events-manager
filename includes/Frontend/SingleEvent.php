<?php

declare(strict_types=1);

namespace Dizzy\Events\Frontend;

use Dizzy\Events\Models\Occurrence;
use Dizzy\Events\Services\EventService;
use WP_Post;

defined('ABSPATH') || exit;

/**
 * Handles single event display.
 *
 * @package Dizzy\Events\Frontend
 */
final class SingleEvent
{
    /**
     * Create the single event renderer.
     */
    public function __construct(
        private EventService $service
    ) {
    }

    /**
     * Register hooks.
     */
    public function register(): void
    {
        add_filter(
            'template_include',
            [
                $this,
                'template',
            ]
        );
    }

    /**
     * Load custom template.
     */
    public function template(string $template): string
    {
        if (! is_singular('event')) {
            return $template;
        }

        global $post;

        if (! $post instanceof WP_Post) {
            return $template;
        }

        $data = $this->service->getEvent((int) $post->ID);

        if ($data === null) {
            return $template;
        }

        $data['upcomingOccurrences'] = $this->filterAndSortOccurrences(
            $data['occurrences'],
            true
        );

        $data['pastOccurrences'] = $this->filterAndSortOccurrences(
            $data['occurrences'],
            false
        );

        set_query_var('dizzy_event_data', $data);

        $customTemplate = DIZZY_EVENTS_PATH
            . 'includes/Frontend/Views/single-event.php';

        if (file_exists($customTemplate)) {
            return $customTemplate;
        }

        return $template;
    }

    /**
     * Filter occurrences by time group and sort them chronologically.
     *
     * @param array<Occurrence> $occurrences Occurrences to filter.
     *
     * @return array<Occurrence>
     */
    private function filterAndSortOccurrences(
        array $occurrences,
        bool $upcoming
    ): array {
        $filtered = array_values(
            array_filter(
                $occurrences,
                static function (Occurrence $occurrence) use ($upcoming): bool {
                    return $occurrence->isUpcoming() === $upcoming;
                }
            )
        );

        usort(
            $filtered,
            static function (Occurrence $first, Occurrence $second): int {
                $dateComparison = $first->startDateTime <=> $second->startDateTime;

                if ($dateComparison !== 0) {
                    return $dateComparison;
                }

                return $first->id <=> $second->id;
            }
        );

        return $filtered;
    }
}
