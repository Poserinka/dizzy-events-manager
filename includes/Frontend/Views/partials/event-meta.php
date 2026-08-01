<?php

declare(strict_types=1);

use Dizzy\Events\Models\EventDetails;

defined('ABSPATH') || exit;

$details = $args['details'] ?? null;

if (! $details instanceof EventDetails) {
    return;
}

$artist  = $details->artist !== null ? trim($details->artist) : '';
$genre   = $details->genre !== null ? trim($details->genre) : '';
$venue   = trim($details->venue);
$address = trim($details->address);
$mapsUrl = esc_url(trim($details->mapsUrl));

if (
    $artist === ''
    && $genre === ''
    && $venue === ''
    && $address === ''
) {
    return;
}
?>
<div class="dizzy-event-meta">
    <?php if ($artist !== '') : ?>
        <p>
            <strong>
                <?php esc_html_e('Artist:', 'dizzy-events-manager'); ?>
            </strong>
            <?php echo esc_html($artist); ?>
        </p>
    <?php endif; ?>

    <?php if ($genre !== '') : ?>
        <p>
            <strong>
                <?php esc_html_e('Genre:', 'dizzy-events-manager'); ?>
            </strong>
            <?php echo esc_html($genre); ?>
        </p>
    <?php endif; ?>

    <?php if ($venue !== '') : ?>
        <p>
            <strong>
                <?php esc_html_e('Venue:', 'dizzy-events-manager'); ?>
            </strong>
            <?php echo esc_html($venue); ?>
        </p>
    <?php endif; ?>

    <?php if ($address !== '') : ?>
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
                    <?php echo esc_html($address); ?>
                </a>
            <?php else : ?>
                <?php echo esc_html($address); ?>
            <?php endif; ?>
        </p>
    <?php endif; ?>
</div>
