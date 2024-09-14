<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * Display a listing of the transactions.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $transactions = Transaction::with('user', 'wallet', 'category')->get();
        return response()->json($transactions);
    }

    /**
     * Store a newly created transaction in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
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

        $transaction = Transaction::create($request->all());
        return response()->json($transaction, 201);
    }

    /**
     * Display the specified transaction.
     *
     * @param \App\Models\Transaction $transaction
     * @return \Illuminate\Http\Response
     */
    public function show(Transaction $transaction)
    {
        $transaction->load('user', 'wallet', 'category');
        return response()->json($transaction);
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
        $request->validate([
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

        $transaction->update($request->all());
        return response()->json($transaction);
    }

    /**
     * Remove the specified transaction from storage.
     *
     * @param \App\Models\Transaction $transaction
     * @return \Illuminate\Http\Response
     */
    public function destroy(Transaction $transaction)
    {
        $transaction->delete();
        return response()->json(null, 204);
    }
}
