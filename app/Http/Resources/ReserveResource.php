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
            'id' => $this->id,
            'patient_name' => $this->user->name,
            'patient_cellphone' => $this->user->cellphone,
            'patient_codemelli' => $this->user->codemelli,
            'hour' => $this->hour,
            'minute' => $this->minute,
            'time' => $this->time,
            'qty' => $this->qty,
            'type' => $this->type,
            'office_id' => 22,
            'related' => $this->related,
            'delay' => $this->delay,
            'status' => $this->status,
            'doctor' => $this->office->doctor->name,
            'source' => $this->source
        ];
    }
}
