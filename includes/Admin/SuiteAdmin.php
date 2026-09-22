<?php

declare(strict_types=1);

namespace Dizzy\Events\Admin;

use Dizzy\Events\Core\Config;

defined('ABSPATH') || exit;

final class SuiteAdmin
{
    /** @var array<string, array{title:string,tabs:array<int,array{label:string,url:string,cap:string}>}> */
    private array $sections = [];

    public function register(): void
    {
        add_action('admin_menu', [$this, 'menu'], 1);
        add_action('admin_menu', [$this, 'sortMenu'], 999);
        add_action('admin_enqueue_scripts', [$this, 'assets']);
        add_action('all_admin_notices', [$this, 'header']);
        add_action('current_screen', [$this, 'setPageTitle']);
        add_filter('admin_body_class', [$this, 'bodyClass']);
        add_filter('parent_file', [$this, 'parentMenu']);
        add_filter('submenu_file', [$this, 'activeSubmenu']);
    }

    public function menu(): void
    {
        add_menu_page(
            __('Dizzy Management', 'dizzy-events-manager'),
            __('Dizzy', 'dizzy-events-manager'),
            'edit_posts',
            DIZZY_EVENTS_ADMIN_MENU,
            [$this, 'eventsPage'],
            'dashicons-admin-generic',
            25
        );
        add_submenu_page(
            DIZZY_EVENTS_ADMIN_MENU,
            __('Events', 'dizzy-events-manager'),
            __('Events', 'dizzy-events-manager'),
            'edit_posts',
            DIZZY_EVENTS_ADMIN_MENU,
            [$this, 'eventsPage']
        );
    }

    public function assets(): void
    {
        if (! $this->isDizzyPage()) {
            return;
        }

        wp_enqueue_style('common');
        wp_add_inline_style('common', $this->css());
    }

    public function sortMenu(): void
    {
        global $submenu;

        if (! isset($submenu[DIZZY_EVENTS_ADMIN_MENU]) || ! is_array($submenu[DIZZY_EVENTS_ADMIN_MENU])) {
            return;
        }

        $order = [
            DIZZY_EVENTS_ADMIN_MENU,
            'dizzy-reservations',
            'dizzy-schedule-manager',
            'dizzy-tickets',
            'dizzy-newsletter',
            'dizzy-wanotify-settings',
            'dizzy-social-media',
        ];
        $positions = array_flip($order);
        usort($submenu[DIZZY_EVENTS_ADMIN_MENU], static function (array $left, array $right) use ($positions): int {
            return ($positions[(string) ($left[2] ?? '')] ?? 999) <=> ($positions[(string) ($right[2] ?? '')] ?? 999);
        });
    }

    public function setPageTitle(): void
    {
        if (! $this->isDizzyPage()) {
            return;
        }

        $section = $this->currentSection();
        if ($section !== null) {
            $GLOBALS['title'] = sprintf(
                /* translators: %s is the active Dizzy module name. */
                __('Dizzy Management — %s', 'dizzy-events-manager'),
                $section['title']
            );
            return;
        }

        $GLOBALS['title'] = $this->isEventsLanding()
            ? __('Dizzy Management — Events', 'dizzy-events-manager')
            : __('Dizzy Suite Modules', 'dizzy-events-manager');
    }

    public function bodyClass(string $classes): string
    {
        return $this->isDizzyPage() ? $classes . ' dizzy-management-admin' : $classes;
    }

    public function parentMenu(string $parentFile): string
    {
        return $this->isDizzyPage() ? DIZZY_EVENTS_ADMIN_MENU : $parentFile;
    }

    public function activeSubmenu(?string $submenuFile): ?string
    {
        if (! $this->isDizzyPage()) {
            return $submenuFile;
        }

        $page = sanitize_key((string) ($_GET['page'] ?? ''));
        $postType = sanitize_key((string) ($_GET['post_type'] ?? ''));
        if ($this->isEventsLanding() || $postType === Config::POST_TYPE_EVENT || get_post_type() === Config::POST_TYPE_EVENT) {
            return DIZZY_EVENTS_ADMIN_MENU;
        }

        foreach ($this->sectionMenuMap() as $pages => $menuSlug) {
            if (in_array($page, explode('|', $pages), true)) {
                return $menuSlug;
            }
        }

        return $submenuFile;
    }

    public function header(): void
    {
        if (! $this->isDizzyPage() || $this->isEventsLanding()) {
            return;
        }

        $section = $this->currentSection();
        if ($section === null) {
            return;
        }

        echo '<div class="dizzy-management-header"><h1>' . esc_html__('Dizzy Management', 'dizzy-events-manager') . '</h1></div>';
        $this->tabs($section['tabs']);
    }

    public function eventsPage(): void
    {
        if (! current_user_can('edit_posts')) {
            wp_die(esc_html__('You do not have permission to view events.', 'dizzy-events-manager'));
        }

        $events = get_posts([
            'post_type' => Config::POST_TYPE_EVENT,
            'post_status' => ['publish', 'future', 'draft', 'pending', 'private'],
            'posts_per_page' => 100,
            'orderby' => 'date',
            'order' => 'DESC',
        ]);

        echo '<div class="dizzy-management-header"><h1>' . esc_html__('Dizzy Management', 'dizzy-events-manager') . '</h1></div>';
        echo '<div class="wrap dizzy-management-page">';
        $this->tabs($this->eventTabs());
        echo '<div class="dizzy-management-cards">';
        if ($events === []) {
            echo '<div class="dizzy-management-card"><h2>' . esc_html__('No events found', 'dizzy-events-manager') . '</h2><p>' . esc_html__('Create the first event to get started.', 'dizzy-events-manager') . '</p></div>';
        }
        foreach ($events as $event) {
            $status = get_post_status_object((string) $event->post_status);
            echo '<details class="dizzy-management-card"><summary>' . esc_html(get_the_title($event)) . '</summary>';
            echo '<div class="dizzy-management-card-body"><span><strong>' . esc_html__('Status:', 'dizzy-events-manager') . '</strong> ' . esc_html($status?->label ?? (string) $event->post_status) . '</span><a class="button button-primary" href="' . esc_url(get_edit_post_link((int) $event->ID, '')) . '">' . esc_html__('Edit Event', 'dizzy-events-manager') . '</a></div></details>';
        }
        echo '</div>';
        if (current_user_can('manage_options')) {
            echo '<p class="dizzy-suite-status-link"><a href="' . esc_url(admin_url('admin.php?page=dizzy-suite-modules')) . '">' . esc_html__('View module status', 'dizzy-events-manager') . '</a></p>';
        }
        echo '</div>';
    }

    /** @param array<int,array{label:string,url:string,cap:string}> $tabs */
    private function tabs(array $tabs): void
    {
        $current = $this->currentUrlKey();
        echo '<nav class="dizzy-management-tabs" aria-label="' . esc_attr__('Dizzy navigation', 'dizzy-events-manager') . '">';
        foreach ($tabs as $tab) {
            if (! current_user_can($tab['cap'])) {
                continue;
            }
            $active = $current === $this->urlKey($tab['url']);
            echo '<a class="' . ($active ? 'is-active' : '') . '" href="' . esc_url($tab['url']) . '">' . esc_html($tab['label']) . '</a>';
        }
        echo '</nav>';
    }

    /** @return array<int,array{label:string,url:string,cap:string}> */
    private function eventTabs(): array
    {
        return [
            ['label' => __('All Events', 'dizzy-events-manager'), 'url' => admin_url('admin.php?page=' . DIZZY_EVENTS_ADMIN_MENU), 'cap' => 'edit_posts'],
            ['label' => __('Add Event', 'dizzy-events-manager'), 'url' => admin_url('post-new.php?post_type=' . Config::POST_TYPE_EVENT), 'cap' => 'edit_posts'],
            ['label' => __('Event Categories', 'dizzy-events-manager'), 'url' => admin_url('edit-tags.php?taxonomy=' . Config::TAX_CATEGORY . '&post_type=' . Config::POST_TYPE_EVENT), 'cap' => 'manage_categories'],
        ];
    }

    /** @return array{title:string,tabs:array<int,array{label:string,url:string,cap:string}>}|null */
    private function currentSection(): ?array
    {
        $page = sanitize_key((string) ($_GET['page'] ?? ''));
        $postType = sanitize_key((string) ($_GET['post_type'] ?? ''));
        if ($postType === Config::POST_TYPE_EVENT || get_post_type() === Config::POST_TYPE_EVENT) {
            return ['title' => 'Events', 'tabs' => $this->eventTabs()];
        }

        foreach ($this->sections() as $pages => $section) {
            if (in_array($page, explode('|', $pages), true)) {
                return $section;
            }
        }
        return null;
    }

    /** @return array<string,array{title:string,tabs:array<int,array{label:string,url:string,cap:string}>}> */
    private function sections(): array
    {
        if ($this->sections !== []) {
            return $this->sections;
        }
        $admin = static fn (string $page): string => admin_url('admin.php?page=' . $page);
        $tab = static fn (string $label, string $page, string $cap = 'manage_options'): array => ['label' => $label, 'url' => $admin($page), 'cap' => $cap];
        return $this->sections = [
            'dizzy-reservations|dizzy-reservation-tables|dizzy-reservations-reports' => ['title' => 'Reservations', 'tabs' => [$tab('Reservations', 'dizzy-reservations', 'dizzy_manage_reservations'), $tab('Tables', 'dizzy-reservation-tables'), $tab('Reports', 'dizzy-reservations-reports')]],
            'dizzy-schedule-manager|dizzy-schedule-reports|dizzy-schedule-settings' => ['title' => 'Schedule', 'tabs' => [$tab('Schedule', 'dizzy-schedule-manager', 'dizzy_view_schedule'), $tab('Reports', 'dizzy-schedule-reports'), $tab('Settings', 'dizzy-schedule-settings')]],
            'dizzy-tickets|dizzy-ticket-orders|dizzy-ticket-checkin|dizzy-ticket-reports|dizzy-ticket-payment-settings' => ['title' => 'Tickets', 'tabs' => [$tab('Tickets', 'dizzy-tickets', 'dizzy_manage_tickets'), $tab('Orders', 'dizzy-ticket-orders', 'dizzy_manage_tickets'), $tab('Check-in', 'dizzy-ticket-checkin', 'dizzy_manage_tickets'), $tab('Reports', 'dizzy-ticket-reports'), $tab('Payment Settings', 'dizzy-ticket-payment-settings')]],
            'dizzy-newsletter|dizzy-newsletter-campaign|dizzy-newsletter-audience|dizzy-newsletter-analytics|dizzy-newsletter-settings' => ['title' => 'Newsletter', 'tabs' => [$tab('Campaigns', 'dizzy-newsletter'), $tab('Add Campaign', 'dizzy-newsletter-campaign'), $tab('Subscribers', 'dizzy-newsletter-audience'), $tab('Analytics', 'dizzy-newsletter-analytics'), $tab('Settings', 'dizzy-newsletter-settings')]],
            'dizzy-wanotify-settings|dizzy-wanotify-templates' => ['title' => 'WA Notify', 'tabs' => [$tab('Settings', 'dizzy-wanotify-settings'), $tab('Message Templates', 'dizzy-wanotify-templates')]],
            'dizzy-social-media|dizzy-poster-settings|dizzy-social-accounts|dizzy-social-templates|dizzy-social-autopost' => ['title' => 'Social Media', 'tabs' => [$tab('Poster Generator', 'dizzy-social-media', 'edit_posts'), $tab('Poster Settings', 'dizzy-poster-settings'), $tab('Accounts', 'dizzy-social-accounts'), $tab('Templates', 'dizzy-social-templates'), $tab('Auto Post', 'dizzy-social-autopost')]],
        ];
    }

    /** @return array<string,string> */
    private function sectionMenuMap(): array
    {
        return [
            'dizzy-reservations|dizzy-reservation-tables|dizzy-reservations-reports' => 'dizzy-reservations',
            'dizzy-schedule-manager|dizzy-schedule-reports|dizzy-schedule-settings' => 'dizzy-schedule-manager',
            'dizzy-tickets|dizzy-ticket-orders|dizzy-ticket-checkin|dizzy-ticket-reports|dizzy-ticket-payment-settings' => 'dizzy-tickets',
            'dizzy-newsletter|dizzy-newsletter-campaign|dizzy-newsletter-audience|dizzy-newsletter-analytics|dizzy-newsletter-settings' => 'dizzy-newsletter',
            'dizzy-wanotify-settings|dizzy-wanotify-templates' => 'dizzy-wanotify-settings',
            'dizzy-social-media|dizzy-poster-settings|dizzy-social-accounts|dizzy-social-templates|dizzy-social-autopost' => 'dizzy-social-media',
        ];
    }

    private function isDizzyPage(): bool
    {
        return $this->currentSection() !== null || $this->isEventsLanding() || sanitize_key((string) ($_GET['page'] ?? '')) === 'dizzy-suite-modules';
    }

    private function isEventsLanding(): bool
    {
        return sanitize_key((string) ($_GET['page'] ?? '')) === DIZZY_EVENTS_ADMIN_MENU;
    }

    private function currentUrlKey(): string
    {
        global $pagenow;
        $query = [];
        foreach (['page', 'post_type', 'taxonomy'] as $key) {
            if (isset($_GET[$key])) {
                $query[$key] = sanitize_key((string) $_GET[$key]);
            }
        }
        return (string) $pagenow . '?' . http_build_query($query);
    }

    private function urlKey(string $url): string
    {
        $parts = wp_parse_url($url);
        parse_str((string) ($parts['query'] ?? ''), $query);
        $filtered = [];
        foreach (['page', 'post_type', 'taxonomy'] as $key) {
            if (isset($query[$key])) {
                $filtered[$key] = sanitize_key((string) $query[$key]);
            }
        }
        return basename((string) ($parts['path'] ?? 'admin.php')) . '?' . http_build_query($filtered);
    }

    private function css(): string
    {
        return '.dizzy-management-admin #wpcontent{background:#f4f5f7}.dizzy-management-admin #wpbody-content>.wrap{width:auto;max-width:1240px;margin-left:auto;margin-right:auto}.dizzy-management-header{margin:0 -20px 28px;padding:26px 20px;background:#fff;border-bottom:1px solid #e2e5e9}.dizzy-management-header h1{max-width:1240px;margin:0 auto;font-size:17px;font-weight:500}.dizzy-management-tabs{display:flex;flex-wrap:wrap;gap:30px;width:auto;max-width:1240px;margin:0 auto 22px;padding:0 4px;border-bottom:1px solid #d9dde3}.dizzy-management-tabs a{padding:12px 0 11px;color:#101828;text-decoration:none;font-weight:500;border-bottom:2px solid transparent}.dizzy-management-tabs a:hover,.dizzy-management-tabs a.is-active{color:#135eeb;border-bottom-color:#135eeb}.dizzy-management-page{max-width:1240px}.dizzy-management-cards{display:grid;gap:20px}.dizzy-management-card{display:block;background:#fff;border:1px solid #dce1e7;box-shadow:0 1px 3px rgba(16,24,40,.08)}.dizzy-management-card summary{position:relative;padding:20px 54px 20px 20px;font-size:15px;font-weight:600;cursor:pointer;list-style:none}.dizzy-management-card summary::-webkit-details-marker{display:none}.dizzy-management-card summary:after{content:"";position:absolute;top:50%;right:24px;width:7px;height:7px;border-right:2px solid #344054;border-bottom:2px solid #344054;transform:translateY(-65%) rotate(45deg);transition:transform .18s ease}.dizzy-management-card[open] summary{border-bottom:1px solid #e6e9ed}.dizzy-management-card[open] summary:after{transform:translateY(-30%) rotate(225deg)}.dizzy-management-card-body{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:20px;color:#475467}.dizzy-suite-status-link{margin-top:20px}@media(max-width:782px){.dizzy-management-header{margin-left:-10px;margin-right:-10px}.dizzy-management-tabs{gap:18px}.dizzy-management-card-body{align-items:flex-start;flex-direction:column}}';
    }
}
