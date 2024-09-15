<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;


class WalletController extends Controller
{
    // GET: /api/wallets?user_id=1
    public function index(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',  // Ensure that the user_id exists
        ]);

        $wallets = Wallet::where('user_id', $validated['user_id'])->get();

        return response()->json($wallets, 200);
    }

    // POST: /api/wallets
    public function store(Request $request)
    {
        // Validate the request data before creating a wallet
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',  // Ensure user exists
            'icon_id' => 'required|exists:icones,id', // Ensure icon exists
            'name' => 'required|string|max:255|unique:wallets,name,NULL,id,user_id,' . $request->user_id, // Ensure unique wallet name per user
            'balance' => 'required|numeric|min:0|max:99999999.99', // Ensure balance is a positive number with a reasonable limit
            'currency' => 'sometimes|required|string|size:3', // Ensure currency is a valid 3-letter code (e.g., USD)
        ]);

        // Create the wallet
        $wallet = Wallet::create($validated);
        return response()->json($wallet, 201);
    }

    // GET: /api/wallets/{id}?user_id=1
    public function show($id, Request $request)
    {
        // Validate the user_id parameter
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id', // Ensure user exists
        ]);

        // Find the wallet based on id and user_id
        $wallet = Wallet::where('id', $id)
                        ->where('user_id', $validated['user_id'])
                        ->first();

        if (!$wallet) {
            return response()->json(['message' => 'Wallet not found or does not belong to the user'], 404);
        }

        return response()->json($wallet, 200);
    }

    // PUT/PATCH: /api/wallets/{id}?user_id=1
    public function update(Request $request, $id)
    {
        // Validate the user_id and input data
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id', // Ensure user exists
            'name' => 'sometimes|required|string|max:255|unique:wallets,name,' . $id . ',id,user_id,' . $request->user_id, // Ensure name is unique for the user
            'balance' => 'sometimes|required|numeric|min:0|max:99999999.99', // Ensure valid balance
            'currency' => 'sometimes|required|string|size:3',
        ]);

        // Find the wallet based on id and user_id
        $wallet = Wallet::where('id', $id)
                        ->where('user_id', $validated['user_id'])
                        ->first();

        if (!$wallet) {
            return response()->json(['message' => 'Wallet not found or does not belong to the user'], 404);
        }

        // Update the wallet with new validated data
        $wallet->update($validated);

        return response()->json($wallet, 200);
    }

    // DELETE: /api/wallets/{id}?user_id=1
    public function destroy($id, Request $request)
    {
        // Validate the user_id
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id', // Ensure user exists
        ]);

        // Find the wallet based on id and user_id
        $wallet = Wallet::where('id', $id)
                        ->where('user_id', $validated['user_id'])
                        ->first();

        if (!$wallet) {
            return response()->json(['message' => 'Wallet not found or does not belong to the user'], 404);
        }

        // Delete the wallet
        $wallet->delete();

        return response()->json(['message' => 'Wallet deleted successfully'], 204);
    }
}
