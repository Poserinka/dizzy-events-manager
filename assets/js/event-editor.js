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

        const relationFields = document.createElement('div');
        relationFields.className = 'dizzy-relation-fields';
        const relationData = window.dizzyEventEditorData || { nonce: '', taxonomies: [] };
        const nonce = document.createElement('input');
        nonce.type = 'hidden';
        nonce.name = 'dizzy_event_relations_nonce';
        nonce.value = relationData.nonce || '';
        relationFields.appendChild(nonce);

        relationData.taxonomies.forEach((item) => {
            const field = document.createElement('div');
            field.className = 'dizzy-relation-field';
            const label = document.createElement('label');
            label.textContent = item.label;
            const dropdown = document.createElement('details');
            dropdown.className = 'dizzy-multi-dropdown';
            const summary = document.createElement('summary');
            const choices = document.createElement('div');
            choices.className = 'dizzy-multi-dropdown-choices';
            const updateSummary = () => {
                const names = [...choices.querySelectorAll('input:checked')].map((input) => input.dataset.name);
                summary.textContent = names.length ? names.join(', ') : `Select ${item.label.toLowerCase()}`;
                summary.title = summary.textContent;
            };
            item.terms.forEach((term) => {
                const choice = document.createElement('label');
                const input = document.createElement('input');
                const name = document.createElement('span');
                input.type = 'checkbox';
                input.name = `dizzy_event_relations[${item.taxonomy}][]`;
                input.value = String(term.id);
                input.checked = Boolean(term.selected);
                input.dataset.name = term.name;
                input.addEventListener('change', updateSummary);
                name.textContent = term.name;
                choice.append(input, name);
                choices.appendChild(choice);
            });
            dropdown.append(summary, choices);
            updateSummary();
            const help = document.createElement('p');
            help.className = 'description';
            help.textContent = item.terms.length ? 'You can select more than one item.' : 'No existing items are available yet.';
            field.append(label, dropdown, help);
            relationFields.appendChild(field);
        });

        ['#tagsdiv-dizzy_event_artist', '#tagsdiv-dizzy_event_venue', '#tagsdiv-dizzy_event_tag'].forEach((selector) => find(selector)?.remove());

        section('Event information', 'Public title, description and category.', elements(['#titlediv', '#postdivrich', '#postexcerpt', '#dizzy_event_categorydiv']));
        section('Date and time', 'Set the event start and optional end.', elements(['#dizzy_event_occurrences']));
        section('Artists, venue and tags', 'Select one or more existing items from each list.', [relationFields]);
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
