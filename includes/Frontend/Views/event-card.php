<?php if (! empty($event->dates)): ?>

<section class="dizzy-event-dates">

    <h4>
        <?php esc_html_e(
            'Dates',
            'dizzy-events-manager'
        ); ?>
    </h4>


    <ul>

        <?php foreach ($event->dates as $date): ?>

            <li>

                <strong>
                    <?php echo esc_html(
                        $date->date
                    ); ?>
                </strong>

                <span>
                    <?php echo esc_html(
                        $date->time
                    ); ?>
                </span>

            </li>

        <?php endforeach; ?>

    </ul>

</section>

<?php endif; ?>