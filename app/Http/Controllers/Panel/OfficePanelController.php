<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\ApiController;
use App\Http\Resources\OfficeResource;
use App\Http\Resources\Panel\OfficePanelResource;
use App\Http\Resources\ReserveResource;
use App\Models\Office;
use App\Models\Reserve;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class OfficePanelController extends ApiController
{
    public function getDailyReserves(Office $office)
    {

        $Reserves = Reserve::where('office_id', $office->id)->whereDate('time', Carbon::now()->toDateString())->where('related', 0)->orderBy('time', 'asc')->get();
        return $this->successResponse([
            'reserves' => ReserveResource::collection($Reserves),
            'office' => new OfficeResource($office),
            'monthly_reserves' => ReservePanelController::monthlyReserves(0)
        ], 200);
    }
    public function deleteReserve(Reserve $reserve)
    {
        $reserve->delete();
        return $this->successResponse(['رزرو با موفقیت حذف شد'], 200);
    }
    public function backToWaitReserve(Reserve $reserve)
    {
        $reserve->update(['status' => 'wait']);
        return $this->successResponse(['رزرو با موفقیت حذف شد'], 200);
    }

    public function getOffices()
    {
        $user = User::find(Auth::id());
        if ($user->is_admin == 1) {
            return $this->successResponse(['offices' =>   OfficePanelResource::collection(
                $user->offices
            )], 200);
        } else {
            $this->errorResponse(['کاربر یافت نشد'], 422);
        }
    }
    public function getReserves(Office $office, $time)
    {
         $Reserves = Reserve::where('office_id', $office->id)->whereDate('time', $time)->where('related', 0)->orderBy('time', 'asc')->get();
        return $this->successResponse([
            'reserves' => ReserveResource::collection($Reserves),
        ], 200);
    }

    public function acceptReserve(Reserve $reserve)
    {

        $this->nonreferral($reserve->time, $reserve->section);
        $reserve = Reserve::section($reserve->section)->date($reserve->time)->where('time', $reserve->time)->first();
        $currentTime = Carbon::now();
        $time = Carbon::createFromFormat('Y-m-d H:i:s', $reserve->time);
        $diffInMinutes = Carbon::parse($time)->diffInMinutes($currentTime, false);
        $delay = $diffInMinutes;
        $reserve->update(['status' => 'done', 'delay' => $delay]);
        $reserve->save();
        if ($diffInMinutes > 0) {
            Reserve::section($reserve->section)->date($reserve->time)->whereNotIn('status', ['done'])->where('section', $reserve->section)->update(['delay' => $diffInMinutes]);
            $this->refreshReserves($reserve->section, $reserve->time);
        } else {
            Reserve::section($reserve->section)->date($reserve->time)->whereIn('status', ['wait'])->whereTime('time', '>', $reserve->time)->where('section', $reserve->section)->update(['delay' => 0]);
        }
        $this->refreshLeavedReserves($reserve->section, $reserve->time);

        return $this->successResponse([
            'reserve' => new ReserveResource($reserve),
        ], 200);
    }


    public function nonreferral($time, $section)
    {

        $reserves = Reserve::section($section)->date($time)->whereTime('time', '<', $time)->whereTime('time', '<',  Carbon::parse(Carbon::now()))->where('related', 0)->where('status', 'wait')->get();
        foreach ($reserves as $myres) {
            $myres->update(['status' => 'non-referral']);
        }
    }
    public function refreshReserves($section, $time)
    {
        $revs = Reserve::section($section)->date($time)->where('related', 0)->where('status', 'wait')->whereTime('time', '>', Carbon::parse(Carbon::now()))->orderBy('time', 'asc')->get();
        foreach ($revs as $rev) {
            $freeRevs = Reserve::section($section)->date($time)->where('related', 0)->where('status', 'wait')->whereTime('time', '<', $rev->time)->where('delay', '>', 0)->orderBy('time', 'asc')->first();
            if (!$freeRevs) {
                $rev->update(['delay' => 0]);
            }
        }

        $first_future_que = $revs->first();
        if ($first_future_que) {
            $past_que = Reserve::section($section)->date($time)->where('related', 0)->whereIn('status', ['wait', 'done'])->whereTime('time', '<', Carbon::parse(Carbon::now()))->orderBy('time', 'desc')->first();
            $base_time = (Carbon::parse($past_que->time))->addMinutes(($past_que->qty * 15) + $past_que->delay);
            // dd($past_que, $base_time, $first_future_que->time);
            if ($base_time > Carbon::parse($first_future_que->time)) {
                $first_future_que->update(['delay' => ($base_time)->diffInMinutes(Carbon::parse($first_future_que->time))]);
                foreach ($revs as $reserve) {
                    $reserve->update(['delay' => $first_future_que->delay]);
                }
            }
        }
    }

    public function refreshLeavedReserves($section, $time)
    {
        $reserves = Reserve::section($section)->date($time)->whereTime('time', '<',  Carbon::parse(Carbon::now()))->where('related', 0)->where('status', 'wait')->orderBy('time', 'asc')->get();
        $lastDone = Reserve::section($section)->date($time)->whereTime('time', '<',  Carbon::parse(Carbon::now()))->where('related', 0)->where('status', 'done')->orderBy('time', 'desc')->first();
        $base_time = (Carbon::parse($lastDone->time))->addMinutes(($lastDone->qty * 15) + $lastDone->delay);
        foreach ($reserves as $rev) {
            $total_qty = Reserve::section($section)->date($time)->whereTime('time', '<',  Carbon::parse($rev->time))->where('related', 0)->where('status', 'wait')->orderBy('time', 'asc')->sum('qty');
            $real_time = Carbon::parse($base_time)->addMinutes($total_qty * 15);
            $rev->update(['delay' => ($real_time)->diffInMinutes(Carbon::parse($rev->time))]);
        }
    }
}
