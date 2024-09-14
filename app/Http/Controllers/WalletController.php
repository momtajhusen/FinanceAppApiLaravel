<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    /**
     * Display a listing of the wallets.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $wallets = Wallet::with('user')->get();
        return response()->json($wallets);
    }

    /**
     * Store a newly created wallet in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'balance' => 'required|numeric',
            'currency' => 'nullable|string|size:3',
        ]);

        $wallet = Wallet::create($request->all());
        return response()->json($wallet, 201);
    }

    /**
     * Display the specified wallet.
     *
     * @param \App\Models\Wallet $wallet
     * @return \Illuminate\Http\Response
     */
    public function show(Wallet $wallet)
    {
        return response()->json($wallet->load('user'));
    }

    /**
     * Update the specified wallet in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Wallet $wallet
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Wallet $wallet)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'balance' => 'required|numeric',
            'currency' => 'nullable|string|size:3',
        ]);

        $wallet->update($request->all());
        return response()->json($wallet);
    }

    /**
     * Remove the specified wallet from storage.
     *
     * @param \App\Models\Wallet $wallet
     * @return \Illuminate\Http\Response
     */
    public function destroy(Wallet $wallet)
    {
        $wallet->delete();
        return response()->json(null, 204);
    }
}
