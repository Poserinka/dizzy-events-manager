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
        const section = (title, description, nodes, target = main, className = '') => {
            if (!nodes.length) return;
            const wrapper = document.createElement('section');
            wrapper.className = `dizzy-editor-section ${className}`.trim();
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
        const posterBox = find('#dizzy_event_poster_generator');
        const posterShell = posterBox?.querySelector('.dizzy-poster-generator-shell');
        let posterGeneratorPane = posterBox?.querySelector('.dizzy-poster-generator-pane');
        let posterOutputPane = posterBox?.querySelector('.dizzy-poster-output-pane');
        const posterAction = posterShell?.dataset.action || '';

        // Backwards compatibility for the poster markup rendered by older
        // Dizzy Social Media Manager versions.
        if (posterBox && (!posterGeneratorPane || !posterOutputPane)) {
            const inside = posterBox.querySelector('.inside');
            const backgroundInput = inside?.querySelector('#dizzy_poster_background_id');
            const backgroundPreviewLegacy = inside?.querySelector('#dizzy_poster_background_preview');
            const selectBackground = inside?.querySelector('#dizzy_select_poster_background');
            const useFeatured = inside?.querySelector('#dizzy_use_featured_background');
            const formatLegacy = inside?.querySelector('#dizzy_poster_format');
            const generateLegacy = inside?.querySelector('#dizzy_generate_poster');
            if (inside && backgroundInput && backgroundPreviewLegacy && selectBackground && useFeatured && formatLegacy && generateLegacy) {
                posterGeneratorPane = document.createElement('div');
                posterGeneratorPane.className = 'dizzy-poster-generator-pane';
                posterOutputPane = document.createElement('div');
                posterOutputPane.className = 'dizzy-poster-output-pane';

                const backgroundCard = document.createElement('div');
                backgroundCard.className = 'dizzy-poster-background-card';
                const backgroundLabel = Array.from(inside.querySelectorAll('p')).find((node) => {
                    const strong = node.querySelector('strong');
                    return strong && /background image/i.test(strong.textContent || '');
                });
                if (backgroundLabel) backgroundCard.appendChild(backgroundLabel);
                backgroundCard.append(backgroundInput, backgroundPreviewLegacy);
                backgroundPreviewLegacy.classList.add('dizzy-poster-background-preview');
                posterGeneratorPane.appendChild(backgroundCard);

                const backgroundActions = selectBackground.closest('p') || document.createElement('div');
                backgroundActions.classList.add('dizzy-poster-actions');
                posterGeneratorPane.appendChild(backgroundActions);
                const nonceLegacy = inside.querySelector('[name="dizzy_poster_nonce"]');
                const postIdLegacy = inside.querySelector('[name="post_id"]');
                if (nonceLegacy) posterGeneratorPane.prepend(nonceLegacy);
                if (postIdLegacy) posterGeneratorPane.prepend(postIdLegacy);

                const outputControls = formatLegacy.closest('p') || document.createElement('div');
                outputControls.classList.add('dizzy-poster-output-controls');
                posterOutputPane.appendChild(outputControls);
                const outputCard = document.createElement('div');
                outputCard.className = 'dizzy-poster-output-card';
                inside.querySelectorAll('.notice.inline').forEach((notice) => outputCard.appendChild(notice));
                const outputPreviewLegacy = document.createElement('div');
                outputPreviewLegacy.className = 'dizzy-poster-output-preview';
                const outputImage = Array.from(inside.children).find((node) => node.tagName === 'IMG');
                if (outputImage) outputPreviewLegacy.appendChild(outputImage);
                outputCard.appendChild(outputPreviewLegacy);
                const outputActions = document.createElement('div');
                outputActions.className = 'dizzy-poster-actions';
                inside.querySelectorAll(':scope > p > a.button').forEach((link) => outputActions.appendChild(link));
                outputActions.appendChild(generateLegacy);
                posterOutputPane.appendChild(outputCard);
                posterOutputPane.appendChild(outputActions);
            }
        }

        ['#tagsdiv-dizzy_event_artist', '#tagsdiv-dizzy_event_venue', '#tagsdiv-dizzy_event_tag'].forEach((selector) => find(selector)?.remove());

        section('Event information', 'Public title, description and category.', elements(['#titlediv', '#postdivrich', '#postexcerpt', '#dizzy_event_categorydiv']));
        section('Date and time', 'Set the event start and optional end.', elements(['#dizzy_event_occurrences']));
        section('Artist', 'Add one or more artists for this event.', [artistFields], main, 'dizzy-editor-artist-section');
        section('Venue', 'Enter the event venue.', [venueField]);
        section('Tags', 'Enter event-specific tags.', [tagsField]);
        section('Tickets and capacity', 'Leave ticket prices empty for a free event.', elements(['#dizzy_event_additional_details']));
        section('Featured image', 'Choose the main image for this event.', elements(['#postimagediv']), main, 'dizzy-editor-featured-section');
        if (posterGeneratorPane && posterOutputPane) {
            section('Poster Generator', 'Create social media artwork for this event.', [posterGeneratorPane], main, 'dizzy-editor-poster-section');
            section('Output format', '', [posterOutputPane], main, 'dizzy-editor-output-section');
            posterBox?.remove();
        } else {
            section('Poster Generator', 'Create social media artwork for this event.', posterBox ? [posterBox] : [], main, 'dizzy-editor-poster-section');
        }
        const featuredBox = find('#postimagediv');
        const featuredRemove = featuredBox?.querySelector('#remove-post-thumbnail');
        const featuredImageLink = featuredBox?.querySelector('#set-post-thumbnail');
        const featuredInside = featuredBox?.querySelector('.inside');
        if (featuredBox && featuredImageLink && featuredInside) {
            const featuredPreview = document.createElement('div');
            featuredPreview.className = 'dizzy-featured-image-preview';
            const featuredEmpty = document.createElement('span');
            featuredEmpty.className = 'dizzy-featured-image-empty';
            featuredEmpty.textContent = 'No image selected';
            featuredPreview.append(featuredImageLink, featuredEmpty);
            featuredInside.prepend(featuredPreview);

            const featuredActions = document.createElement('div');
            featuredActions.className = 'dizzy-featured-actions dizzy-poster-actions';
            const featuredSetButton = document.createElement('button');
            featuredSetButton.type = 'button';
            featuredSetButton.className = 'button';
            featuredSetButton.textContent = 'Set featured image';
            featuredSetButton.addEventListener('click', () => featuredImageLink.click());
            featuredActions.appendChild(featuredSetButton);
            if (featuredRemove) featuredActions.appendChild(featuredRemove);
            featuredBox.appendChild(featuredActions);

            const syncFeaturedPreview = () => {
                const hasImage = Boolean(featuredImageLink.querySelector('img'));
                featuredImageLink.classList.toggle('has-image', hasImage);
                featuredEmpty.hidden = hasImage;
            };
            new MutationObserver(syncFeaturedPreview).observe(featuredImageLink, {childList: true, subtree: true});
            syncFeaturedPreview();
        }
        section('Publish', '', elements(['#submitdiv']), side);
        const statusBox = find('#dizzy-event-status');
        const statusContent = statusBox?.querySelector('.inside');
        section('Event status', 'Control whether the event is public, scheduled or archived.', statusContent ? [statusContent] : [], side);
        statusBox?.remove();

        const posterWorkspace = workspace;
        const backgroundId = posterWorkspace.querySelector('#dizzy_poster_background_id');
        const backgroundPreview = posterWorkspace.querySelector('.dizzy-poster-background-preview img');
        const backgroundEmpty = posterWorkspace.querySelector('.dizzy-poster-background-empty');
        const setPosterBackground = (id, url) => {
            if (!backgroundId || !backgroundPreview || !backgroundEmpty) return;
            backgroundId.value = String(id || 0);
            if (url) {
                backgroundPreview.src = url;
                backgroundPreview.hidden = false;
                backgroundEmpty.hidden = true;
            } else {
                backgroundPreview.removeAttribute('src');
                backgroundPreview.hidden = true;
                backgroundEmpty.hidden = false;
            }
        };
        posterWorkspace.querySelector('.dizzy-poster-select-image')?.addEventListener('click', () => {
            const frame = wp.media({title: 'Select poster background', button: {text: 'Use this image'}, library: {type: 'image'}, multiple: false});
            frame.on('select', () => {
                const selected = frame.state().get('selection').first().toJSON();
                setPosterBackground(selected.id, selected.sizes?.medium?.url || selected.url);
            });
            frame.open();
        });
        posterWorkspace.querySelector('.dizzy-poster-use-featured')?.addEventListener('click', () => {
            const featuredData = posterWorkspace.querySelector('#dizzy_poster_featured_id');
            const featuredId = Number(find('#_thumbnail_id')?.value || featuredData?.value || 0);
            const featuredImage = find('#postimagediv .inside img');
            const featuredUrl = featuredImage?.src || featuredData?.dataset.url || '';
            if (featuredId > 0 && featuredUrl) setPosterBackground(featuredId, featuredUrl);
        });
        const formatSelect = posterWorkspace.querySelector('#dizzy_poster_format');
        const outputPreview = posterWorkspace.querySelector('.dizzy-poster-output-preview');
        const updateOutputRatio = () => {
            if (!formatSelect || !outputPreview) return;
            const ratios = {social_square: '1 / 1', social_portrait: '4 / 5', social_story: '9 / 16', print_a4: '210 / 297'};
            outputPreview.style.aspectRatio = ratios[formatSelect.value] || '1 / 1';
        };
        formatSelect?.addEventListener('change', updateOutputRatio);
        updateOutputRatio();
        posterWorkspace.querySelector('.dizzy-poster-generate')?.addEventListener('click', () => {
            const postId = posterWorkspace.querySelector('#dizzy_poster_post_id')?.value || '';
            const nonceValue = posterWorkspace.querySelector('#dizzy_poster_nonce')?.value || '';
            if (!posterAction || !postId || !nonceValue) return;
            const generateForm = document.createElement('form');
            generateForm.method = 'post';
            generateForm.action = posterAction;
            generateForm.hidden = true;
            const values = {
                action: 'dizzy_generate_poster',
                post_id: postId,
                dizzy_poster_nonce: nonceValue,
                background_id: backgroundId?.value || '0',
                format: formatSelect?.value || 'social_square',
            };
            Object.entries(values).forEach(([name, value]) => {
                const input = document.createElement('input');
                input.type = 'hidden'; input.name = name; input.value = value;
                generateForm.appendChild(input);
            });
            document.body.appendChild(generateForm);
            generateForm.submit();
        });

        original.classList.add('dizzy-original-editor-hidden');
        document.body.classList.add('dizzy-custom-event-editor-ready');
        workspace.querySelector('.dizzy-editor-save')?.addEventListener('click', () => (document.querySelector('#publish') || document.querySelector('#save-post'))?.click());
        workspace.querySelector('.dizzy-editor-preview')?.addEventListener('click', () => document.querySelector('#post-preview')?.click());
    };

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', ready);
    else ready();
})();

