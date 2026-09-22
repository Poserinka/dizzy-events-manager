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
        add_action('admin_head', [$this, 'forceMenuState'], 999);
        add_action('admin_footer', [$this, 'forceMenuStateInBrowser'], 999);
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

        return $this->activeMenuSlug() ?? $submenuFile;
    }

    public function forceMenuState(): void
    {
        if (! $this->isDizzyPage()) {
            return;
        }

        $GLOBALS['parent_file'] = DIZZY_EVENTS_ADMIN_MENU;
        $GLOBALS['submenu_file'] = $this->activeMenuSlug();
    }

    public function forceMenuStateInBrowser(): void
    {
        if (! $this->isDizzyPage()) {
            return;
        }

        $activeSlug = $this->activeMenuSlug();
        if ($activeSlug === null) {
            return;
        }
        ?>
        <script>
        (() => {
            const menu = document.getElementById('toplevel_page_dizzy-management');
            if (!menu) return;
            menu.classList.remove('wp-not-current-submenu');
            menu.classList.add('wp-has-current-submenu', 'wp-menu-open');
            const topLink = menu.querySelector(':scope > a');
            if (topLink) {
                topLink.classList.remove('wp-not-current-submenu');
                topLink.classList.add('wp-has-current-submenu', 'wp-menu-open');
                topLink.setAttribute('aria-expanded', 'true');
            }
            const activeSlug = <?php echo wp_json_encode($activeSlug); ?>;
            menu.querySelectorAll('.wp-submenu li').forEach(item => item.classList.remove('current'));
            menu.querySelectorAll('.wp-submenu a').forEach(link => {
                link.classList.remove('current');
                link.removeAttribute('aria-current');
                let page = '';
                try { page = new URL(link.href, window.location.href).searchParams.get('page') || ''; } catch (error) {}
                if (page !== activeSlug) return;
                link.classList.add('current');
                link.setAttribute('aria-current', 'page');
                link.closest('li')?.classList.add('current');
            });
        })();
        </script>
        <?php
    }

    private function activeMenuSlug(): ?string
    {

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

        return null;
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

        $this->renderHeader($section['title']);
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

        $this->renderHeader(__('Events', 'dizzy-events-manager'));
        echo '<div class="wrap dizzy-management-page">';
        $this->tabs($this->eventTabs());
        echo '<div class="dizzy-management-cards">';
        if ($events === []) {
            echo '<div class="dizzy-management-card"><h2>' . esc_html__('No events found', 'dizzy-events-manager') . '</h2><p>' . esc_html__('Create the first event to get started.', 'dizzy-events-manager') . '</p></div>';
        }
        foreach ($events as $event) {
            $status = get_post_status_object((string) $event->post_status);
            $eventDate = $eventDates[(int) $event->ID]['date'] ?? '';
            $timestamp = $eventDate !== '' ? strtotime($eventDate) : false;
            $formattedDate = $timestamp !== false
                ? wp_date('d F Y', $timestamp, wp_timezone())
                : __('No date', 'dizzy-events-manager');
            $formattedTime = $timestamp !== false ? wp_date('H:i', $timestamp, wp_timezone()) : '';
            echo '<article class="dizzy-management-card">';
            echo '<strong class="dizzy-management-card-title">' . esc_html(get_the_title($event)) . '</strong>';
            echo '<div class="dizzy-management-card-meta"><span>' . esc_html($formattedDate) . '</span>';
            if ($formattedTime !== '') {
                echo '<span aria-hidden="true">•</span><time>' . esc_html($formattedTime) . '</time>';
            }
            echo '<span aria-hidden="true">•</span><span><strong>' . esc_html__('Status:', 'dizzy-events-manager') . '</strong> ' . esc_html($status?->label ?? (string) $event->post_status) . '</span></div>';
            echo '<a class="button button-primary" href="' . esc_url(get_edit_post_link((int) $event->ID, '')) . '">' . esc_html__('Edit Event', 'dizzy-events-manager') . '</a></article>';
        }
        echo '</div>';
        if (current_user_can('manage_options')) {
            echo '<p class="dizzy-suite-status-link"><a href="' . esc_url(admin_url('admin.php?page=dizzy-suite-modules')) . '">' . esc_html__('View module status', 'dizzy-events-manager') . '</a></p>';
        }
        echo '</div>';
    }

    private function renderHeader(string $pageName): void
    {
        $presentation = $this->sectionPresentation($pageName);
        echo '<header class="dizzy-management-header"><div class="dizzy-management-header-inner">';
        echo '<img src="' . esc_url(DIZZY_EVENTS_URL . 'assets/images/jazzcafe-dizzy-logo-black.png') . '" width="270" height="38" alt="' . esc_attr__('Jazzcafé Dizzy', 'dizzy-events-manager') . '">';
        echo '<h1>' . esc_html__('Dizzy Management', 'dizzy-events-manager') . '</h1>';
        echo '</div></header>';
        echo '<section class="dizzy-management-hero"><div><h2>' . esc_html($pageName) . '</h2><p>' . esc_html($presentation['description']) . '</p></div>';
        if ($presentation['action_url'] !== '' && $presentation['action_label'] !== '') {
            echo '<a class="button button-primary" href="' . esc_url($presentation['action_url']) . '">' . esc_html($presentation['action_label']) . '</a>';
        }
        echo '</section>';
    }

    /** @return array{description:string,action_label:string,action_url:string} */
    private function sectionPresentation(string $pageName): array
    {
        $presentations = [
            'Events' => [
                'description' => __('Manage events, dates and publication status.', 'dizzy-events-manager'),
                'action_label' => __('Add Event', 'dizzy-events-manager'),
                'action_url' => admin_url('post-new.php?post_type=' . Config::POST_TYPE_EVENT),
            ],
            'Reservations' => [
                'description' => __('Manage guests, tables and reservation reports.', 'dizzy-events-manager'),
                'action_label' => '',
                'action_url' => '',
            ],
            'Schedule' => [
                'description' => __('Manage employee schedules, reports and positions.', 'dizzy-events-manager'),
                'action_label' => '',
                'action_url' => '',
            ],
            'Tickets' => [
                'description' => __('Manage tickets, orders, check-in and payment settings.', 'dizzy-events-manager'),
                'action_label' => '',
                'action_url' => '',
            ],
            'Newsletter' => [
                'description' => __('Create campaigns and manage subscribers.', 'dizzy-events-manager'),
                'action_label' => __('Add Campaign', 'dizzy-events-manager'),
                'action_url' => admin_url('admin.php?page=dizzy-newsletter-campaign'),
            ],
            'WA Notify' => [
                'description' => __('Manage WhatsApp connections and message templates.', 'dizzy-events-manager'),
                'action_label' => '',
                'action_url' => '',
            ],
            'Social Media' => [
                'description' => __('Create event posters and manage social publishing.', 'dizzy-events-manager'),
                'action_label' => '',
                'action_url' => '',
            ],
        ];
        return $presentations[$pageName] ?? [
            'description' => __('Manage Dizzy settings and content.', 'dizzy-events-manager'),
            'action_label' => '',
            'action_url' => '',
        ];
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
        return '.dizzy-management-admin #wpcontent{background:#f4f5f7}.dizzy-management-admin #wpbody-content>.wrap{width:auto;max-width:1240px;margin-left:auto;margin-right:auto}.dizzy-management-admin #wpbody-content>.wrap>h1:first-child{display:none}.dizzy-management-header{margin:0 -20px 26px;padding:24px 20px;background:#fff;border-bottom:1px solid #e2e5e9}.dizzy-management-header-inner{display:flex;align-items:center;gap:26px;max-width:1240px;margin:0 auto}.dizzy-management-header img{display:block;width:270px;max-width:35%;height:auto}.dizzy-management-header h1{margin:0;font-size:17px;font-weight:500;line-height:1.3}.dizzy-management-hero{display:flex;align-items:center;justify-content:space-between;gap:24px;box-sizing:border-box;width:auto;max-width:1240px;min-height:108px;margin:0 auto;padding:24px;background:#171b23;color:#fff}.dizzy-management-hero h2{margin:0 0 8px;color:#fff;font-size:24px;font-weight:500;line-height:1.2}.dizzy-management-hero p{margin:0;color:#c7ced9}.dizzy-management-hero>.button{display:inline-flex;align-items:center;justify-content:center;min-height:40px;padding:0 18px;white-space:nowrap}.dizzy-management-tabs{display:flex;flex-wrap:wrap;gap:30px;box-sizing:border-box;width:auto;max-width:1240px;margin:0 auto 28px;padding:0 20px;background:#fff;border-bottom:1px solid #d9dde3}.dizzy-management-tabs a{padding:17px 0 14px;color:#667085;text-decoration:none;text-transform:uppercase;font-size:12px;font-weight:600;border-bottom:3px solid transparent}.dizzy-management-tabs a:hover,.dizzy-management-tabs a.is-active{color:#135eeb;border-bottom-color:#2271b1}.dizzy-management-page{max-width:1240px;margin-top:0!important}.dizzy-management-cards{display:grid;gap:20px}.dizzy-management-card{display:grid;grid-template-columns:minmax(180px,1fr) auto auto;align-items:center;gap:24px;min-height:58px;padding:10px 12px 10px 20px}.dizzy-management-card-title{font-size:15px}.dizzy-management-card-meta{display:flex;align-items:center;justify-content:flex-end;gap:7px;color:#344054;white-space:nowrap}.dizzy-management-card>.button{display:inline-flex;align-items:center;justify-content:center;min-height:40px;padding:0 18px}.dizzy-management-card,.dizzy-reservation-card,.dizzy-reservation-empty,.dizzy-calendar-surface,.dizzy-reservation-report-list,.dizzy-report-calendar-surface,.dizzy-reservation-report-cards>div,.dizzy-ticket-list-panel,.dizzy-list-calendar-surface,.dizzy-ticket-report-list,.dizzy-ticket-calendar-surface,.dizzy-ticket-report-cards>div,.dizzy-nl-card,.dizzy-nl-stats>div,.dizzy-wa-panel,.dizzy-schedule-settings-panel,.dizzy-social-page .dizzy-card,.dizzy-table-editor,.dizzy-background-controls{background-color:#fff!important;border:1px solid #E8E8EB!important;box-shadow:0 2px 5px #0000000d!important}.dizzy-suite-status-link{margin-top:20px}@media(max-width:782px){.dizzy-management-header{margin-left:-10px;margin-right:-10px}.dizzy-management-header-inner{align-items:flex-start;flex-direction:column;gap:12px}.dizzy-management-header img{width:240px;max-width:80%}.dizzy-management-hero{align-items:flex-start;flex-direction:column;margin-left:0;margin-right:0}.dizzy-management-tabs{gap:18px}.dizzy-management-card{grid-template-columns:1fr;align-items:start;gap:10px;padding:16px}.dizzy-management-card-meta{flex-wrap:wrap;justify-content:flex-start;white-space:normal}.dizzy-management-card>.button{width:max-content}}';
    }
}
