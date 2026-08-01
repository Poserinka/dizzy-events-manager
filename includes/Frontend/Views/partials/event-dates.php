<?php

declare(strict_types=1);

use Dizzy\Events\Models\Occurrence;

defined('ABSPATH') || exit;

$upcomingOccurrences = $args['upcomingOccurrences'] ?? [];
$pastOccurrences     = $args['pastOccurrences'] ?? [];
$dateFormat          = get_option('date_format');
$timeFormat          = get_option('time_format');
$dateFormat          = is_string($dateFormat) && $dateFormat !== ''
    ? $dateFormat
    : 'j F Y';
$timeFormat          = is_string($timeFormat) && $timeFormat !== ''
    ? $timeFormat
    : 'H:i';
$dateTimeFormat      = sprintf('%s - %s', $dateFormat, $timeFormat);

/**
 * Format an occurrence date range.
 */
$formatOccurrence = static function (
    Occurrence $occurrence
) use ($dateFormat, $timeFormat, $dateTimeFormat): string {
    $start = $occurrence->startDateTime;
    $end   = $occurrence->endDateTime;

    $startLabel = wp_date(
        $dateTimeFormat,
        $start->getTimestamp(),
        $start->getTimezone()
    );

    if ($end === null) {
        return $startLabel;
    }

    $sameDate = $start->format('Y-m-d') === $end->format('Y-m-d');

    if ($sameDate) {
        return sprintf(
            /* translators: 1: occurrence start date and time, 2: end time. */
            __('%1$s – %2$s', 'dizzy-events-manager'),
            $startLabel,
            wp_date(
                $timeFormat,
                $end->getTimestamp(),
                $end->getTimezone()
            )
        );
    }

    return sprintf(
        /* translators: 1: occurrence start date and time, 2: end date and time. */
        __('%1$s – %2$s', 'dizzy-events-manager'),
        $startLabel,
        wp_date(
            $dateTimeFormat,
            $end->getTimestamp(),
            $end->getTimezone()
        )
    );
};

/**
 * Render occurrence list items.
 *
 * @param array<Occurrence> $occurrences Event occurrences.
 */
$renderOccurrences = static function (
    array $occurrences
) use ($formatOccurrence): void {
    foreach ($occurrences as $occurrence) {
        if (! $occurrence instanceof Occurrence) {
            continue;
        }
        ?>
        <li>
            <?php echo esc_html($formatOccurrence($occurrence)); ?>
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
<?php endif;
