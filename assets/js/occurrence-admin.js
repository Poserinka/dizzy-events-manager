(function ($) {
    'use strict';

    /**
     * Occurrence admin manager.
     */
    const DizzyOccurrences = {
        init: function () {
            this.bindEvents();
            this.enableSorting();
            this.refreshOrder();
        },

        bindEvents: function () {
            $(document).on(
                'click',
                '.dizzy-add-occurrence',
                this.addRow.bind(this)
            );

            $(document).on(
                'click',
                '.dizzy-remove-occurrence',
                this.removeRow.bind(this)
            );
        },

        /**
         * Enable drag-and-drop sorting.
         */
        enableSorting: function () {
            const rows = $('#dizzy-occurrence-rows');

            if (! rows.length || typeof rows.sortable !== 'function') {
                return;
            }

            rows.sortable({
                axis: 'y',
                handle: '.dizzy-sort-handle',
                items: '> .dizzy-occurrence-row',
                placeholder: 'dizzy-sort-placeholder',
                forcePlaceholderSize: true,
                tolerance: 'pointer',
                update: this.refreshOrder.bind(this)
            });
        },

        /**
         * Create a drag handle.
         */
        createSortHandle: function () {
            return $('<button>', {
                type: 'button',
                class: 'button-link dizzy-sort-handle',
                title: DizzyEventsAdmin.dragLabel,
                'aria-label': DizzyEventsAdmin.dragLabel
            }).append(
                $('<span>', {
                    class: 'dashicons dashicons-move',
                    'aria-hidden': 'true'
                })
            );
        },

        /**
         * Create a time select field.
         */
        createTimeSelect: function (name) {
            const select = $('<select>', {
                name: name
            });

            select.append(
                $('<option>', {
                    value: '',
                    text: DizzyEventsAdmin.selectTimeLabel
                })
            );

            DizzyEventsAdmin.timeOptions.forEach(function (time) {
                select.append(
                    $('<option>', {
                        value: time,
                        text: time.replace(':', '.')
                    })
                );
            });

            return select;
        },

        /**
         * Add a new occurrence row.
         */
        addRow: function (event) {
            event.preventDefault();

            const row = $('<tr>', {
                class: 'dizzy-occurrence-row'
            });

            row.append(
                $('<td>').append(
                    $('<input>', {
                        type: 'date',
                        name: 'dizzy_occurrences[start_date][]'
                    })
                ),
                $('<td>').append(
                    this.createTimeSelect(
                        'dizzy_occurrences[start_time][]'
                    )
                ),
                $('<td>').append(
                    $('<input>', {
                        type: 'date',
                        name: 'dizzy_occurrences[end_date][]'
                    })
                ),
                $('<td>').append(
                    this.createTimeSelect(
                        'dizzy_occurrences[end_time][]'
                    )
                ),
                $('<td>', {
                    class: 'dizzy-occurrence-actions'
                }).append(
                    this.createSortHandle(),
                    $('<input>', {
                        type: 'hidden',
                        name: 'dizzy_occurrences[sort_order][]',
                        value: '0'
                    }),
                    $('<button>', {
                        type: 'button',
                        class: 'button dizzy-remove-occurrence',
                        text: DizzyEventsAdmin.removeLabel
                    })
                )
            );

            $('#dizzy-occurrence-rows').append(row);
            this.refreshOrder();
        },

        /**
         * Remove an occurrence row.
         */
        removeRow: function (event) {
            event.preventDefault();

            const rows = $('.dizzy-occurrence-row');

            if (rows.length <= 1) {
                const row = rows.first();

                row.find('input:not([type="hidden"]), select').val('');
                row.find(
                    'input[name="dizzy_occurrences[sort_order][]"]'
                ).val('0');

                return;
            }

            $(event.currentTarget)
                .closest('.dizzy-occurrence-row')
                .remove();

            this.refreshOrder();
        },

        /**
         * Refresh sort order values.
         */
        refreshOrder: function () {
            $('.dizzy-occurrence-row').each(function (index) {
                $(this)
                    .find(
                        'input[name="dizzy_occurrences[sort_order][]"]'
                    )
                    .val(index);
            });
        }
    };

    $(document).ready(function () {
        DizzyOccurrences.init();
    });
})(jQuery);
