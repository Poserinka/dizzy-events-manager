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

        const relationData = window.dizzyEventEditorData || { nonce: '', fields: { artists: [], venue: 'Jazzcafe Dizzy', tags: '' } };
        const nonce = document.createElement('input');
        nonce.type = 'hidden';
        nonce.name = 'dizzy_event_relations_nonce';
        nonce.value = relationData.nonce || '';
        const artistFields = document.createElement('div');
        artistFields.className = 'dizzy-artist-profiles';
        artistFields.appendChild(nonce);
        let artistIndex = 0;
        const addArtist = (artist = {}) => {
            const index = artistIndex++;
            const profile = document.createElement('div');
            profile.className = 'dizzy-artist-profile';
            const textField = (key, label, value) => {
                const wrap = document.createElement('label');
                const caption = document.createElement('span');
                const input = document.createElement('input');
                caption.textContent = label;
                input.type = 'text';
                input.name = `dizzy_event_artists[${index}][${key}]`;
                input.value = value || '';
                wrap.append(caption, input);
                return wrap;
            };
            profile.append(textField('name', 'Name', artist.name), textField('role', 'Role', artist.role), textField('contact', 'Contact', artist.contact));
            const photo = document.createElement('div');
            photo.className = 'dizzy-artist-profile-photo';
            const heading = document.createElement('strong');
            heading.textContent = 'Artist Photo';
            const image = document.createElement('img');
            image.alt = '';
            if (artist.imageUrl) image.src = artist.imageUrl;
            const imageId = document.createElement('input');
            imageId.type = 'hidden';
            imageId.name = `dizzy_event_artists[${index}][image_id]`;
            imageId.value = String(artist.imageId || 0);
            const select = document.createElement('button');
            select.type = 'button'; select.className = 'button'; select.textContent = 'Select image';
            const removeImage = document.createElement('button');
            removeImage.type = 'button'; removeImage.className = 'button'; removeImage.textContent = 'Remove image';
            const removeArtist = document.createElement('button');
            removeArtist.type = 'button'; removeArtist.className = 'button-link-delete'; removeArtist.textContent = 'Remove artist';
            select.addEventListener('click', () => {
                const frame = wp.media({title: 'Select Artist Photo', button: {text: 'Use this image'}, library: {type: 'image'}, multiple: false});
                frame.on('select', () => { const selected = frame.state().get('selection').first().toJSON(); imageId.value = String(selected.id); image.src = selected.sizes?.medium?.url || selected.url; });
                frame.open();
            });
            removeImage.addEventListener('click', () => { imageId.value = '0'; image.removeAttribute('src'); });
            removeArtist.addEventListener('click', () => profile.remove());
            photo.append(heading, image, imageId, select, removeImage, removeArtist);
            profile.appendChild(photo);
            artistFields.appendChild(profile);
        };
        (relationData.fields.artists || []).forEach(addArtist);
        if (!artistFields.querySelector('.dizzy-artist-profile')) addArtist();
        const addArtistButton = document.createElement('button');
        addArtistButton.type = 'button'; addArtistButton.className = 'button'; addArtistButton.textContent = 'Add Artist';
        addArtistButton.addEventListener('click', () => { addArtist(); artistFields.appendChild(addArtistButton); });
        artistFields.appendChild(addArtistButton);

        const simpleField = (name, label, value, help) => {
            const wrapper = document.createElement('div');
            wrapper.className = 'dizzy-simple-event-field';
            const fieldLabel = document.createElement('label'); fieldLabel.textContent = label;
            const input = document.createElement('input'); input.type = 'text'; input.name = name; input.value = value || '';
            const description = document.createElement('p'); description.className = 'description'; description.textContent = help;
            wrapper.append(fieldLabel, input, description);
            return wrapper;
        };
        const venueField = simpleField('dizzy_event_venue_name', 'Venue Name', relationData.fields.venue, 'Jazzcafe Dizzy is used by default.');
        const tagsField = simpleField('dizzy_event_tags', 'Tags', relationData.fields.tags, 'Separate multiple tags with commas.');

        ['#tagsdiv-dizzy_event_artist', '#tagsdiv-dizzy_event_venue', '#tagsdiv-dizzy_event_tag'].forEach((selector) => find(selector)?.remove());

        section('Event information', 'Public title, description and category.', elements(['#titlediv', '#postdivrich', '#postexcerpt', '#dizzy_event_categorydiv']));
        section('Date and time', 'Set the event start and optional end.', elements(['#dizzy_event_occurrences']));
        section('Artist', 'Add one or more artists for this event.', [artistFields]);
        section('Venue', 'Enter the event venue.', [venueField]);
        section('Tags', 'Enter event-specific tags.', [tagsField]);
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
