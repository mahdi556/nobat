<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorResource extends JsonResource
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
            'name' => $this->name,
            'avatar'=>$this->avatar,
            'nezam'=>$this->nezam,
            'expert'=>$this->expert,
            'slug'=>$this->slug,
            'site'=>$this->site,
            'offices'=> OfficeResource::collection($this->offices)
        ];
    }
}
