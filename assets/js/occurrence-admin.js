(function ($) {

    'use strict';


    const DizzyEventsAdmin = {


        init: function () {

            this.bindEvents();

            this.initDatePicker();

            this.initSortable();

            this.updateOrder();

        },



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



            DizzyEventsAdmin.initDatePicker();

            DizzyEventsAdmin.updateOrder();

        },



        removeOccurrence: function (event) {

            event.preventDefault();


            $(event.currentTarget)
                .closest(
                    '.dizzy-occurrence-row'
                )
                .remove();



            DizzyEventsAdmin.updateOrder();

        },



        initDatePicker: function () {


            $('.dizzy-occurrence-date')
                .each(
                    function () {


                        if (
                            $(this)
                            .hasClass(
                                'hasDatepicker'
                            )
                        ) {

                            return;

                        }



                        $(this)
                            .datepicker({

                                dateFormat:
                                    'yy-mm-dd',


                                minDate:
                                    0

                            });

                    }
                );

        },



        initSortable: function () {


            const list =
                $('.dizzy-occurrences-list');



            if (
                ! list.length
            ) {

                return;

            }



            list.sortable({

                items:
                    '.dizzy-occurrence-row',


                handle:
                    '.dizzy-sort-handle',


                placeholder:
                    'dizzy-sort-placeholder',


                update:
                    function () {

                        DizzyEventsAdmin.updateOrder();

                    }

            });


        },



        updateOrder: function () {


            $('.dizzy-occurrences-list')
                .find(
                    '.dizzy-occurrence-row'
                )
                .each(
                    function (index) {


                        $(this)
                            .find(
                                '.dizzy-occurrence-order'
                            )
                            .val(
                                index
                            );

                    }
                );


        }


    };



    $(document).ready(
        function () {

            DizzyEventsAdmin.init();

        }
    );


})(jQuery);