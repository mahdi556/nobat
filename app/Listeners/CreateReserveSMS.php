<?php

namespace App\Listeners;

use App\Models\Reserve;
use App\Models\User;
use App\Notifications\OTPSms;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateReserveSMS
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        $token = $event->token;
        $user_id = Reserve::where('payment_token', $token)->pluck('user_id')->firstOrFail();
        $user = User::find($user_id);
        $user->notify(new OTPSms(1233));
    }
}
