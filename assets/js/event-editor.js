(() => {
    'use strict';

    const ready = () => {
        const poststuff = document.querySelector('#poststuff');
        const original = document.querySelector('#post-body');

        if (!poststuff || !original || document.querySelector('#dizzy-event-workspace')) {
            return;
        }

        const workspace = document.createElement('div');
        workspace.id = 'dizzy-event-workspace';
        workspace.innerHTML = `
            <header class="dizzy-editor-header">
                <div><h1>${document.querySelector('#title')?.value ? 'Edit Event' : 'Create Event'}</h1><p>Manage the complete event from one page.</p></div>
                <div class="dizzy-editor-header-actions"><button type="button" class="button dizzy-editor-preview">Preview</button><button type="button" class="button button-primary dizzy-editor-save">Save Event</button></div>
            </header>
            <div class="dizzy-editor-columns"><main class="dizzy-editor-main"></main><aside class="dizzy-editor-side"></aside></div>`;

        poststuff.insertBefore(workspace, original);
        const main = workspace.querySelector('.dizzy-editor-main');
        const side = workspace.querySelector('.dizzy-editor-side');
        const find = (selector) => document.querySelector(selector);
        const elements = (selectors) => selectors.map(find).filter(Boolean);
        const section = (title, description, nodes, target = main) => {
            if (!nodes.length) return;
            const wrapper = document.createElement('section');
            wrapper.className = 'dizzy-editor-section';
            wrapper.innerHTML = `<div class="dizzy-editor-section-head"><h2>${title}</h2>${description ? `<p>${description}</p>` : ''}</div><div class="dizzy-editor-section-body"></div>`;
            const body = wrapper.querySelector('.dizzy-editor-section-body');
            nodes.forEach((node) => body.appendChild(node));
            target.appendChild(wrapper);
        };

        const mostUsedTab = find('#dizzy_event_category-tabs a[href="#dizzy_event_category-pop"]')?.closest('li');
        mostUsedTab?.remove();
        find('#dizzy_event_category-pop')?.remove();

        const artistBox = find('#tagsdiv-dizzy_event_artist');
        const venueBox = find('#tagsdiv-dizzy_event_venue');
        [artistBox, venueBox].filter(Boolean).forEach((box) => {
            box.classList.add('dizzy-taxonomy-box-visible');
            box.querySelector('.tagchecklist')?.classList.add('dizzy-assigned-terms');
        });

        section('Event information', 'Public title, description and category.', elements(['#titlediv', '#postdivrich', '#postexcerpt', '#dizzy_event_categorydiv']));
        section('Date and time', 'Set the event start and optional end.', elements(['#dizzy_event_occurrences']));
        section('Artists and venue', 'Assigned artists are listed above each search field.', [artistBox, venueBox].filter(Boolean));
        section('Tickets and capacity', 'Leave ticket prices empty for a free event.', elements(['#dizzy_event_additional_details']));
        section('Featured image', 'Choose the main image for this event.', elements(['#postimagediv']));
        section('Poster Generator', 'Create social media artwork for this event.', elements(['#dizzy_event_poster_generator']));
        section('Publish', '', elements(['#submitdiv']), side);
        const statusBox = find('#dizzy-event-status');
        const statusContent = statusBox?.querySelector('.inside');
        section('Event status', 'Control whether the event is public, scheduled or archived.', statusContent ? [statusContent] : [], side);
        statusBox?.remove();

        const template = document.createElement('section');
        template.className = 'dizzy-editor-section dizzy-template-summary';
        template.innerHTML = '<div class="dizzy-editor-section-head"><h2>Page template</h2></div><div class="dizzy-editor-section-body"><strong>Event Full Width</strong><p class="dizzy-editor-success">Selected automatically</p></div>';
        side.appendChild(template);

        const readiness = document.createElement('section');
        readiness.className = 'dizzy-editor-section dizzy-readiness';
        readiness.innerHTML = '<div class="dizzy-editor-section-head"><h2>Event readiness</h2></div><div class="dizzy-editor-section-body"><p>&#10003; Event information</p><p>&#10003; Date and time</p><p>&#10003; Artists and venue</p><p>&#10003; Tickets and capacity</p><p>&#10003; Featured image</p></div>';
        side.appendChild(readiness);

        original.classList.add('dizzy-original-editor-hidden');
        document.body.classList.add('dizzy-custom-event-editor-ready');
        workspace.querySelector('.dizzy-editor-save')?.addEventListener('click', () => (document.querySelector('#publish') || document.querySelector('#save-post'))?.click());
        workspace.querySelector('.dizzy-editor-preview')?.addEventListener('click', () => document.querySelector('#post-preview')?.click());
    };

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', ready);
    else ready();
})();
