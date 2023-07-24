<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReserveResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request)
    {
        return [
            'hour' => $this->hour,
            'minute' => $this->minute,
            'time' => $this->time,
            'qty' => $this->qty,
            'type' => $this->type,
            'office_id' => 22,
            'related'=>$this->related,
            'delay'=>$this->delay,
            'status'=>$this->status
        ];
    }
}
