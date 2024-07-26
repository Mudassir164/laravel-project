<html xmlns="http://www.w3.org/1999/xhtml" lang="en-GB">
   <head>
      <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
      <title>{{ env('APP_NAME') }}</title>
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <style type="text/css">
         a[x-apple-data-detectors] {color: inherit !important;}
      </style>
      <style id="__web-inspector-hide-shortcut-style__">
         .__web-inspector-hide-shortcut__, .__web-inspector-hide-shortcut__ *, .__web-inspector-hidebefore-shortcut__::before, .__web-inspector-hideafter-shortcut__::after
         {
         visibility: hidden !important;
         }
      </style>
   </head>
   <body style="margin: 0; padding: 0;">
      <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
         <tbody>
            <tr>
               <td style="padding: 20px 0 30px 0;">
                  <table align="center" border="0" cellpadding="0" cellspacing="0" width="600" style="border-collapse: collapse; border: 1px solid #8256db;">
                     <tbody>
                        <tr>
                           <td align="center" bgcolor="#70bbd9" style="padding: 40px 0 30px 0;">
                              <h1>{{ env('APP_NAME') }}</h1>
                           </td>
                        </tr>
                        <tr>
                           <td bgcolor="#ffffff" style="padding: 40px 30px 40px 30px;">
                              <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse;">
                                 <tbody>
                                    <tr>
                                       <td style="color: #153643; font-family: Arial, sans-serif;">
                                          <h1 style="font-size: 24px; margin: 0;">Dear {{ $details['name'] }}</h1>
                                       </td>
                                    </tr>
                                    <tr>
                                       <td style="color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 24px; padding: 20px 0 30px 0;">
                                          <p style="margin: 0;">Please use the following code on {{ env('APP_NAME') }} App.<b>{{ $details['code'] }}</b>.</p>
                                          <p>Please remember this code will expire at {{$details['expires_at']}}</p>
                                       </td>
                                    </tr>
                                 </tbody>
                              </table>
                           </td>
                        </tr>
                        <tr>
                           <td bgcolor="#ee4c50" style="padding: 30px 30px;">
                              <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse;">
                                 <tbody>
                                    <tr>
                                       <td style="color: #aae009; font-family: Arial, sans-serif; font-size: 14px; text-align:right">
                                          <p style="margin: 0;">® {{ env('APP_NAME') }}</p>
                                       </td>
                                    </tr>
                                 </tbody>
                              </table>
                           </td>
                        </tr>
                     </tbody>
                  </table>
               </td>
            </tr>
         </tbody>
      </table>
      </td>
      </tr>
      </tbody></table>
   </body>
</html>