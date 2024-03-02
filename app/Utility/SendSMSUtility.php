<?php

namespace App\Utility;

class SendSMSUtility
{
    public static function sendSMS($to, $text)
    {
        $sender = urlencode("TOMSHER");
        $sms_url = 'http://tomsher.me/sms/smsapi';
        $sms_args = array(
            'api_key' => env('SMS_API_KEY', 'R60001345fd4c0b80cb815.29446877'),
            'type' => 'text',
            'contacts' => $to,
            'senderid' => $sender,
            'msg' => $text,
        );

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $sms_url . "?" . http_build_query($sms_args));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        curl_close($curl);

        return $result;
    }
}
