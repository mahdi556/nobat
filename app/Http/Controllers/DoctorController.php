<?php

namespace App\Http\Controllers;

use App\Http\Resources\DoctorResource;
use App\Models\Doctor;
use Illuminate\Http\Request;

class DoctorController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $doctors=Doctor::all();
        return $this->successResponse([
            'doctors' => DoctorResource::collection($doctors),
        ], 200);
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
         $doctor = Doctor::create([
            'name'=>$request->name
        ]);
        return $this->successResponse([
            'doctor' => new DoctorResource($doctor),
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Doctor $doctor)
    {
        return $this->successResponse([
            'doctor' => new DoctorResource($doctor),
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Doctor $doctor)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Doctor $doctor)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Doctor $doctor)
    {
        //
    }
    public function verifySite(Request $request)
    {
        $doctor_name=$request->query('name');
        $doctor=Doctor::where('site',$doctor_name)->firstOrFail();
        return $this->successResponse([
            'doctor' => new DoctorResource($doctor),
        ], 200);
    }
}
