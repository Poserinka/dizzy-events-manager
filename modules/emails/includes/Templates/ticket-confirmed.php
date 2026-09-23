<?php
/**
 * New reservation confirmation email.
 *
 * Available variables:
 * $site_name, $site_url, $reservation_id, $name, $email, $phone,
 * $date, $time, $guests, $message and $status.
 *
 * This file may be edited as HTML. Keep dynamic values escaped as shown below.
 */
defined('ABSPATH') || exit;
?>
<!doctype html>
<html>
<!-- Email Head Start-->
<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1" name="viewport">
  <meta name="x-apple-disable-message-reformatting">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta content="telephone=no" name="format-detection">
  <title><?php esc_html_e('Your event tickets', 'dizzy-ticket-manager'); ?></title><!--[if (mso 16)]>
    <style type="text/css">
    a {text-decoration: none;}
    </style>
    <![endif]--><!--[if gte mso 9]><style>sup { font-size: 100% !important; }</style><![endif]--><!--[if gte mso 9]>
<noscript>
         <xml>
           <o:OfficeDocumentSettings>
           <o:AllowPNG></o:AllowPNG>
           <o:PixelsPerInch>96</o:PixelsPerInch>
           </o:OfficeDocumentSettings>
         </xml>
      </noscript>
<![endif]--><!--[if mso]><xml>
    <w:WordDocument xmlns:w="urn:schemas-microsoft-com:office:word">
      <w:DontUseAdvancedTypographyReadingMail/>
    </w:WordDocument>
    </xml><![endif]--><!--[if !mso]><!-- -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap"><!--<![endif]-->
  <style type="text/css">
u + .body img ~ div div {
  display:none;
}
#outlook a {
  padding:0;
}
span.MsoHyperlink,
span.MsoHyperlinkFollowed {
  color:inherit;
  mso-style-priority:99;
}
a.es-button {
  mso-style-priority:100!important;
  text-decoration:none!important;
}
a[x-apple-data-detectors],
#MessageViewBody a {
  color:inherit!important;
  text-decoration:none!important;
  font-size:inherit!important;
  font-family:inherit!important;
  font-weight:inherit!important;
  line-height:inherit!important;
}
.es-desk-hidden {
  display:none;
  float:left;
  overflow:hidden;
  width:0;
  max-height:0;
  line-height:0;
  mso-hide:all;
}
.es-header, .esd-header-popover:not(.es-content) { background-color:transparent; background-repeat:repeat; background-position:center top }
.es-footer, .esd-footer-popover:not(.es-content) { background-color:transparent; background-repeat:repeat; background-position:center top }
@media only screen and (max-width:600px) {.es-m-p40r { padding-right:40px!important } .es-p-default { } *[class="gmail-fix"] { display:none!important } p, a { line-height:150%!important } h1, h1 a { line-height:120%!important } h2, h2 a { line-height:120%!important } h3, h3 a { line-height:120%!important } h4, h4 a { line-height:120%!important } h5, h5 a { line-height:120%!important } h6, h6 a { line-height:120%!important } h1 { font-size:36px!important; text-align:left } h2 { font-size:26px!important; text-align:left } h3 { font-size:20px!important; text-align:left } h4 { font-size:24px!important; text-align:left } h5 { font-size:20px!important; text-align:left } h6 { font-size:16px!important; text-align:left } .es-header-body h1 a, .es-content-body h1 a, .es-footer-body h1 a { font-size:36px!important } .es-header-body h2 a, .es-content-body h2 a, .es-footer-body h2 a { font-size:26px!important } .es-header-body h3 a, .es-content-body h3 a, .es-footer-body h3 a { font-size:20px!important } .es-header-body h4 a, .es-content-body h4 a, .es-footer-body h4 a { font-size:24px!important } .es-header-body h5 a, .es-content-body h5 a, .es-footer-body h5 a { font-size:20px!important } .es-header-body h6 a, .es-content-body h6 a, .es-footer-body h6 a { font-size:16px!important } .es-header-body p, .es-header-body a { font-size:14px!important } .es-content-body p, .es-content-body a { font-size:14px!important } .es-footer-body p, .es-footer-body a { font-size:14px!important } .es-infoblock p, .es-infoblock a { font-size:12px!important } .es-m-txt-c, .es-m-txt-c h1, .es-m-txt-c h2, .es-m-txt-c h3, .es-m-txt-c h4, .es-m-txt-c h5, .es-m-txt-c h6 { text-align:center!important } .es-m-txt-r, .es-m-txt-r h1, .es-m-txt-r h2, .es-m-txt-r h3, .es-m-txt-r h4, .es-m-txt-r h5, .es-m-txt-r h6 { text-align:right!important } .es-m-txt-j, .es-m-txt-j h1, .es-m-txt-j h2, .es-m-txt-j h3, .es-m-txt-j h4, .es-m-txt-j h5, .es-m-txt-j h6 { text-align:justify!important } .es-m-txt-l, .es-m-txt-l h1, .es-m-txt-l h2, .es-m-txt-l h3, .es-m-txt-l h4, .es-m-txt-l h5, .es-m-txt-l h6 { text-align:left!important } .es-m-txt-r img, .es-m-txt-c img, .es-m-txt-l img { display:inline!important } .es-m-txt-r .es-menu td { float:right!important } .es-m-txt-l .es-menu td { float:left!important } .es-m-txt-c .es-menu td { display:inline-block } .es-spacer { display:inline-table } a.es-button, button.es-button { display:inline-block!important; font-size:20px!important; padding:10px 20px 10px 20px!important; line-height:120%!important } .es-button-border { display:inline-block!important } .es-m-fw, .es-m-fw.es-fw, .es-m-fw .es-button { display:block!important } .es-m-il, .es-m-il .es-button, .es-social, .es-social td, .es-menu.es-table-not-adapt { display:inline-block!important } .es-adaptive table, .es-left, .es-right { width:100%!important; border-collapse:separate!important } .es-content table, .es-header table, .es-footer table, .es-content, .es-footer, .es-header { width:100%!important; max-width:600px!important } .adapt-img { width:100%!important; height:auto!important } .es-adapt-td { display:block!important; width:100%!important } .es-mobile-hidden, .es-hidden { display:none!important } .es-container-hidden { display:none!important } .es-desk-hidden { width:auto!important; overflow:visible!important; float:none!important; max-height:inherit!important; line-height:inherit!important } tr.es-desk-hidden { display:table-row!important } table.es-desk-hidden { display:table!important } td.es-desk-hidden { display:table-cell!important } td.es-desk-menu-hidden { display:table-cell!important } .es-m-txt-c .es-menu td.es-desk-menu-hidden { display:inline-block!important } .es-menu td { width:1%!important } table.es-table-not-adapt, .esd-block-html table, .es-m-txt-r .es-menu td, .es-m-txt-l .es-menu td, .es-m-txt-c .es-menu td { width:auto!important } .h-auto { height:auto!important } }
@media screen and (max-width:384px) {.mail-message-content { width:414px!important } }
</style>
 </head>
 <body class="body" style="width:100%;height:100%;font-family:arial, 'helvetica neue', helvetica, sans-serif;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;padding:0;Margin:0">
  <div dir="ltr" class="es-wrapper-color" lang="en" style="background-color:#FAFAFA"><!--[if gte mso 9]>
			<v:background xmlns:v="urn:schemas-microsoft-com:vml" fill="t">
				<v:fill type="tile" color="#fafafa"></v:fill>
			</v:background>
		<![endif]-->
   <table width="100%" cellspacing="0" cellpadding="0" class="es-wrapper" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-spacing:0px;padding:0;Margin:0;width:100%;height:100%;background-repeat:repeat;background-position:center top">
     <tr>
      <td valign="top" style="padding:0;Margin:0">
       <table cellpadding="0" cellspacing="0" align="center" class="es-content" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-spacing:0px;width:100%;table-layout:fixed !important">
         <tr>
          <td align="center" style="padding:0;Margin:0">
           <table bgcolor="#ffffff" align="center" cellpadding="0" cellspacing="0" class="es-content-body" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-spacing:0px;background-color:#FFFFFF;width:600px">
             <tr>
              <td style="padding:20px 0;Margin:0">
               <table cellspacing="0" cellpadding="0" align="center" bgcolor="#efefef" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-spacing:0px;border-radius:20px;background-color:#efefef;border-top:2px solid #cccccc;border-right:2px solid #cccccc;border-left:2px solid #cccccc;width:600px;border-collapse:separate;border-bottom:2px solid #cccccc">
                 <tr>
                  <td align="left" style="padding:20px;Margin:0">
                   <table cellpadding="0" cellspacing="0" width="100%" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-spacing:0px">
                     <tr>
                      <td align="center" valign="top" style="padding:0;Margin:0;width:556px">
                       <table cellpadding="0" cellspacing="0" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-spacing:0px;width:556px">
                         <tr>
                          <td align="center" style="padding:20px 0;Margin:0;font-size:0px"><img src="<?php echo esc_url(DIZZY_EMAILS_URL . 'assets/images/jazzcafe-dizzy-logo-black.png'); ?>" alt="" width="484" class="adapt-img" style="display:block;font-size:14px;border:0;outline:none;text-decoration:none;margin:0"></td>
                         </tr>
						   
						   
                         <tr>
                          <td align="center" style="padding:0;Margin:0;font-size:0"><img src="https://poserinka.com/dizzy/wp-content/uploads/2026/08/dizzy-poster-981b6d9d-8f1b-4eb6-b4e8-f6198bfd3426.png" alt="" width="556" referrerpolicy class="adapt-img" style="display:block;font-size:14px;border:0;outline:none;text-decoration:none;margin:0"></td>
                         </tr>
						   
						   
                         <tr>
                          <td align="center" style="padding:10px 0;Margin:0"><h3 class="es-m-txt-c" style="Margin:0;font-family:'open sans', 'helvetica neue', helvetica, arial, sans-serif;mso-line-height-rule:exactly;letter-spacing:0;font-size:20px;font-style:normal;font-weight:600;line-height:30px;color:#333333"><?php echo esc_html((string) $event_name); ?></h3></td>
                         </tr>
                         <tr>
                          <td align="center" style="padding:10px 0;Margin:0"><h3 class="es-m-txt-c" style="Margin:0;font-family:'open sans', 'helvetica neue', helvetica, arial, sans-serif;mso-line-height-rule:exactly;letter-spacing:0;font-size:20px;font-style:normal;font-weight:600;line-height:30px;color:#333333"><?php echo esc_html((string) $event_date); ?> · <?php echo esc_html((string) $event_time); ?></h3></td>
                         </tr>
                         <tr>
                          <td align="center" style="padding:10px 0;Margin:0"><h3 class="es-m-txt-c" style="Margin:0;font-family:'open sans', 'helvetica neue', helvetica, arial, sans-serif;mso-line-height-rule:exactly;letter-spacing:0;font-size:20px;font-style:normal;font-weight:600;line-height:30px;color:#333333">Total Price: <?php echo esc_html((string) $currency . ' ' . (string) $total_amount); ?></h3></td>
                         </tr>
						 <?php foreach ((array) $tickets as $index => $ticket) : ?>   
                         <tr>
                          <td align="center" style="padding:10px 0;Margin:0"><h3 class="es-m-txt-c" style="Margin:0;font-family:'open sans', 'helvetica neue', helvetica, arial, sans-serif;mso-line-height-rule:exactly;letter-spacing:0;font-size:20px;font-style:normal;font-weight:600;line-height:30px;color:#333333"><?php echo esc_html((string) ($ticket['label'] ?? '')); ?></h3></td>
                         </tr>
                         <tr>
                          <td align="center" style="padding:10px 0;Margin:0"><span class="es-button-border" style="border-style:solid;border-color:#2CB543;background:#5C68E2;border-width:0px;display:inline-block;border-radius:6px;width:auto"><a href="<?php echo esc_url((string) ($ticket['url'] ?? '')); ?>" target="_blank" class="es-button" style="mso-style-priority:100 !important;text-decoration:none !important;mso-line-height-rule:exactly;color:#FFFFFF;font-size:20px;font-weight:normal;padding:10px 30px;display:inline-block;background:#5C68E2;border-radius:6px;font-family:arial, 'helvetica neue', helvetica, sans-serif;font-style:normal;line-height:24px;width:auto;text-align:center;letter-spacing:0;mso-padding-alt:0;mso-border-alt:10px solid #5C68E2;text-transform:none;border-left-width:30px;border-right-width:30px">OPEN TICKET</a></span></td>
                         </tr>
						 <?php endforeach; ?>    
                         <tr>
                          <td align="center" style="padding:10px 0;Margin:0"><h3 class="es-m-txt-c" style="Margin:0;font-family:'open sans', 'helvetica neue', helvetica, arial, sans-serif;mso-line-height-rule:exactly;letter-spacing:0;font-size:20px;font-style:normal;font-weight:600;line-height:30px;color:#333333"><?php echo esc_html((string) $customer_name); ?></h3></td>
                         </tr>
                         <tr>
                          <td align="center" style="padding:10px 0;Margin:0"><h3 class="es-m-txt-c" style="Margin:0;font-family:'open sans', 'helvetica neue', helvetica, arial, sans-serif;mso-line-height-rule:exactly;letter-spacing:0;font-size:20px;font-style:normal;font-weight:600;line-height:30px;color:#333333"><?php echo esc_html((string) $customer_email); ?></h3></td>
                         </tr>
                         <tr>
                          <td align="center" style="padding:10px 0;Margin:0"><h3 class="es-m-txt-c" style="Margin:0;font-family:'open sans', 'helvetica neue', helvetica, arial, sans-serif;mso-line-height-rule:exactly;letter-spacing:0;font-size:20px;font-style:normal;font-weight:600;line-height:30px;color:#333333"><?php echo esc_html((string) $customer_phone); ?></h3></td>
                         </tr>
                         <tr>
                          <td align="center" style="padding:10px 0;Margin:0"><h3 class="es-m-txt-c" style="Margin:0;font-family:'open sans', 'helvetica neue', helvetica, arial, sans-serif;mso-line-height-rule:exactly;letter-spacing:0;font-size:20px;font-style:normal;font-weight:600;line-height:30px;color:#333333"><?php echo esc_html(sprintf(__('Reservation Number: %d', 'dizzy-ticket-manager'), (int) $order_id)); ?></h3></td>
                         </tr>
                         <tr>
                          <td align="center" style="padding:10px 0 25px;Margin:0"><p style="Margin:0;mso-line-height-rule:exactly;font-family:'open sans', 'helvetica neue', helvetica, arial, sans-serif;line-height:21px;letter-spacing:0;font-weight:normal;color:#333333;font-size:14px">You can&nbsp;cancel your&nbsp;reservation by reaching us via phone.</p></td>
                         </tr>
                         <tr>
                          <td align="center" style="padding:15px 0;Margin:0;font-size:0">
                           <table cellpadding="0" cellspacing="0" class="es-table-not-adapt es-social" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-spacing:0px">
                             <tr>
                              <td align="center" valign="top" class="es-m-p40r" style="padding:0 40px 0 0;Margin:0"><a href="https://www.facebook.com/DizzyJazz/" target="_blank" style="mso-line-height-rule:exactly;text-decoration:underline;color:#5C68E2;font-size:14px;font-weight:inherit"><img alt="Fb" width="32" title="Facebook" src="<?php echo esc_url(DIZZY_EMAILS_URL . 'assets/images/facebook-logo-black.png'); ?>" style="display:block;font-size:14px;border:0;outline:none;text-decoration:none;margin:0"></a></td>
                              <td valign="top" align="center" class="es-m-p40r" style="padding:0 40px 0 0;Margin:0"><a href="https://www.instagram.com/jazz_cafe_dizzy" target="_blank" style="mso-line-height-rule:exactly;text-decoration:underline;color:#5C68E2;font-size:14px;font-weight:inherit"><img alt="Inst" width="32" title="Instagram" src="<?php echo esc_url(DIZZY_EMAILS_URL . 'assets/images/instagram-logo-black.png'); ?>" style="display:block;font-size:14px;border:0;outline:none;text-decoration:none;margin:0"></a></td>
                              <td valign="top" align="center" style="padding:0;Margin:0"><a target="_blank" href="https://www.tiktok.com/discover/jazz-cafe-dizzy-rotterdam" style="mso-line-height-rule:exactly;text-decoration:underline;color:#5C68E2;font-size:14px;font-weight:inherit"><img alt="TT" width="32" title="TikTok" src="<?php echo esc_url(DIZZY_EMAILS_URL . 'assets/images/tiktok-logo-black.png'); ?>" style="display:block;font-size:14px;border:0;outline:none;text-decoration:none;margin:0"></a></td>
                             </tr>
                           </table></td>
                         </tr>
                         <tr>
                          <td align="center" style="padding:0 0 35px;Margin:0"><p style="Margin:0;mso-line-height-rule:exactly;font-family:'open sans', 'helvetica neue', helvetica, arial, sans-serif;line-height:21px;letter-spacing:0;font-weight:normal;color:#333333;font-size:14px">2026 © Jazzcafe Dizzy. All Rights Reserved.</p><p style="Margin:0;mso-line-height-rule:exactly;font-family:'open sans', 'helvetica neue', helvetica, arial, sans-serif;line-height:21px;letter-spacing:0;font-weight:normal;color:#333333;font-size:14px">'s-Gravendijkwal 127 3021 EK, Rotterdam</p><p style="Margin:0;mso-line-height-rule:exactly;font-family:'open sans', 'helvetica neue', helvetica, arial, sans-serif;line-height:21px;letter-spacing:0;font-weight:normal;color:#333333;font-size:14px">010 477 3014</p></td>
                         </tr>
                         <tr>
                          <td style="padding:0;Margin:0;font-family:'open sans', 'helvetica neue', helvetica, arial, sans-serif">
                           <table width="100%" cellpadding="0" cellspacing="0" class="es-menu" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-spacing:0px">
                             <tr class="links">
                              <td width="33.33%" align="center" valign="top" style="Margin:0;border:0;padding:5px">
                              <div style="vertical-align:middle;display:block">
                              <a target="_blank" href="https://dizzy.nl/unsubscribe" style="mso-line-height-rule:exactly;text-decoration:none;font-family:'open sans', 'helvetica neue', helvetica, arial, sans-serif;font-weight:normal;display:block;color:#999999;font-size:14px">Unsubscribe</a>
                              </div></td>
                              <td align="center" valign="top" width="33.33%" style="Margin:0;border:0;padding:5px;border-left:1px solid #cccccc">
                              <div style="vertical-align:middle;display:block">
                              <a href="https://dizzy.nl/privacy-policy" target="_blank" style="mso-line-height-rule:exactly;text-decoration:none;font-family:'open sans', 'helvetica neue', helvetica, arial, sans-serif;font-weight:normal;display:block;color:#999999;font-size:14px">Privacy Policy</a>
                              </div></td>
                              <td valign="top" width="33.33%" align="center" style="Margin:0;border:0;padding:5px;border-left:1px solid #cccccc">
                              <div style="vertical-align:middle;display:block">
                              <a target="_blank" href="https://dizzy.nl/terms-of-use" style="mso-line-height-rule:exactly;text-decoration:none;font-family:'open sans', 'helvetica neue', helvetica, arial, sans-serif;font-weight:normal;display:block;color:#999999;font-size:14px">Terms of Use</a>
                              </div></td>
                             </tr>
                           </table></td>
                         </tr>
                       </table></td>
                     </tr>
                   </table></td>
                 </tr>
               </table></td>
             </tr>
           </table></td>
         </tr>
       </table>
       <table cellpadding="0" cellspacing="0" align="center" class="es-content" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-spacing:0px;width:100%;table-layout:fixed !important">
         <tr>
          <td align="center" class="es-info-area" style="padding:0;Margin:0">
           <table align="center" cellpadding="0" cellspacing="0" bgcolor="#00000000" class="es-content-body" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-spacing:0px;background-color:transparent;width:600px">
             <tr>
              <td align="left" style="padding:20px;Margin:0">
               <table cellpadding="0" cellspacing="0" width="100%" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-spacing:0px">
                 <tr>
                  <td align="center" valign="top" style="padding:0;Margin:0;width:560px">
                   <table cellpadding="0" cellspacing="0" width="100%" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-spacing:0px">
                     <tr>
                      <td align="center" style="padding:0;Margin:0;display:none"></td>
                     </tr>
                   </table></td>
                 </tr>
               </table></td>
             </tr>
           </table></td>
         </tr>
       </table></td>
     </tr>
   </table>
  </div>
 </body>
</html>