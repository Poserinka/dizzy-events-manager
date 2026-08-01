(function ($) {

    'use strict';


    /**
     * Dizzy Events admin handler.
     */
    const DizzyEventsAdmin = {


        /**
         * Initialize.
         */
        init: function () {

            this.bindEvents();

        },


        /**
         * Bind events.
         */
        bindEvents: function () {


            $(document).on(
                'click',
                '.dizzy-add-occurrence',
                this.addOccurrence
            );


            $(document).on(
                'click',
                '.dizzy-remove-occurrence',
                this.removeOccurrence
            );


        },


        /**
         * Add occurrence row.
         */
        addOccurrence: function (event) {

            event.preventDefault();


            const template =
                $('#dizzy-occurrence-template')
                    .html();


            if (!template) {
                return;
            }


            $('.dizzy-occurrences-list')
                .append(template);

        },


        /**
         * Remove occurrence row.
         */
        removeOccurrence: function (event) {

            event.preventDefault();


            $(event.currentTarget)
                .closest('.dizzy-occurrence-row')
                .remove();

        }


    };


    $(document).ready(function () {

        DizzyEventsAdmin.init();

    });


})(jQuery);