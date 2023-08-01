<?php

namespace App\Http\Controllers;

use App\Http\Resources\OfficeResource;
use App\Http\Resources\ReserveResource;
use App\Models\Office;
use App\Models\Reserve;
use Illuminate\Http\Request;

class OfficeController extends ApiController


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
        // dd( json_decode($request->getContent(),true)[0]);
        $office = Office::create([
            'doctor_id' =>  1,
            'work_time' => json_encode(json_decode($request->getContent(), true)[0]),
            'visit_type' => json_encode(json_decode($request->getContent(), true)[1])
        ]);
        return $this->successResponse([
            'office' => new OfficeResource($office),
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Office $office)
    {
        
         $reserves = Reserve::all()->where('office_id', $office->id);
        return $this->successResponse([
            'office' => new OfficeResource($office->load('doctor')),
            'reserves' => ReserveResource::collection($reserves)
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Office $office)
    {
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Office $office)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Office $office)
    {
        //
    }
}
