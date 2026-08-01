<?php

declare(strict_types=1);

use Dizzy\Events\Models\Occurrence;

defined('ABSPATH') || exit;

$upcomingOccurrences = $args['upcomingOccurrences'] ?? [];
$pastOccurrences     = $args['pastOccurrences'] ?? [];
$dateTimeFormat      = sprintf(
    '%s - %s',
    (string) get_option('date_format'),
    (string) get_option('time_format')
);

/**
 * Render occurrence list items.
 *
 * @param array<Occurrence> $occurrences Event occurrences.
 */
$renderOccurrences = static function (array $occurrences) use ($dateTimeFormat): void {
    foreach ($occurrences as $occurrence) {
        if (! $occurrence instanceof Occurrence) {
            continue;
        }
        ?>
        <li>
            <?php
            echo esc_html(
                wp_date(
                    $dateTimeFormat,
                    $occurrence->startDateTime->getTimestamp(),
                    $occurrence->startDateTime->getTimezone()
                )
            );
            ?>
        </li>
        <?php
    }
};
?>

<?php if ($upcomingOccurrences !== []) : ?>
    <section class="dizzy-event-dates">
        <h2>
            <?php esc_html_e('Upcoming Dates', 'dizzy-events-manager'); ?>
        </h2>

        <ul>
            <?php $renderOccurrences($upcomingOccurrences); ?>
        </ul>
    </section>
<?php endif; ?>

<?php if ($pastOccurrences !== []) : ?>
    <section class="dizzy-event-past-dates">
        <h2>
            <?php esc_html_e('Past Dates', 'dizzy-events-manager'); ?>
        </h2>

        <ul>
            <?php $renderOccurrences($pastOccurrences); ?>
        </ul>
    </section>
<?php endif; ?>
