<?php

declare(strict_types=1);

namespace Dizzy\Events\Admin;

use Dizzy\Events\Core\Config;
use WP_Term;

defined('ABSPATH') || exit;

final class ArtistTaxonomyFields
{
    private const FIELD = 'dizzy_artist_contact';
    private const META_KEY = '_dizzy_artist_contact';
    private const NONCE = 'dizzy_artist_contact_nonce';
    private const NONCE_ACTION = 'dizzy_artist_contact_save';

    public function register(): void
    {
        add_action(Config::TAX_ARTIST . '_add_form_fields', [$this, 'renderAddField']);
        add_action(Config::TAX_ARTIST . '_edit_form_fields', [$this, 'renderEditField']);
        add_action('created_' . Config::TAX_ARTIST, [$this, 'save']);
        add_action('edited_' . Config::TAX_ARTIST, [$this, 'save']);
    }

    public function renderAddField(): void
    {
        wp_nonce_field(self::NONCE_ACTION, self::NONCE);
        ?>
        <div class="form-field term-<?php echo esc_attr(self::FIELD); ?>-wrap">
            <label for="<?php echo esc_attr(self::FIELD); ?>"><?php esc_html_e('Contact', 'dizzy-events-manager'); ?></label>
            <input type="text" id="<?php echo esc_attr(self::FIELD); ?>" name="<?php echo esc_attr(self::FIELD); ?>" value="">
            <p><?php esc_html_e('Enter an artist contact detail or social media address.', 'dizzy-events-manager'); ?></p>
        </div>
        <?php
    }

    public function renderEditField(WP_Term $term): void
    {
        $value = (string) get_term_meta($term->term_id, self::META_KEY, true);
        wp_nonce_field(self::NONCE_ACTION, self::NONCE);
        ?>
        <tr class="form-field term-<?php echo esc_attr(self::FIELD); ?>-wrap">
            <th scope="row">
                <label for="<?php echo esc_attr(self::FIELD); ?>"><?php esc_html_e('Contact', 'dizzy-events-manager'); ?></label>
            </th>
            <td>
                <input type="text" id="<?php echo esc_attr(self::FIELD); ?>" name="<?php echo esc_attr(self::FIELD); ?>" value="<?php echo esc_attr($value); ?>">
                <p class="description"><?php esc_html_e('Enter an artist contact detail or social media address.', 'dizzy-events-manager'); ?></p>
            </td>
        </tr>
        <?php
    }

    public function save(int $termId): void
    {
        if (! current_user_can('manage_categories')) {
            return;
        }

        if (
            ! isset($_POST[self::NONCE])
            || ! is_string($_POST[self::NONCE])
            || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[self::NONCE])), self::NONCE_ACTION)
        ) {
            return;
        }

        if (! isset($_POST[self::FIELD]) || ! is_string($_POST[self::FIELD])) {
            return;
        }

        $contact = sanitize_text_field(wp_unslash($_POST[self::FIELD]));

        if ($contact === '') {
            delete_term_meta($termId, self::META_KEY);
            return;
        }

        update_term_meta($termId, self::META_KEY, $contact);
    }
}
