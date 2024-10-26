<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use App\Models\Icon;
use App\Models\Currency;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;




class WalletController extends Controller
{
// GET: /api/wallets?user_id=1
// GET: /api/wallets?user_id=1
public function index(Request $request)
{
    // Fetch wallets for the authenticated user
    $wallets = Wallet::where('user_id', Auth::id())->get();

    // Fetch icons based on the icon_id present in the wallets
    $iconIds = $wallets->pluck('icon_id')->unique();
    $icons = Icon::whereIn('id', $iconIds)->pluck('path', 'id');

    // Fetch currencies based on the currency IDs present in the wallets
    $currencyIds = $wallets->pluck('currency')->unique();
    $currencies = Currency::whereIn('id', $currencyIds)->pluck('currency_code', 'id');
    $currencySymbols = Currency::whereIn('id', $currencyIds)->pluck('currency_symbols', 'id');
    $currencyCode = Currency::whereIn('id', $currencyIds)->pluck('currency_code', 'id');

    // Map icon path, transactions count, and currency details to wallets
    $wallets->map(function ($wallet) use ($icons, $currencies, $currencySymbols) {
        // Add icon path
        $wallet->icon_path = $icons->get($wallet->icon_id);

        // Fetch the number of transactions associated with this wallet
        $wallet->transactions_in_use = DB::table('transactions')->where('wallet_id', $wallet->id)->count();

        // Add currency details
        $wallet->currency_code = $currencies->get($wallet->currency);
        $wallet->currency_symbols = $currencySymbols->get($wallet->currency);


        return $wallet;
    });

    return response()->json($wallets, 200);
}


    

    // POST: /api/wallets
    public function store(Request $request)
    {
        // Define custom error messages (optional)
        $messages = [
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
        ];
    
        // Validate the request data
        $validated = $request->validate([
            'icon_id' => [
                'required',
                'exists:icones,id',
                Rule::unique('wallets')->where(function ($query) {
                    return $query->where('user_id', Auth::id());
                })
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('wallets')->where(function ($query) {
                    return $query->where('user_id', Auth::id());
                })
            ],
            'balance' => 'required|numeric|min:0|max:99999999.99',
            'currency' => 'sometimes|required',
        ], $messages);  // Pass custom error messages
    
        // Add Auth::id() to the validated data
        $validated['user_id'] = Auth::id();
    
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
        // Find the wallet based on id and authenticated user_id
        $wallet = Wallet::where('id', $id)
                        ->where('user_id', Auth::id())
                        ->first();
    
        if (!$wallet) {
            return response()->json(['message' => 'Wallet not found or does not belong to the user'], 404);
        }
    
        return response()->json($wallet, 200);
    }


    // PUT/PATCH: /api/wallets/{id}?user_id=1
    public function update(Request $request, $id)
    {
        // Validate the request data
        $validated = $request->validate([
            'icon_id' => [
                'required',
                'exists:icones,id',
            ],
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'balance' => 'required|numeric|min:0|max:99999999.99',
            'currency' => 'sometimes|required',
        ]);
    
        // Find the wallet based on id and authenticated user_id
        $wallet = Wallet::where('id', $id)
                        ->where('user_id', Auth::id())
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
        try {
            // Find the wallet based on id and authenticated user_id
            $wallet = Wallet::where('id', $id)
                            ->where('user_id', Auth::id())
                            ->first();
    
            if (!$wallet) {
                return response()->json(['message' => 'Wallet not found or does not belong to the user'], 404);
            }
    
            // Check if there are transactions associated with this wallet
            $transactionsUsingWallet = DB::table('transactions')
                ->where('wallet_id', $id)
                ->where('user_id', Auth::id())
                ->pluck('id');
    
            if ($transactionsUsingWallet->isNotEmpty()) {
                $message = "This wallet cannot be deleted because it has associated transactions.\n";
                $message .= "Transactions: " . implode(', ', $transactionsUsingWallet->toArray()) . ".\n";
                $message .= "\nTo delete this wallet, follow these steps:\n";
                $message .= "1. First, delete or reassign the transactions linked to this wallet: " . implode(', ', $transactionsUsingWallet->toArray()) . ".\n";
                $message .= "2. Once all transactions are removed or reassigned, try deleting the wallet again.\n";
    
                Log::info("Wallet ID {$id} has associated transactions and cannot be deleted.");
                return response()->json(['error' => $message], 400);
            }
    
            // If there are no associated transactions, proceed to delete
            $wallet->delete();
            Log::info("Wallet deleted successfully: ID {$id}");
            return response()->json(['message' => 'Wallet deleted successfully'], 204);
        } catch (\Exception $e) {
            Log::error('Error deleting wallet: ' . $e->getMessage());
            return response()->json(['error' => 'An unexpected error occurred: ' . $e->getMessage()], 500);
        }
    }



}
