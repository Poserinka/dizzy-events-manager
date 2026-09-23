# Dizzy Emails

The Emails admin tab manages three transactional notifications. Ticket purchased and Reservation confirmed are enabled by default to preserve current behavior. Shift reminder starts disabled and is sent to the WordPress employee email approximately two hours before a published shift when enabled.

The ticket and reservation messages continue to use their existing branded PHP/HTML templates and image folders. The fields on the Emails page control each notification's enabled state, subject, and an optional message displayed in the template. Reservation status-change emails are separate and are not affected by the Reservation confirmed switch.

Shift reminder runs through WP-Cron every five minutes. Each shift update has a one-time marker, so a successfully sent reminder is not repeated; a failed send releases its marker for retry.
