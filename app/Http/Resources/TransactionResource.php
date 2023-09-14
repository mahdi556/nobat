<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request) 
    {
        return [
            'amount' => $this->amount,
            'token' => $this->token,
            'date' => $this->created_at,
            'status' => $this->status,
            'message'=>$this->message
             
        ];
    }
}
