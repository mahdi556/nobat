<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Http\Controllers\Controller;
use App\Http\Resources\ReserveResource;
use App\Http\Resources\TransactionResource;
use App\Models\Reserve;
use Illuminate\Http\Request;

class TransactionController extends ApiController
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        $transaction = Transaction::where('token', $request->query('token'))->firstOrFail();
        $reserve = Reserve::where('payment_token', $request->query('token'))->first();
        if ($reserve) {
            return $this->successResponse([
                'reserve' => new ReserveResource($reserve),
                'transaction' => new TransactionResource($transaction)
            ], 200);
        } else {
            return $this->successResponse([
                'transaction' => new TransactionResource($transaction)
            ], 200);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Transaction $transaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transaction $transaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction)
    {
        //
    }
}
