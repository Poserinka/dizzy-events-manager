# Dizzy Emails

The Emails admin tab manages three transactional notifications. Ticket purchased and Reservation confirmed are enabled by default to preserve current behavior. Shift reminder starts disabled and is sent to the WordPress employee email approximately two hours before a published shift when enabled.

All mail delivery goes through `includes/Delivery.php`. Transactional messages use `includes/Mailer.php`; newsletter campaigns use `includes/CampaignSender.php`. Their branded ticket, reservation, reservation-status, newsletter, and shift templates live under `includes/Templates/`, with one shared image set under `assets/images/`. The fields on the Emails page control each transactional notification's enabled state, subject, and an optional message displayed in the template. Reservation status-change emails and newsletter campaigns retain their own workflows and are not affected by the Reservation confirmed switch.

Shift reminder runs through WP-Cron every five minutes. Each shift update has a one-time marker, so a successfully sent reminder is not repeated; a failed send releases its marker for retry.
