<?php

namespace App\Http\Controllers;

use App\Http\Resources\ReserveResource;
use App\Models\Reserve;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReserveController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $delay = 0;
        $diffInMinutes = Carbon::parse($request->time)->diffInMinutes(Carbon::parse($request->display_time));
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
                return $this->errorResponse(['message' => 'already reserved'], 406);
            }
        }
        if (Carbon::parse($request->time)->diffInMinutes(Carbon::parse($request->display_time)) > 0) {
            $delay = $diffInMinutes;
        }

        $reserve = Reserve::create([
            'hour' => $request->hour,
            'minute' => $request->minute,
            'time' => $request->time,
            'qty' => $request->qty,
            'type' => $request->type,
            'office_id' => 22,
            'status' => 'wait',
            'section' => $request->section,
            'delay' => $delay
        ]);
        if ($request->qty > 1) {
            for ($i = 1; $i < $request->qty; $i++) {
                $time = Carbon::createFromFormat('Y-m-d H:i:s', $request->time)->addMinutes(($i) * 15);
                $reserve = Reserve::create([
                    'hour' => $time->format('H'),
                    'minute' => $time->format('i'),
                    'time' => $time->format('Y-m-d H:i:s'),
                    'qty' => 1,
                    'type' => $request->type,
                    'office_id' => 22,
                    'related' => true,
                    'status' => 'wait',
                    'section' => $request->section,
                    'delay' => $delay
                ]);
            }
        }
        // $this->refreshLeavedReserves();
        // $this->refreshReserves();

        return $this->successResponse([
            'reserve' => new ReserveResource($reserve),
        ], 200);
    }


    /**
     * Display the specified resource.
     */
    public function show(Reserve $reserve)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Reserve $reserve)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Reserve $reserve)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reserve $reserve)
    {
        //
    }

    public function task(Request $request)
    {
        $this->nonreferral($request->time,$request->section);
        $reserve = Reserve::section($request->section)->where('time', $request->time)->first();
        $currentTime = Carbon::now();
        $time = Carbon::createFromFormat('Y-m-d H:i:s', $request->time);
        $diffInMinutes = Carbon::parse($time)->diffInMinutes($currentTime, false);
        $delay = $diffInMinutes;
        $reserve->update(['status' => 'done', 'delay' => $delay]);
        $reserve->save();
        if ($diffInMinutes > 0) {
            Reserve::section($request->section)->whereNotIn('status', ['done'])->where('section', $reserve->section)->update(['delay' => $diffInMinutes]);
            $this->refreshReserves($request->section);
        } else {
            Reserve::section($request->section)->whereIn('status', ['wait'])->whereTime('time', '>', $request->time)->where('section', $reserve->section)->update(['delay' => 0]);
        }
        $this->refreshLeavedReserves($request->section);

        return $this->successResponse([
            'reserve' => new ReserveResource($reserve),
        ], 200);
    }


    public function nonreferral($time,$section)
    {

        $reserves = Reserve::section($section)->whereTime('time', '<', $time)->whereTime('time', '<',  Carbon::parse(Carbon::now()))->where('related', 0)->where('status', 'wait')->get();
        foreach ($reserves as $myres) {
            $myres->update(['status' => 'non-referral']);
        }
    }
    public function refreshReserves($section)
    {
        $revs = Reserve::section($section)->where('related', 0)->where('status', 'wait')->whereTime('time', '>', Carbon::parse(Carbon::now()))->orderBy('time', 'asc')->get();
        foreach ($revs as $rev) {
            $freeRevs = Reserve::section($section)->where('related', 0)->where('status', 'wait')->whereTime('time', '<', $rev->time)->where('delay', '>', 0)->orderBy('time', 'asc')->first();
            if (!$freeRevs) {
                $rev->update(['delay' => 0]);
            }
        }

        $first_future_que = $revs->first();
        $past_que = Reserve::section($section)->where('related', 0)->whereIn('status', ['wait', 'done'])->whereTime('time', '<', Carbon::parse(Carbon::now()))->orderBy('time', 'desc')->first();
        $base_time = (Carbon::parse($past_que->time))->addMinutes(($past_que->qty * 15) + $past_que->delay);
        $first_future_que->update(['delay' => ($base_time)->diffInMinutes(Carbon::parse($first_future_que->time))]);
        foreach ($revs as $reserve) {
            $reserve->update(['delay' => $first_future_que->delay]);
        }
    }

    public function refreshLeavedReserves($section)
    {
        $reserves = Reserve::section($section)->whereTime('time', '<',  Carbon::parse(Carbon::now()))->where('related', 0)->where('status', 'wait')->orderBy('time', 'asc')->get();
        $lastDone = Reserve::section($section)->whereTime('time', '<',  Carbon::parse(Carbon::now()))->where('related', 0)->where('status', 'done')->orderBy('time', 'desc')->first();
        $base_time = (Carbon::parse($lastDone->time))->addMinutes(($lastDone->qty * 15) + $lastDone->delay);
        foreach ($reserves as $rev) {
            $total_qty = Reserve::section($section)->whereTime('time', '<',  Carbon::parse($rev->time))->where('related', 0)->where('status', 'wait')->orderBy('time', 'asc')->sum('qty');
            $real_time = Carbon::parse($base_time)->addMinutes($total_qty * 15);
            $rev->update(['delay' => ($real_time)->diffInMinutes(Carbon::parse($rev->time))]);
        }
    }

    public function resetReserves()
    {
        $reserves = Reserve::get();
        foreach ($reserves as $reserve) {
            $reserve->update(['status' => 'wait', 'delay' => 0]);
        }
    }
}
