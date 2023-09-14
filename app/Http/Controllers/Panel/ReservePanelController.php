<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\ApiController;
use App\Http\Resources\ReserveResource;
use App\Models\Reserve;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Morilog\Jalali\Jalalian;
use Illuminate\Support\Str;

class ReservePanelController extends ApiController
{
    public static function monthlyReserves($month)
    {
        if ($month == 0) {

            $month = (Jalalian::now())->getMonth();
        }
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
                // Create a new object for each date
                $results[$date] = [
                    'date' => $date,
                    'types' => [],
                ];
            }

            // Assign status and count to types array
            $results[$date]['types'][$status] = $count;
        }

        // Convert the associative array to indexed array
        $results = array_values($results);


        return response()->json(['reserves' => $results]);
    }
    public function store(Request $request)
    {
        DB::beginTransaction();
        $delay = 0;
        if ($request->qty > 1) {
            $isReserve = false;
            for ($i = 1; $i < $request->qty; $i++) {
                $time = Carbon::createFromFormat('Y-m-d H:i:s', $request->time)->addMinutes(($i) * 15);
                $reserve =  Reserve::all()->where('time', $time->format('Y-m-d H:i:s'))->first();
                if ($reserve) {
                    $isReserve = true;
                }
            }
            if ($isReserve) {
                return response()->json(['message' => 'already reserved'], 406);
            }
        }
        // if (Carbon::parse($request->time)->diffInMinutes(Carbon::parse($request->display_time)) > 0) {
        //     $delay = $diffInMinutes;
        // } 
        $user = User::where('cellphone', $request->patient['cellphone'])->first();
        if ($user) {
            $user->update(['name' => $request->patient['name'], 'codemelli' => $request->patient['codemelli']]);
        }
        if (!$user) {
            $user = User::create([
                'cellphone' => $request->patient['cellphone'],
                'name' => $request->patient['name'],
                'codemelli' => $request->patient['codemelli'],
                'new' => 0
            ]);
        }
        $reserve = Reserve::create([
            'user_id' => $user->id,
            'hour' => $request->hour,
            'minute' => $request->minute,
            'time' => $request->time,
            'qty' => $request->qty,
            'type' => $request->type,
            'office_id' => 22,
            'status' => 'wait',
            'section' => $request->section,
            'delay' => $delay,
            'uuid' => Str::uuid(),
            'persian_date' => Jalalian::forge(Carbon::parse($request->time)->timestamp)->format('Y-m-d'),
            'source' => 'office'
        ]);
        if ($request->qty > 1) {
            for ($i = 1; $i < $request->qty; $i++) {
                $time = Carbon::createFromFormat('Y-m-d H:i:s', $request->time)->addMinutes(($i) * 15);
                $reserve = Reserve::create([
                    'user_id' => $user->id,
                    'hour' => $time->format('H'),
                    'minute' => $time->format('i'),
                    'time' => $time->format('Y-m-d H:i:s'),
                    'qty' => 1,
                    'type' => $request->type,
                    'office_id' => 22,
                    'related' => true,
                    'status' => 'wait',
                    'section' => $request->section,
                    'delay' => $delay,
                    'persian_date' => Jalalian::forge(Carbon::parse($request->time)->timestamp)->format('Y-m-d'),
                    'source' => 'office'
                ]);
            }
        }
        DB::commit();
        return $this->successResponse(['reserve' => new ReserveResource($reserve)], 200);
    }
}
