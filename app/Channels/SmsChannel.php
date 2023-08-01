<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use SoapClient;

class SmsChannel
{

    public function send($notifiable, Notification $notification)
    {
        // dd($notification->code,$notifiable);
        $client = new SoapClient("http://ippanel.com/class/sms/wsdlservice/server.php?wsdl");
        $user = "9133048270"; 
        $pass = "Faraz@1292665254"; 
        $fromNum = "+983000505"; 
        $toNum = $notifiable->cellphone; 
        $pattern_code = "jyn6y95ek8"; 
        $input_data = array( "verification-code" => $notification->code); 
        echo $client->sendPatternSms($fromNum,$toNum,$user,$pass,$pattern_code,$input_data);

    }
}
