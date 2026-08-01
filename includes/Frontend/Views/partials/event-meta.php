<?php

declare(strict_types=1);

use Dizzy\Events\Models\EventDetails;

defined('ABSPATH') || exit;

$details = $args['details'] ?? null;

if (! $details instanceof EventDetails) {
    return;
}

$mapsUrl = esc_url(trim($details->mapsUrl));
?>
<div class="dizzy-event-meta">
    <?php if ($details->artist !== null) : ?>
        <p>
            <strong>
                <?php esc_html_e('Artist:', 'dizzy-events-manager'); ?>
            </strong>
            <?php echo esc_html($details->artist); ?>
        </p>
    <?php endif; ?>

    <?php if ($details->genre !== null) : ?>
        <p>
            <strong>
                <?php esc_html_e('Genre:', 'dizzy-events-manager'); ?>
            </strong>
            <?php echo esc_html($details->genre); ?>
        </p>
    <?php endif; ?>

    <?php if ($details->venue !== '') : ?>
        <p>
            <strong>
                <?php esc_html_e('Venue:', 'dizzy-events-manager'); ?>
            </strong>
            <?php echo esc_html($details->venue); ?>
        </p>
    <?php endif; ?>

    <?php if ($details->address !== '') : ?>
        <p>
            <strong>
                <?php esc_html_e('Address:', 'dizzy-events-manager'); ?>
            </strong>

            <?php if ($mapsUrl !== '') : ?>
                <a
                    href="<?php echo $mapsUrl; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above. ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    <?php echo esc_html($details->address); ?>
                </a>
            <?php else : ?>
                <?php echo esc_html($details->address); ?>
            <?php endif; ?>
        </p>
    <?php endif; ?>
</div>
