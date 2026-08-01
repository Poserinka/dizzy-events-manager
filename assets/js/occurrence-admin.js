(function ($) {

    'use strict';



    /**
     * Occurrence admin manager.
     */
    const DizzyOccurrences = {


        init: function () {

            this.bindEvents();

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
         * Add new occurrence row.
         */
        addRow: function (event) {


            event.preventDefault();



            const row =
                `
                <tr class="dizzy-occurrence-row">

                    <td>
                        <input
                            type="date"
                            name="dizzy_occurrences[start_date][]"
                        >
                    </td>


                    <td>
                        <input
                            type="time"
                            name="dizzy_occurrences[start_time][]"
                        >
                    </td>


                    <td>
                        <input
                            type="date"
                            name="dizzy_occurrences[end_date][]"
                        >
                    </td>


                    <td>
                        <input
                            type="time"
                            name="dizzy_occurrences[end_time][]"
                        >
                    </td>


                    <td>

                        <input
                            type="hidden"
                            name="dizzy_occurrences[sort_order][]"
                            value="0"
                        >


                        <button
                            type="button"
                            class="button dizzy-remove-occurrence"
                        >
                            Remove
                        </button>


                    </td>

                </tr>
                `;



            $('#dizzy-occurrence-rows')
                .append(row);


            this.refreshOrder();

        },





        /**
         * Remove occurrence row.
         */
        removeRow: function (event) {


            event.preventDefault();



            const rows =
                $('.dizzy-occurrence-row');



            if (
                rows.length <= 1
            ) {


                rows
                    .find('input')
                    .val('');


                return;

            }



            $(event.currentTarget)
                .closest(
                    '.dizzy-occurrence-row'
                )
                .remove();



            this.refreshOrder();

        },





        /**
         * Refresh sort order values.
         */
        refreshOrder: function () {


            $('.dizzy-occurrence-row')
                .each(
                    function (index) {


                        $(this)
                            .find(
                                'input[name="dizzy_occurrences[sort_order][]"]'
                            )
                            .val(index);


                    }
                );

        },


    };





    $(document).ready(
        function () {

            DizzyOccurrences.init();

        }
    );



})(jQuery);