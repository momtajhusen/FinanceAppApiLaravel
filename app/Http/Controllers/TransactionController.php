<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\Category;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class TransactionController extends Controller
{
    // GET /transactions
public function index(Request $request)
{
    try {
     // Get start_date and end_date from the request
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Ensure the end date is inclusive for the entire day
        if ($startDate && $endDate) {
            $endDate = date('Y-m-d H:i:s', strtotime($endDate . ' +1 day'));
        }

        // Fetch transactions for the authenticated user within the date range and order them by created_at in descending order
        $transactions = Transaction::where('user_id', Auth::id())
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('transaction_date', [$startDate, $endDate]);
            })
            ->orderBy('transaction_date', 'desc')
            ->get();
    
        $transactionsWithUsage = $transactions->map(function ($transaction) {
            $wallet = Wallet::find($transaction->wallet_id);
            $category = Category::find($transaction->category_id);
            $currency = Currency::find($transaction->currency);

            $transaction->transaction_category_name = $category->name;
            $transaction->transaction_type = $category->type;
            $transaction->wallets_icon = $wallet->icon->path ?? '';
            $transaction->wallets_name = $wallet->name;
            $transaction->wallet_id = $wallet->id;
            $transaction->categories_icon = $category->icon->path ?? '';
            $transaction->currency_symbols = $currency->currency_symbols;
            $transaction->currency_code = $currency->currency_code;

            return $transaction;
        });
    
        return response()->json($transactionsWithUsage);
    } catch (\Exception $e) {
        Log::error('Error fetching transactions: ' . $e->getMessage());
        return response()->json(['error' => 'Internal Server Error'], 500);
    }
}


    // GET /transactions-by-category
    public function fetchTransactionsByCategory(Request $request)
    {
        try {
         // Get start_date and end_date from the request
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            // Ensure the end date is inclusive for the entire day
            if ($startDate && $endDate) {
                $endDate = date('Y-m-d H:i:s', strtotime($endDate . ' +1 day'));
            }

            // Fetch transactions for the authenticated user within the date range and order them by created_at in descending order
            $transactions = Transaction::where('user_id', Auth::id())
                ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                    return $query->whereBetween('transaction_date', [$startDate, $endDate]);
                })
                ->orderBy('transaction_date', 'desc')
                ->get();
            
            
            
            $groupedTransactions = [];

            $transactions->each(function ($transaction) use (&$groupedTransactions) {
                $category = Category::find($transaction->category_id);
                $wallet = Wallet::find($transaction->wallet_id);
                $currency = Currency::find($transaction->currency);
                

                if ($category && $wallet) {
                    $walletIconPath = $wallet->icon->path ?? '';
                    $transaction->wallets_icon = $walletIconPath;
                    $transaction->wallets_name = $wallet->name;
                    $transaction->transaction_category_name = $category->name ?? 'Unknown';
                    $transaction->transaction_type = $category->type ?? 'Unknown';
                    $transaction->categories_icon = $category->icon->path ?? '';
                    $transaction->currency_symbols = $currency->currency_symbols;

                    if ($category->parent_id) {
                        $parentCategory = Category::find($category->parent_id);
                        if ($parentCategory) {
                            $groupId = $parentCategory->id;
                            if (!isset($groupedTransactions[$groupId])) {
                                $groupedTransactions[$groupId] = [
                                    'parent_category_name' => $parentCategory->name,
                                    'parent_icon' => $parentCategory->icon->path ?? '',
                                    'transactions' => [],
                                    'total_count' => 0,
                                    'total_amount' => 0
                                ];
                            }
                            $groupedTransactions[$groupId]['transactions'][] = $transaction;
                            $groupedTransactions[$groupId]['total_count']++;
                            $groupedTransactions[$groupId]['total_amount'] += $transaction->amount;
                        }
                    } else {
                        $groupId = $category->id;
                        if (!isset($groupedTransactions[$groupId])) {
                            $groupedTransactions[$groupId] = [
                                'parent_category_name' => $category->name,
                                'parent_icon' => $category->icon->path ?? '',
                                'transactions' => [],
                                'total_count' => 0,
                                'total_amount' => 0
                            ];
                        }
                        $groupedTransactions[$groupId]['transactions'][] = $transaction;
                        $groupedTransactions[$groupId]['total_count']++;
                        $groupedTransactions[$groupId]['total_amount'] += $transaction->amount;
                    }
                }
            });

            return response()->json(array_values($groupedTransactions));
        } catch (\Exception $e) {
            Log::error('Error fetching transactions: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }
    
public function fetchTransactionsIncomeExpenseData(Request $request)
{
    try {
        // Get the current year
        $currentYear = date('Y');

        // Fetch transactions for the current year with category type
        $transactions = Transaction::with('category') // Ensure category relationship is defined in the Transaction model
            ->where('user_id', Auth::id())
            ->whereYear('transaction_date', $currentYear)
            ->get();

        // Debug: Check fetched transactions
        if ($transactions->isEmpty()) {
            return response()->json([
                'income' => array_fill(0, 12, 0),
                'expense' => array_fill(0, 12, 0),
                'message' => 'No transactions found for the year ' . $currentYear
            ]);
        }

        // Initialize arrays to hold monthly income and expense totals
        $monthlyIncome = array_fill(0, 12, 0);
        $monthlyExpense = array_fill(0, 12, 0);

        // Loop through transactions to categorize them by month
        foreach ($transactions as $transaction) {
            // Ensure transaction_date is a Carbon instance
            $transactionDate = \Carbon\Carbon::parse($transaction->transaction_date);
            $month = (int) $transactionDate->format('n') - 1; // Get month index (0-11)
            
            // Get the category type (Income/Expense)
            $categoryType = $transaction->category ? $transaction->category->type : null;

            if ($categoryType === 'Income') {
                $monthlyIncome[$month] += $transaction->amount; // Add to income total for the month
            } else if ($categoryType === 'Expense') {
                $monthlyExpense[$month] += $transaction->amount; // Add to expense total for the month
            }
        }

        // Prepare the response data
        return response()->json([
            'income' => $monthlyIncome,
            'expense' => $monthlyExpense,
            'data_duration' => $currentYear,
            'message' => 'Data fetched successfully for the year ' . $currentYear
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error fetching income and expense data: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to fetch data. Please try again later.'], 500);
    }
}

    
public function topSpendingTransactions(Request $request)
{
    try {
        // Fetch expenses for the authenticated user
        // Fetch expenses for the authenticated user, filtering by category type 'Expense'
        $expenses = Transaction::where('user_id', Auth::id())
            ->whereHas('category', function ($query) {
                $query->where('type', 'Expense'); // Filter transactions by category type
            })
            ->get();

        // Initialize arrays to hold total expenses and categories
        $categoryTotals = [];
        $totalSpent = 0;

        // Calculate total expenses for each category (including parent categories)
        foreach ($expenses as $expense) {
            $categoryId = $expense->category_id;
            $totalSpent += $expense->amount;

            // Find the category and its parent
            $category = Category::find($categoryId);
            if ($category) {
                // Use parent_id for aggregation
                $parentId = $category->parent_id ? $category->parent_id : $categoryId;

                if (!isset($categoryTotals[$parentId])) {
                    $categoryTotals[$parentId] = [
                        'total_amount' => 0,
                        'category_name' => $category->parent_id ? Category::find($category->parent_id)->name : $category->name,
                        'icon_path' => $category->icon->path ?? '',
                        'transactions' => [],
                    ];
                }

                // Aggregate the amount for the parent category
                $categoryTotals[$parentId]['total_amount'] += $expense->amount;
                $categoryTotals[$parentId]['transactions'][] = $expense;
            }
        }

        // Prepare the results with percentages
        $result = [];
        foreach ($categoryTotals as $parentId => $data) {
            $percentage = $totalSpent > 0 ? number_format(($data['total_amount'] / $totalSpent) * 100, 2) : '0.00';

            $result[] = [
                'parent_id' => $parentId,
                'category_name' => $data['category_name'],
                'icon_path' => $data['icon_path'],
                'total_amount' => $data['total_amount'],
                'percentage' => $percentage,
            ];
        }

        // Sort the categories by total amount spent in descending order
        usort($result, function ($a, $b) {
            return $b['total_amount'] <=> $a['total_amount'];
        });

        // Return the top categories
        return response()->json($result);
    } catch (\Exception $e) {
        Log::error('Error fetching top spending transactions: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to fetch data. Please try again later.'], 500);
    }
}




    /**
     * Store a newly created transaction in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'wallet_id' => 'required|exists:wallets,id',
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric|min:0', // Ensure the amount is non-negative
            'note' => 'nullable|string',
            'transaction_date' => 'required|date',
            'attachment_url' => 'nullable|url',
        ]);
    
        // Check for validation failures
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
    
        // Get wallet and category data
        $wallet = Wallet::find($request->wallet_id);
        $category = Category::find($request->category_id);
    
        // Check if the wallet exists
        if (!$wallet) {
            return response()->json(['error' => 'Wallet not found'], 404);
        }
    
        // Check if the category exists
        if (!$category) {
            return response()->json(['error' => 'Category not found'], 404);
        }
    
        // Convert to float for comparison
        $walletAmount = (float)$wallet->balance;
        $requestAmount = (float)$request->amount;
        

        // Format the transaction_date
        $formattedDate = date('Y-m-d H:i:s', strtotime($request->transaction_date));
    
        // Override currency and transaction_type
        $request->merge([
            'currency' => $wallet->currency,
            'transaction_type' => $category->type,
            'transaction_date' => $formattedDate,
        ]);
    
        // Start a database transaction
        DB::beginTransaction();
    
        try {
            // Check if the wallet has sufficient balance for Expense transactions
            if ($category->type === 'Expense' && $walletAmount < $requestAmount) {
                DB::rollBack();
                return response()->json(['error' => 'Insufficient balance in your ' . $wallet->name . ' wallet'], 400);
            }
    
            // Create a new transaction record
            $transaction = Transaction::create($request->all());
    
            // Update the wallet balance based on transaction type
            if ($category->type === 'Expense') {
                $wallet->balance -= $request->amount;
            } else if ($category->type === 'Income') {
                $wallet->balance += $request->amount;
            }
    
            // Save the updated wallet balance
            $wallet->save();
    
            // Commit the transaction
            DB::commit();
    
            // Return the created transaction
            return response()->json($transaction, 201);
        } catch (\Exception $e) {
            // Rollback the transaction in case of error
            DB::rollBack();
            Log::error('Transaction failed: ' . $e->getMessage());
            return response()->json(['error' => 'Transaction failed'], 500);
        }
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
    Log::info('Updating transaction: ', [
        'transaction_id' => $transaction->id,
        'request_data' => $request->all()
    ]);

    // Validate the request
    $validator = Validator::make($request->all(), [
        'wallet_id' => 'required|exists:wallets,id',
        'category_id' => 'required|exists:categories,id',
        'amount' => 'required|numeric|min:0', // Ensure the amount is non-negative
        'note' => 'nullable|string',
        'transaction_date' => 'required|date',
        'attachment_url' => 'nullable|url',
    ]);

    // Check for validation failures
    if ($validator->fails()) {
        return response()->json($validator->errors(), 422);
    }

    // Get the current wallet data
    $oldWallet = $transaction->wallet;

    // Get the new wallet and category data
    $newWallet = Wallet::find($request->wallet_id);
    $newCategory = Category::find($request->category_id);

    // Check if the new wallet and category exist
    if (!$newWallet) {
        return response()->json(['error' => 'Wallet not found'], 404);
    }

    if (!$newCategory) {
        return response()->json(['error' => 'Category not found'], 404);
    }

    // Get the old category ID from the transaction
    $oldCategoryId = $transaction->category_id;
    $oldCategory = Category::find($oldCategoryId); // Fetch the old category

    // Start a database transaction
    DB::beginTransaction();

    try {
        // Adjust the wallet balances based on the changes
        if ($oldWallet->id !== $newWallet->id) {
            // Handling case when wallet changes
            if ($oldCategory->type === 'Expense') {
                $oldWallet->balance += $transaction->amount; // Re-add the amount if it was an expense
            } else if ($oldCategory->type === 'Income') {
                $oldWallet->balance -= $transaction->amount; // Deduct the amount if it was income
            }

            // Adjust new wallet balance based on new category
            if ($newCategory->type === 'Expense') {
                $newWallet->balance -= $request->amount; // Deduct for the new expense
                if ($newWallet->balance < 0) {
                    DB::rollBack();
                    return response()->json(['error' => 'Insufficient balance in your ' . $newWallet->name . ' wallet'], 400);
                }
            } else if ($newCategory->type === 'Income') {
                $newWallet->balance += $request->amount; // Add for the new income
            }
        } else {
            // If wallet ID hasn't changed, adjust balances based on the amount and category changes
            if ($oldCategoryId !== $request->category_id || $request->amount !== $transaction->amount) {
                // Handle changes in category type and amount
                if ($oldCategory->type === 'Expense') {
                    $oldWallet->balance += $transaction->amount; // Re-add if it was an expense
                } else if ($oldCategory->type === 'Income') {
                    $oldWallet->balance -= $transaction->amount; // Deduct if it was an income
                }

                // Now apply the new category changes
                if ($newCategory->type === 'Expense') {
                    $oldWallet->balance -= $request->amount; // Deduct the new expense amount
                } else if ($newCategory->type === 'Income') {
                    $oldWallet->balance += $request->amount; // Add the new income amount
                }

                // Check for sufficient balance after changes
                if ($oldWallet->balance < 0) {
                    DB::rollBack();
                    return response()->json(['error' => 'Insufficient balance in your ' . $oldWallet->name . ' wallet'], 400);
                }
            }
        }

        // Override currency with the new wallet's currency
        $request->merge([
            'currency' => $newWallet->currency,
        ]);

        // Update the transaction with the new data including currency
        $transaction->update($request->all());

        // Save the updated wallets
        $oldWallet->save();
        $newWallet->save();

        // Commit the transaction
        DB::commit();

        // Return the updated transaction
        return response()->json($transaction, 200);
    } catch (\Exception $e) {
        // Rollback the transaction in case of error
        DB::rollBack();
        Log::error('Transaction update failed: ' . $e->getMessage());
        return response()->json(['error' => 'Transaction update failed'], 500);
    }
}


    /**
     * Remove the specified transaction from storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Transaction $transaction
     * @return \Illuminate\Http\Response
     */
    
    
    public function destroy($id)
    {
        // Start a database transaction
        DB::beginTransaction();

        try {
            // Get the transaction, category, and wallet
            $transaction = Transaction::findOrFail($id);
            $category = Category::find($transaction->category_id);
            $wallet = Wallet::find($transaction->wallet_id);

            // Check if the category and wallet exist
            if (!$category) {
                DB::rollBack();
                return response()->json(['error' => 'Category not found'], 404);
            }
            if (!$wallet) {
                DB::rollBack();
                return response()->json(['error' => 'Wallet not found'], 404);
            }

            // Get the amount from the transaction
            $amount = $transaction->amount;

            // Update the wallet balance based on transaction type
            if ($category->type === 'Expense') {
                $wallet->balance += $amount; // Add the amount back to the wallet
            } else if ($category->type === 'Income') {
                $wallet->balance -= $amount; // Subtract the amount from the wallet
            }

            // Save the updated wallet balance
            $wallet->save();

            // Delete the transaction
            $transaction->delete();

            // Commit the transaction
            DB::commit();

            // Return success response
            return response()->json(['message' => 'Transaction deleted successfully']);
        } catch (\Exception $e) {
            // Rollback the transaction in case of error
            DB::rollBack();
            Log::error('Transaction deletion failed: ' . $e->getMessage());
            return response()->json(['error' => 'Transaction deletion failed'], 500);
        }
    }



}
