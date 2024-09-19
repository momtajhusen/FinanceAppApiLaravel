<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    // ... existing methods ...

    /**
     * Store a newly created transaction in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'wallet_id' => 'required|exists:wallets,id',
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric',
            'transaction_type' => 'required|in:Income,Expense',
            'note' => 'nullable|string',
            'transaction_date' => 'required|date',
            'currency' => 'nullable|string|size:3',
            'attachment_url' => 'nullable|url',
        ]);

        // Check for validation failures
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Get currency and transaction type
        $wallet = Wallet::find($request->wallet_id);
        $category = Category::find($request->category_id);

        // Override currency and transaction_type
        $request->merge([
            'currency' => $wallet->currency,
            'transaction_type' => $category->transaction_type,
        ]);

        $transaction = Transaction::create($request->all());
        return response()->json($transaction, 201);
    }

    /**
     * Update the specified transaction in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Transaction $transaction
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Transaction $transaction)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'wallet_id' => 'required|exists:wallets,id',
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric',
            'transaction_type' => 'required|in:Income,Expense',
            'note' => 'nullable|string',
            'transaction_date' => 'required|date',
            'currency' => 'nullable|string|size:3',
            'attachment_url' => 'nullable|url',
        ]);

        // Check for validation failures
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Get currency and transaction type
        $wallet = Wallet::find($request->wallet_id);
        $category = Category::find($request->category_id);

        // Override currency and transaction_type
        $request->merge([
            'currency' => $wallet->currency,
            'transaction_type' => $category->transaction_type,
        ]);

        $transaction->update($request->all());
        return response()->json($transaction);
    }

    // ... existing methods ...
}
