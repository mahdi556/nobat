<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Notifications\OTPSms;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends ApiController
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cellphone' => ['required', 'regex:/^(\+98|0)?9\d{9}$/']
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->messages(), 422);
        }

        $user = User::where('cellphone', $request->cellphone)->first();
        $OTPCode = mt_rand(1000, 9999);
        $loginToken = Hash::make('DCDCojncd@cdjn%!!ghnjrgtn&&');

        if ($user) {
            $user->update([
                'otp' => $OTPCode,
                'login_token' => $loginToken,
                'new' => 0

            ]);
        } else {
            $user = User::Create([
                'cellphone' => $request->cellphone,
                'otp' => $OTPCode,
                'login_token' => $loginToken,
                'new' => 1
            ]);
        }
        // $user->notify(new OTPSms($OTPCode));

        return $this->successResponse(['login_token' => $loginToken, 'new' => $user->new], 200);
    }

    public function checkOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'otp' => 'required|digits:4',
            'login_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->messages(), 422);
        }

        $user = User::where('login_token', $request->login_token)->firstOrFail();

        if ($user->otp == $request->otp) {
            $token = $user->createToken('myApp', ['user'])->plainTextToken;
            if ($user->new == 1) {
                $validator = Validator::make($request->all(), [
                    'name' => 'required|string',
                    'codeMelli' => 'required|string'
                ]);
                $user->update([
                    'name' => $request->name,
                    'codemelli' => $request->codeMelli,
                    'new' => 0

                ]);
            }
            return $this->successResponse([
                'user' => new UserResource($user),
                'token' => $token
            ], 200);
        } else {
            return $this->errorResponse(['otp' => ['کد ورود نادرست است']], 422);
        }
    }
    public function me()
    {
        $user = User::find(Auth::id());
        return $this->successResponse(new UserResource($user), 200);
    }
    public function logout()
    {
        auth()->user()->tokens()->delete();

        return $this->successResponse(['data' => ['logged out']], 200);
    }
}
