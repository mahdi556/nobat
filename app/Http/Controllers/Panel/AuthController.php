<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\ApiController;
use App\Http\Resources\UserResource;
use App\Models\Reserve;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Morilog\Jalali\Jalalian;

class AuthController extends ApiController
{
    public function login(Request $request)
    {
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'cellphone' => ['required', 'regex:/^(\+98|0)?9\d{9}$/'],
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->messages(), 422);
        }

        $user = User::where('cellphone', $request->cellphone)->first();

        if (!$user) {
            return $this->errorResponse(['user' => ['کاربر مورد نظر پیدا نشد']], 422);
        }

        if ($user->is_admin == 0) {
            return $this->errorResponse(['user' => ['کاربر مورد نظر پیدا نشد']], 422);
        }

        if (!Hash::check($request->password, $user->password)) {
            return $this->errorResponse(['password' => ['کلمه عبور اشتباه است']], 422);
        }

        $token = $user->createToken('myApp', ['admin'])->plainTextToken;

        return $this->successResponse([
            'user' => new UserResource($user),
            'token' => $token
        ], 200);
    }


    public function register(Request $request)
    {
        $user = User::where('cellphone', $request->cellphone)->first();
        if ($user) {
            $user->update(['password' => Hash::make($request->password)]);
            return $this->errorResponse(['کاربر موجود است'], 422);
        }
        if (!$user) {
            $user = User::create([
                'cellphone' => $request->cellphone,
                'password' => Hash::make($request->password),
            ]);
        }
        return $this->successResponse(['user' => new UserResource($user)], 200);
    }
    public function me()
    {
        $user = User::find(Auth::id());
        return $this->successResponse(new UserResource($user), 200);
    }

    public function checkCellphone(Request $request)
    {
        $user = User::where('cellphone', $request->cellphone)->firstOrFail();
        return $this->successResponse(['user' => new UserResource($user)], 200);
    }

    public function parsianDate()
    {
        // $date = '2023-08-23 11:30:00';

        // $timestamp = Carbon::parse($date)->timestamp;
        // $new = Jalalian::forge($timestamp);
        // return $new->format('Y-m-d');
        $reserves = Reserve::all();
        foreach ($reserves as $res) {
            $res->update(['persian_date' => Jalalian::forge(Carbon::parse($res->time)->timestamp)->format('Y-m-d')]);
        }
    }
    public function exportReport()
    {
        $month = 5; // Assuming you want to retrieve data for September (you can change it to the desired month)
        $query = DB::table('reserves')
            ->select(DB::raw('DATE(persian_date) as date'), 'status', DB::raw('COUNT(*) as count'))
            ->whereMonth('persian_date', '=', $month)
            ->groupBy('date', 'status')
            ->get();
        $results = [];

        foreach ($query as $row) {
            $date = $row->date;
            $status = $row->status;
            $count = $row->count;

            if (!isset($results[$date])) {
                $results[$date] = (object)[];
            }

            $results[$date]->{$status} = $count;
        }



        return response()->json($results);
    }
}
