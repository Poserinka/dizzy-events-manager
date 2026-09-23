<?php
/** @var array{employee_name:string,shift_date:string,start_time:string,end_time:string,position:string} $data */
defined('ABSPATH') || exit;
$logo = DIZZY_EMAILS_URL . 'assets/images/jazzcafe-dizzy-logo-black.png';
?>
<!doctype html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?php esc_html_e('Shift reminder', 'dizzy-events-manager'); ?></title></head>
<body style="margin:0;padding:24px;background:#f4f5f7;font-family:Arial,Helvetica,sans-serif;color:#20242a">
<table role="presentation" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px;margin:auto;background:#fff;border-collapse:collapse">
<tr><td style="padding:30px;text-align:center"><img src="<?php echo esc_url($logo); ?>" alt="Jazzcafe Dizzy" width="270" style="display:inline-block;max-width:100%;height:auto"></td></tr>
<tr><td style="padding:0 30px 30px"><h1 style="font-size:26px;line-height:1.3"><?php esc_html_e('Your shift is coming up', 'dizzy-events-manager'); ?></h1>
<p><?php echo esc_html(sprintf(__('Hello %s,', 'dizzy-events-manager'), $data['employee_name'])); ?></p>
<?php if ($data['message'] !== '') : ?><p><?php echo nl2br(esc_html($data['message'])); ?></p><?php endif; ?>
<p><strong><?php esc_html_e('Date:', 'dizzy-events-manager'); ?></strong> <?php echo esc_html($data['shift_date']); ?><br>
<strong><?php esc_html_e('Time:', 'dizzy-events-manager'); ?></strong> <?php echo esc_html($data['start_time'] . '–' . $data['end_time']); ?><br>
<strong><?php esc_html_e('Position:', 'dizzy-events-manager'); ?></strong> <?php echo esc_html($data['position']); ?></p></td></tr>
</table></body></html>
