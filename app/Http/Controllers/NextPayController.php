<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NextPayController extends ApiController
{
    public function send(Request $request)
    {
        // $validator = Validator::make($request->all(), [
        //     'user_id' => 'required',
        //     'order_items' => 'required',
        //     'order_items.*.product_id' => 'required|integer',
        //     'order_items.*.quantity' => 'required|integer',
        //     'request_from' => 'required'
        // ]);

        // if ($validator->fails()) {
        //     return $this->errorResponse($validator->messages(), 422);
        // }
        // $reserve = ReserveController::checkReserve($request);
        // if ($reserve->getStatusCode() == 406) {
        //     return $this->errorResponse(['message' => 'already reserved'], 406);
        // }
        // $totalAmount = 0;
        // $deliveryAmount = 0;
        // foreach ($request->order_items as $orderItem) {
        //     $product = Product::findOrFail($orderItem['product_id']);
        //     if ($product->quantity < $orderItem['quantity']) {
        //         return $this->errorResponse('The product quantity is incorrect', 422);
        //     }

        //     $totalAmount += $product->price * $orderItem['quantity'];
        //     $deliveryAmount += $product->delivery_amount;
        // }

        // $payingAmount = $totalAmount + $deliveryAmount;

        // $amounts = [
        //     'totalAmount' => $totalAmount,
        //     'deliveryAmount' => $deliveryAmount,
        //     'payingAmount' => $payingAmount,
        // ];


        $merchant = '71f2a47d-a3c7-48df-8588-3c5a242e0db1';
        $amount = 10000;
        $mobile = "شماره موبایل";
        $description = "توضیحات";
        $callbackUrl = env('ZIBAL_IR_CALLBACK_URL');
        $result = $this->sendRequest($merchant, $amount, $callbackUrl, $mobile, $description);

        $result = json_decode($result);
        dd($result);


        if ($result->result == 100) {
            OrderController::create($request, $amounts, $result->trackId);
            ReserveController::store($request, $result->trackId);
            $go = "https://gateway.zibal.ir/start/$result->trackId";
            return $this->successResponse([
                'url' => $go
            ], 200);
        } else {
            return $this->errorResponse('تراکنش با خطا مواجه شد', 422);
        }
    }
    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'status' => 'required',
            'success' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->messages(), 422);
        }

        $merchant = env('ZIBAL_IR_API_KEY');
        $token = $request->token;
        $result = json_decode($this->verifyRequest($merchant, $token));

        if ($result->result == 100) {
            // if (Transaction::where('trans_id', $result->transId)->exists()) {
            //     return $this->errorResponse('این تراکنش قبلا توی سیستم ثبت شده است', 422);
            // }
            ReserveController::update($token);
            OrderController::update($token, $result->refNumber);
            // event(new CreateReserve($token));
            return $this->successResponse('تراکنش با موفقیت انجام شد', 200);
        } else if ($result->result == 202 || $result->result == 203) {
            ReserveController::destroy($token, $msg = 'تراکنش با خطا مواجه شد کد' . $result->result);
            return $this->errorResponse('تراکنش با خطا مواجه شد', 422);
        } else if ($result->result == 102 || $result->result == 103 || $result->result == 104) {
            ReserveController::destroy($token, $msg = ' خطای درگاه کد'   . $result->result);
            return $this->errorResponse('خطای درگاه', 422);
        } else if ($result->result == 201) {
            return $this->errorResponse('خطای درگاه', 422);
        } else {
            ReserveController::destroy($token, $msg = ' خطای ناشناخته کد' . $result->result);
            return $this->errorResponse('خطای ناشناخته کد', 422);
        }
    }


    public function sendRequest($merchant, $amount, $callbackUrl, $mobile = null, $description = null)
    {
        return $this->curl_post('https://nextpay.org/nx/gateway/token', [
            'api_key'     => '71f2a47d-a3c7-48df-8588-3c5a242e0db1',
            'amount'       => 10000,
            'callback_uri'  => $callbackUrl,
            'order_id' => 6548484,
            'customer_phone' => '09133048270'
        ]);
     }
    function verifyRequest($merchant, $token)
    {
        return $this->curl_post('https://gateway.zibal.ir/v1/verify', [
            'merchant'     => $merchant,
            'trackId' => $token,
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
