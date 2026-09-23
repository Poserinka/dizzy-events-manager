<?php
/** @var array<string,mixed> $experience */
defined('ABSPATH') || exit;

if (empty($experience['has_concert'])) {
    return;
}

$isDinnerOnly = ($experience['reservation_type'] ?? '') === 'dinner_only';
$ticketStatus = (string) ($experience['ticket_status'] ?? 'none');
$ticketUrl = (string) ($experience['ticket_url'] ?? '');
?>
<tr>
 <td align="center" bgcolor="#000000" class="es-m-text" style="padding:10px 0;Margin:0">
  <h3 class="es-m-txt-c es-text-mobile-size-18" style="Margin:0;font-family:'open sans', 'helvetica neue', helvetica, arial, sans-serif;mso-line-height-rule:exactly;letter-spacing:0;font-size:18px;font-style:normal;font-weight:600;line-height:27px;color:#efefef"><?php echo esc_html($isDinnerOnly ? __('Dinner only', 'dizzy-reservations-manager') : __('Dinner & Live Music', 'dizzy-reservations-manager')); ?></h3>
  <?php if ($isDinnerOnly) : ?>
   <p style="Margin:0;line-height:22px;font-size:14px"><strong><?php echo esc_html(sprintf(__('Dinner reservation — %1$s–%2$s', 'dizzy-reservations-manager'), (string) $time, (string) ($experience['dinner_cutoff'] ?? ''))); ?></strong><br><?php echo esc_html(sprintf(__('A ticketed concert starts at %s. This reservation does not include concert admission. If you would like to stay for the concert, please book Dinner + Concert.', 'dizzy-reservations-manager'), (string) ($experience['concert_time'] ?? ''))); ?></p>
  <?php else : ?>
   <p style="Margin:0;line-height:22px;font-size:14px"><strong><?php esc_html_e('Your table is guaranteed for the entire concert.', 'dizzy-reservations-manager'); ?></strong><br><?php echo esc_html((string) ($experience['event_title'] ?? '')); ?> — <?php echo esc_html((string) ($experience['concert_time'] ?? '')); ?><br><?php echo esc_html($ticketStatus === 'already_purchased' ? __('Concert tickets: already purchased and verified.', 'dizzy-reservations-manager') : ($ticketStatus === 'paid' ? sprintf(__('Concert tickets paid: %1$d × €%2$s per person.', 'dizzy-reservations-manager'), (int) ($experience['ticket_quantity'] ?? 0), number_format_i18n((float) ($experience['ticket_price'] ?? 0), 2)) : sprintf(__('Concert tickets requested: %1$d × €%2$s per person. Tickets are not valid until payment is completed.', 'dizzy-reservations-manager'), (int) ($experience['ticket_quantity'] ?? 0), number_format_i18n((float) ($experience['ticket_price'] ?? 0), 2)))); ?></p>
   <?php if ($ticketStatus === 'buy' && $ticketUrl !== '') : ?><p style="Margin:12px 0 0"><a href="<?php echo esc_url($ticketUrl); ?>" style="display:inline-block;padding:10px 18px;background:#ffb900;color:#111;text-decoration:none;font-weight:bold"><?php esc_html_e('Complete concert ticket payment', 'dizzy-reservations-manager'); ?></a></p><?php endif; ?>
  <?php endif; ?>
 </td>
</tr>

