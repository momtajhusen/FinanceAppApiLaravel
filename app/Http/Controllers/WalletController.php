<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use App\Models\Icon;



class WalletController extends Controller
{
    // GET: /api/wallets?user_id=1
    public function index(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',  // Ensure that the user_id exists
        ]);

        // Fetch wallets
        $wallets = Wallet::where('user_id', $validated['user_id'])->get();

        // Fetch icons based on the icon_id present in the wallets
        $iconIds = $wallets->pluck('icon_id')->unique();
        $icons = Icon::whereIn('id', $iconIds)->pluck('path', 'id');

        // Map icon path to wallets
        $wallets->map(function ($wallet) use ($icons) {
            $wallet->icon_path = $icons->get($wallet->icon_id);
            return $wallet;
        });

        return response()->json($wallets, 200);
    }
    // POST: /api/wallets
    public function store(Request $request)
    {
        // Define custom error messages (optional)
        $messages = [
            'user_id.required' => 'The user is required.',
            'user_id.exists' => 'The selected user does not exist.',
            'icon_id.required' => 'The icon is required.',
            'icon_id.exists' => 'The selected icon does not exist.',
            'icon_id.unique' => 'This icon is already associated with a wallet for this user.',
            'name.required' => 'The wallet name is required.',
            'name.unique' => 'You already have a wallet with this name.',
            'balance.required' => 'The balance is required.',
            'balance.numeric' => 'The balance must be a number.',
            'balance.min' => 'The balance must be at least 0.',
            'balance.max' => 'The balance cannot exceed 99,999,999.99.',
            'currency.required' => 'The currency is required.',
            'currency.size' => 'The currency must be a valid 3-letter code.',
        ];
    
        // Validate the request data
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'icon_id' => [
                'required',
                'exists:icones,id',
                Rule::unique('wallets')->where(function ($query) use ($request) {
                    return $query->where('user_id', $request->user_id);
                })
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('wallets')->where(function ($query) use ($request) {
                    return $query->where('user_id', $request->user_id);
                })
            ],
            'balance' => 'required|numeric|min:0|max:99999999.99',
            'currency' => 'sometimes|required|string|size:3',
        ], $messages);  // Pass custom error messages
    
        // Create the wallet if validation passes
        try {
            $wallet = Wallet::create($validated);
            return response()->json($wallet, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create wallet. Please try again.'], 500);
        }
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
        // Validate the request data before creating a wallet
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',  // Ensure user exists
            'icon_id' => [
                'required',
                'exists:icones,id', // Ensure icon exists
                Rule::unique('wallets')->where(function ($query) use ($request) {
                    return $query->where('user_id', $request->user_id);
                })
            ], // Ensure unique icon_id per user_id
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('wallets')->where(function ($query) use ($request) {
                    return $query->where('user_id', $request->user_id);
                })
            ], // Ensure unique wallet name per user
            'balance' => 'required|numeric|min:0|max:99999999.99', // Ensure balance is a positive number with a reasonable limit
            'currency' => 'sometimes|required|string|size:3', // Ensure currency is a valid 3-letter code (e.g., USD)
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
