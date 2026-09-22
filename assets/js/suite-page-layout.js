(() => {
    const start = () => {
        const body = document.body;
        if (!body.classList.contains('dizzy-management-admin')) return;
        const params = new URLSearchParams(window.location.search);
        const page = params.get('page') || '';
        const taxonomy = params.get('taxonomy') || '';
        const isCategory = body.classList.contains('dizzy-event-category-admin') || taxonomy === 'dizzy_event_category';
        const wrap = document.querySelector('#wpbody-content > .wrap');
        if (!wrap || body.classList.contains('dizzy-event-editor-screen') || document.querySelector('#dizzy-event-workspace')) return;

        const details = {
            'dizzy-management': ['Events', 'Manage events, dates and publication status.'],
            'dizzy-reservations': ['Reservations', 'Review guests, dates and reservation details.'],
            'dizzy-reservation-tables': ['Table Layout', 'Arrange and edit tables on the floor plan.'],
            'dizzy-reservations-reports': ['Reservation Reports', 'Review reservation activity and totals.'],
            'dizzy-schedule-manager': ['Employee shift planning', 'Plan and review employee shifts.'],
            'dizzy-schedule-reports': ['Schedule Reports', 'Scheduled hours and shifts by employee.'],
            'dizzy-schedule-settings': ['Employee Roles', 'These roles are available in the Position dropdown when creating a shift.'],
            'dizzy-tickets': ['Tickets', 'Manage tickets for upcoming events.'],
            'dizzy-ticket-orders': ['Ticket Orders', 'Review ticket orders and payment status.'],
            'dizzy-ticket-checkin': ['Check-in & Attendance', 'Check tickets and review attendance.'],
            'dizzy-ticket-reports': ['Ticket Reports', 'Review ticket sales and attendance.'],
            'dizzy-ticket-payment-settings': ['Payment Settings', 'Configure ticket payments and terminals.'],
            'dizzy-newsletter': ['Campaigns', 'Manage newsletter campaigns.'],
            'dizzy-newsletter-campaign': ['Add Campaign', 'Create or edit a newsletter campaign.'],
            'dizzy-newsletter-audience': ['Subscribers', 'Manage newsletter subscribers.'],
            'dizzy-newsletter-analytics': ['Analytics', 'Review newsletter performance.'],
            'dizzy-newsletter-settings': ['Newsletter Settings', 'Configure newsletter delivery.'],
            'dizzy-wanotify-settings': ['WhatsApp Settings', 'Connect WAnotify to the WhatsApp Cloud API.'],
            'dizzy-wanotify-templates': ['Message Templates', 'Manage approved WhatsApp templates.'],
            'dizzy-social-media': ['Social Media & Poster Generator', 'Generate posters and manage social publishing.'],
            'dizzy-poster-settings': ['Poster Settings', 'Configure poster layers, typography and layout.'],
            'dizzy-social-accounts': ['Social Accounts', 'Connect and manage social accounts.'],
            'dizzy-social-templates': ['Social Templates', 'Configure messages for social posts.'],
            'dizzy-social-autopost': ['Auto Post Settings', 'Control automatic sharing and review recent activity.'],
            'dizzy-suite-modules': ['Modules', 'Review the status of Dizzy modules.']
        };
        const [title, description] = isCategory
            ? ['Event Categories', 'Organize events into categories.']
            : (details[page] || [document.title, 'Manage this Dizzy page.']);
        const existing = wrap.querySelector(':scope > .dizzy-nl-head, :scope > .dizzy-wa-heading, :scope > .dizzy-social-page-header, :scope > .dizzy-schedule-header, :scope > .dizzy-reports-heading, :scope > .dizzy-schedule-settings-header, :scope > .dizzy-ticket-checkin-heading');
        if (existing) {
            existing.classList.add('dizzy-editor-header');
            if (!existing.querySelector('p')) {
                const p = document.createElement('p');
                p.textContent = description;
                (existing.querySelector('h1')?.parentElement || existing).appendChild(p);
            }
            return;
        }

        const header = document.createElement('header');
        header.className = 'dizzy-editor-header';
        const heading = document.createElement('div');
        const h1 = document.createElement('h1');
        h1.textContent = title;
        const p = document.createElement('p');
        p.textContent = description;
        heading.append(h1, p);
        header.appendChild(heading);
        const anchor = page === 'dizzy-management'
            ? wrap.querySelector('.dizzy-management-cards')
            : wrap.querySelector(':scope > h1, :scope > .wp-heading-inline, :scope > #col-container, :scope > #edittag, :scope > form, :scope > section, :scope > .dizzy-table-toolbar, :scope > .dizzy-ticket-list-panel, :scope > .dizzy-reservation-report-list');
        if (anchor) anchor.before(header);
        else wrap.prepend(header);
    };
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start, {once: true});
    else start();
})();
