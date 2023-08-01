<?php

namespace App\Http\Resources;

use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfficeResource extends JsonResource
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
            'doctor_id' => $this->doctor_id,
            'work_time' => json_decode( $this->work_time) ,
            'visit_type' => json_decode( $this->visit_type) ,
            'interval' => 15,
            'name' => $this->name,
            'address' => $this->address,
            'doctor'=>new DoctorResource($this->whenLoaded('doctor')) 
        ];
    }
}
