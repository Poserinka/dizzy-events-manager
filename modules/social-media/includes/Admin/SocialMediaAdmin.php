<?php

declare(strict_types=1);

namespace Dizzy\SocialMedia\Admin;

use Dizzy\SocialMedia\Core\Config;
use Dizzy\SocialMedia\Poster\Repositories\PosterRepository;

defined('ABSPATH') || exit;

final class SocialMediaAdmin
{
    public function __construct(private PosterRepository $posters) {}

    public function register(): void
    {
        add_action('admin_menu', [$this, 'menu']);
    }

    public function menu(): void
    {
        add_submenu_page(
            DIZZY_EVENTS_ADMIN_MENU,
            __('Social Media Manager', 'dizzy-social-media-manager'),
            __('Social Media', 'dizzy-social-media-manager'),
            'edit_posts',
            'dizzy-social-media',
            [$this, 'render']
        );
    }

    public function render(): void
    {
        if (! current_user_can('edit_posts')) return;
        $events = get_posts([
            'post_type' => Config::POST_TYPE_EVENT,
            'post_status' => ['publish', 'future', 'draft', 'pending', 'private'],
            'posts_per_page' => 100,
            'orderby' => 'ID',
            'order' => 'ASC',
        ]);

        $eventDates = $this->eventDates($events);
        usort($events, static function (object $left, object $right) use ($eventDates): int {
            $leftDate = $eventDates[(int) $left->ID] ?? null;
            $rightDate = $eventDates[(int) $right->ID] ?? null;
            $leftRank = $leftDate === null ? 2 : ($leftDate['upcoming'] ? 0 : 1);
            $rightRank = $rightDate === null ? 2 : ($rightDate['upcoming'] ? 0 : 1);
            if ($leftRank !== $rightRank) {
                return $leftRank <=> $rightRank;
            }
            if ($leftDate === null || $rightDate === null) {
                return (int) $right->ID <=> (int) $left->ID;
            }
            return $leftRank === 0
                ? strcmp($leftDate['date'], $rightDate['date'])
                : strcmp($rightDate['date'], $leftDate['date']);
        });

        echo '<div class="wrap dizzy-social-page"><div class="dizzy-social-page-header"><h1>' . esc_html__('Social Media & Poster Generator', 'dizzy-social-media-manager') . '</h1>';
        echo '<p>' . esc_html__('Choose an event to create posters and social media exports.', 'dizzy-social-media-manager') . '</p></div>';
        echo '<div class="dizzy-management-cards dizzy-social-generator-cards">';
        if ($events === []) {
            echo '<div class="dizzy-management-card"><strong>' . esc_html__('No events found', 'dizzy-social-media-manager') . '</strong></div>';
        }
        foreach ($events as $event) {
            $poster = $this->posters->findByEvent((int) $event->ID);
            $eventDate = $eventDates[(int) $event->ID]['date'] ?? '';
            $timestamp = $eventDate !== '' ? strtotime($eventDate) : false;
            $formattedDate = $timestamp !== false
                ? wp_date('d F Y', $timestamp, wp_timezone())
                : __('No date', 'dizzy-social-media-manager');
            $formattedTime = $timestamp !== false ? wp_date('H:i', $timestamp, wp_timezone()) : '';
            $status = get_post_status_object((string) $event->post_status);
            echo '<article class="dizzy-management-card dizzy-social-generator-card"><div class="dizzy-social-generator-title">';
            if ($poster && $poster->imageUrl !== '') {
                echo '<img src="' . esc_url($poster->imageUrl) . '" width="48" height="48" alt="">';
            }
            echo '<strong class="dizzy-management-card-title">' . esc_html(get_the_title($event)) . '</strong></div>';
            echo '<div class="dizzy-management-card-meta"><span>' . esc_html($formattedDate) . '</span>';
            if ($formattedTime !== '') {
                echo '<span aria-hidden="true">•</span><time>' . esc_html($formattedTime) . '</time>';
            }
            echo '<span aria-hidden="true">•</span><span><strong>' . esc_html__('Status:', 'dizzy-social-media-manager') . '</strong> ' . esc_html($status?->label ?? (string) $event->post_status) . '</span>';
            echo '<span aria-hidden="true">•</span><span>' . esc_html($poster && $poster->imageUrl !== '' ? __('Poster ready', 'dizzy-social-media-manager') : __('Poster not generated', 'dizzy-social-media-manager')) . '</span></div>';
            echo '<a class="button button-primary" href="' . esc_url(get_edit_post_link((int) $event->ID, '')) . '#dizzy_event_poster_generator">' . esc_html__('Open Generator', 'dizzy-social-media-manager') . '</a></article>';
        }
        echo '</div></div>';
    }

    /**
     * @param array<int,object> $events
     * @return array<int,array{date:string,upcoming:bool}>
     */
    private function eventDates(array $events): array
    {
        global $wpdb;

        $ids = array_values(array_filter(array_map(static fn (object $event): int => absint($event->ID ?? 0), $events)));
        if ($ids === []) {
            return [];
        }

        $idList = implode(',', $ids);
        $now = current_time('mysql');
        $sql = $wpdb->prepare(
            "SELECT event_id,
                MIN(CASE WHEN start_datetime >= %s THEN start_datetime END) AS upcoming_date,
                MAX(CASE WHEN start_datetime < %s THEN start_datetime END) AS past_date
            FROM {$wpdb->prefix}dizzy_event_occurrences
            WHERE event_id IN ({$idList})
            GROUP BY event_id",
            $now,
            $now
        );
        $rows = $wpdb->get_results($sql, ARRAY_A);
        $dates = [];
        foreach ((array) $rows as $row) {
            $upcoming = (string) ($row['upcoming_date'] ?? '');
            $past = (string) ($row['past_date'] ?? '');
            $date = $upcoming !== '' ? $upcoming : $past;
            if ($date !== '') {
                $dates[(int) $row['event_id']] = ['date' => $date, 'upcoming' => $upcoming !== ''];
            }
        }
        return $dates;
    }
}
