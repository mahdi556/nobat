<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;

class PaymentController extends ApiController
{
    // add to .env file
    // ZIBAL_IR_API_KEY=zibal
    // ZIBAL_IR_CALLBACK_URL=http://localhost:8000/payment/verify

    public function send()
    {
        $merchant = env('ZIBAL_IR_API_KEY');
        $amount = 10000;
        $mobile = "شماره موبایل";
        $description = "توضیحات";
        $callbackUrl = env('ZIBAL_IR_CALLBACK_URL');
        $result = $this->sendRequest($merchant, $amount, $callbackUrl, $mobile, $description);

        $result = json_decode($result);

        // dd($result);

        if ($result->result == 100) {
            $go = "https://gateway.zibal.ir/start/$result->trackId";
            return $this->successResponse([
                'url' => $go
            ],200);
        } else {
            return $this->errorResponse('تراکنش با خطا مواجه شد', 422);
        }
    }

    public function sendRequest($merchant, $amount, $callbackUrl, $mobile = null, $description = null)
    {
        return $this->curl_post('https://gateway.zibal.ir/v1/request', [
            'merchant'     => $merchant,
            'amount'       => $amount,
            'callbackUrl'  => $callbackUrl,
            'mobile'       => $mobile,
            'description'  => $description,
        ]);
    }

    public function curl_post($url, $params)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);
        $res = curl_exec($ch);
        curl_close($ch);

        return $res;
    }
}
