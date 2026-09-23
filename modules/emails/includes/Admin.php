<?php

declare(strict_types=1);

namespace Dizzy\Emails;

defined('ABSPATH') || exit;

final class Admin
{
    public const SLUG = 'dizzy-emails';
    private string $hook = '';

    public function register(): void
    {
        add_action('admin_menu', [$this, 'menu']);
        add_action('admin_init', [$this, 'registerSettings']);
        add_action('admin_enqueue_scripts', [$this, 'assets']);
    }

    public function menu(): void
    {
        $this->hook = (string) add_submenu_page(
            DIZZY_EVENTS_ADMIN_MENU,
            __('Emails', 'dizzy-events-manager'),
            __('Emails', 'dizzy-events-manager'),
            'manage_options',
            self::SLUG,
            [$this, 'render']
        );
    }

    public function registerSettings(): void
    {
        register_setting('dizzy_email_templates', Settings::OPTION, [
            'type' => 'array',
            'sanitize_callback' => [Settings::class, 'sanitize'],
        ]);
    }

    public function assets(string $hook): void
    {
        if ($hook === $this->hook) {
            wp_enqueue_style('dizzy-emails-admin', DIZZY_EMAILS_URL . 'assets/admin.css', [], DIZZY_EMAILS_VERSION);
        }
    }

    public function render(): void
    {
        if (! current_user_can('manage_options')) {
            wp_die(esc_html__('You are not allowed to manage email templates.', 'dizzy-events-manager'));
        }
        $templates = Settings::templates();
        $definitions = [
            'ticket' => [__('Ticket purchased', 'dizzy-events-manager'), __('Sent after a successful ticket payment.', 'dizzy-events-manager'), 'modules/tickets/includes/Email/Templates/ticket-confirmed.php'],
            'reservation' => [__('Reservation confirmed', 'dizzy-events-manager'), __('Sent when a reservation is confirmed.', 'dizzy-events-manager'), 'modules/reservations/includes/Email/Templates/reservation-confirmed.php'],
            'schedule' => [__('Shift reminder', 'dizzy-events-manager'), __('Sent approximately two hours before a published shift.', 'dizzy-events-manager'), 'modules/emails/includes/Templates/shift-reminder.php'],
        ];
        ?>
        <div class="wrap dizzy-emails-admin">
            <header class="dizzy-editor-header dizzy-emails-header"><div><h1><?php esc_html_e('Email Templates', 'dizzy-events-manager'); ?></h1><p><?php esc_html_e('Enable notifications and set their subject lines.', 'dizzy-events-manager'); ?></p></div></header>
            <?php settings_errors(); ?>
            <form method="post" action="options.php">
                <?php settings_fields('dizzy_email_templates'); ?>
                <?php foreach ($definitions as $key => [$title, $description, $file]) : $item = $templates[$key]; ?>
                    <section class="dizzy-email-panel">
                        <div class="dizzy-email-panel-head">
                            <div><h2><?php echo esc_html($title); ?></h2><p><?php echo esc_html($description); ?></p></div>
                            <label><input type="checkbox" name="<?php echo esc_attr(Settings::OPTION . '[' . $key . '][enabled]'); ?>" value="1" <?php checked($item['enabled'], '1'); ?>> <?php esc_html_e('Enabled', 'dizzy-events-manager'); ?></label>
                        </div>
                        <label class="dizzy-email-field"><?php esc_html_e('Subject', 'dizzy-events-manager'); ?><input type="text" name="<?php echo esc_attr(Settings::OPTION . '[' . $key . '][subject]'); ?>" value="<?php echo esc_attr((string) $item['subject']); ?>" required></label>
                        <label class="dizzy-email-field"><?php esc_html_e('Message', 'dizzy-events-manager'); ?><textarea name="<?php echo esc_attr(Settings::OPTION . '[' . $key . '][message]'); ?>" rows="3"><?php echo esc_textarea((string) $item['message']); ?></textarea></label>
                        <p class="description"><?php esc_html_e('HTML template:', 'dizzy-events-manager'); ?> <code><?php echo esc_html($file); ?></code></p>
                    </section>
                <?php endforeach; ?>
                <?php submit_button(__('Save Email Templates', 'dizzy-events-manager')); ?>
            </form>
        </div>
        <?php
    }
}
