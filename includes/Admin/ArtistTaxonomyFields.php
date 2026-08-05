<?php

declare(strict_types=1);

namespace Dizzy\Events\Admin;

use Dizzy\Events\Core\Config;
use WP_Term;

defined('ABSPATH') || exit;

final class ArtistTaxonomyFields
{
    private const CONTACT_FIELD = 'dizzy_artist_contact';
    private const CONTACT_META = '_dizzy_artist_contact';
    private const IMAGE_FIELD = 'dizzy_artist_image_id';
    private const IMAGE_META = '_dizzy_artist_image_id';
    private const NONCE = 'dizzy_artist_fields_nonce';
    private const NONCE_ACTION = 'dizzy_artist_fields_save';

    public function register(): void
    {
        add_action(Config::TAX_ARTIST . '_add_form_fields', [$this, 'renderAddFields']);
        add_action(Config::TAX_ARTIST . '_edit_form_fields', [$this, 'renderEditFields']);
        add_action('created_' . Config::TAX_ARTIST, [$this, 'save']);
        add_action('edited_' . Config::TAX_ARTIST, [$this, 'save']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueMedia']);
        add_action('admin_footer-edit-tags.php', [$this, 'printMediaScript']);
        add_action('admin_footer-term.php', [$this, 'printMediaScript']);
    }

    public function renderAddFields(): void
    {
        wp_nonce_field(self::NONCE_ACTION, self::NONCE);
        ?>
        <div class="form-field term-<?php echo esc_attr(self::CONTACT_FIELD); ?>-wrap">
            <label for="<?php echo esc_attr(self::CONTACT_FIELD); ?>"><?php esc_html_e('Contact', 'dizzy-events-manager'); ?></label>
            <input type="text" id="<?php echo esc_attr(self::CONTACT_FIELD); ?>" name="<?php echo esc_attr(self::CONTACT_FIELD); ?>" value="">
            <p><?php esc_html_e('Enter an artist contact detail or social media address.', 'dizzy-events-manager'); ?></p>
        </div>
        <?php $this->renderAddImageField(); ?>
        <?php
    }

    public function renderEditFields(WP_Term $term): void
    {
        $contact = (string) get_term_meta($term->term_id, self::CONTACT_META, true);
        $imageId = absint(get_term_meta($term->term_id, self::IMAGE_META, true));
        wp_nonce_field(self::NONCE_ACTION, self::NONCE);
        ?>
        <tr class="form-field term-<?php echo esc_attr(self::CONTACT_FIELD); ?>-wrap">
            <th scope="row"><label for="<?php echo esc_attr(self::CONTACT_FIELD); ?>"><?php esc_html_e('Contact', 'dizzy-events-manager'); ?></label></th>
            <td>
                <input type="text" id="<?php echo esc_attr(self::CONTACT_FIELD); ?>" name="<?php echo esc_attr(self::CONTACT_FIELD); ?>" value="<?php echo esc_attr($contact); ?>">
                <p class="description"><?php esc_html_e('Enter an artist contact detail or social media address.', 'dizzy-events-manager'); ?></p>
            </td>
        </tr>
        <tr class="form-field term-<?php echo esc_attr(self::IMAGE_FIELD); ?>-wrap">
            <th scope="row"><label><?php esc_html_e('Artist Photo', 'dizzy-events-manager'); ?></label></th>
            <td><?php $this->renderImageControls($imageId); ?></td>
        </tr>
        <?php
    }

    private function renderAddImageField(): void
    {
        ?>
        <div class="form-field term-<?php echo esc_attr(self::IMAGE_FIELD); ?>-wrap">
            <label><?php esc_html_e('Artist Photo', 'dizzy-events-manager'); ?></label>
            <?php $this->renderImageControls(0); ?>
        </div>
        <?php
    }

    private function renderImageControls(int $imageId): void
    {
        $url = $imageId > 0 ? (string) wp_get_attachment_image_url($imageId, 'medium') : '';
        ?>
        <input type="hidden" class="dizzy-artist-image-id" name="<?php echo esc_attr(self::IMAGE_FIELD); ?>" value="<?php echo esc_attr((string) $imageId); ?>">
        <div class="dizzy-artist-image-preview" style="margin-bottom:10px">
            <?php if ($url !== '') : ?>
                <img src="<?php echo esc_url($url); ?>" alt="" style="display:block;width:160px;height:160px;object-fit:cover;border-radius:4px">
            <?php endif; ?>
        </div>
        <button type="button" class="button dizzy-select-artist-image"><?php esc_html_e('Select image', 'dizzy-events-manager'); ?></button>
        <button type="button" class="button dizzy-remove-artist-image" <?php disabled($imageId, 0); ?>><?php esc_html_e('Remove image', 'dizzy-events-manager'); ?></button>
        <p class="description"><?php esc_html_e('Select a square or portrait image from the Media Library.', 'dizzy-events-manager'); ?></p>
        <?php
    }

    public function save(int $termId): void
    {
        if (
            ! current_user_can('manage_categories')
            || ! isset($_POST[self::NONCE])
            || ! is_string($_POST[self::NONCE])
            || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[self::NONCE])), self::NONCE_ACTION)
        ) {
            return;
        }

        if (isset($_POST[self::CONTACT_FIELD]) && is_string($_POST[self::CONTACT_FIELD])) {
            $contact = sanitize_text_field(wp_unslash($_POST[self::CONTACT_FIELD]));

            if ($contact === '') {
                delete_term_meta($termId, self::CONTACT_META);
            } else {
                update_term_meta($termId, self::CONTACT_META, $contact);
            }
        }

        if (isset($_POST[self::IMAGE_FIELD])) {
            $imageId = absint($_POST[self::IMAGE_FIELD]);

            if ($imageId > 0 && wp_attachment_is_image($imageId)) {
                update_term_meta($termId, self::IMAGE_META, $imageId);
            } else {
                delete_term_meta($termId, self::IMAGE_META);
            }
        }
    }

    public function enqueueMedia(): void
    {
        if (! $this->isArtistScreen()) {
            return;
        }

        wp_enqueue_media();
    }

    public function printMediaScript(): void
    {
        if (! $this->isArtistScreen()) {
            return;
        }
        ?>
        <script>
        (() => {
            let frame;

            const description = document.querySelector('textarea[name="description"]');
            if (description) {
                const wrapper = description.closest('.form-field');
                const label = wrapper?.querySelector('label');
                const help = wrapper?.querySelector('.description, p');
                const role = document.createElement('input');
                role.type = 'text';
                role.value = description.value;
                role.id = 'dizzy_artist_role';
                role.addEventListener('input', () => { description.value = role.value; });
                description.hidden = true;
                description.insertAdjacentElement('beforebegin', role);
                if (label) { label.textContent = 'Role'; label.htmlFor = role.id; }
                if (help) help.textContent = 'Enter an artist role (Group, Band, Guitarist etc.).';
            }

            document.addEventListener('click', (event) => {
                const select = event.target.closest('.dizzy-select-artist-image');
                const remove = event.target.closest('.dizzy-remove-artist-image');

                if (!select && !remove) return;

                event.preventDefault();
                const wrapper = event.target.closest('.form-field');
                const input = wrapper.querySelector('.dizzy-artist-image-id');
                const preview = wrapper.querySelector('.dizzy-artist-image-preview');
                const removeButton = wrapper.querySelector('.dizzy-remove-artist-image');

                if (remove) {
                    input.value = '0';
                    preview.innerHTML = '';
                    removeButton.disabled = true;
                    return;
                }

                frame = wp.media({
                    title: <?php echo wp_json_encode(__('Select Artist Photo', 'dizzy-events-manager')); ?>,
                    button: {text: <?php echo wp_json_encode(__('Use this image', 'dizzy-events-manager')); ?>},
                    library: {type: 'image'},
                    multiple: false
                });

                frame.on('select', () => {
                    const image = frame.state().get('selection').first().toJSON();
                    const source = image.sizes?.medium?.url || image.url;
                    input.value = image.id;
                    preview.innerHTML = '';
                    const element = document.createElement('img');
                    element.src = source;
                    element.alt = '';
                    element.style.cssText = 'display:block;width:160px;height:160px;object-fit:cover;border-radius:4px';
                    preview.appendChild(element);
                    removeButton.disabled = false;
                });

                frame.open();
            });
        })();
        </script>
        <?php
    }

    private function isArtistScreen(): bool
    {
        $screen = get_current_screen();

        return $screen !== null && $screen->taxonomy === Config::TAX_ARTIST;
    }
}
