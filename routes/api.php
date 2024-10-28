<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\GoalController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\IconController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\GoogleAuthController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Google OAuth routes
Route::get('/auth/google', [GoogleAuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback']);


// Protected routes (authentication required)
Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/verify-token', [AuthController::class, 'verifyToken']);

    // API resources for managing data
    Route::apiResource('users', UserController::class);
    Route::apiResource('transactions', TransactionController::class);
    Route::get('/transactions-category', [TransactionController::class, 'fetchTransactionsByCategory']);

    Route::apiResource('wallets', WalletController::class);
    Route::apiResource('budgets', BudgetController::class);
    Route::apiResource('currencies', CurrencyController::class);
    Route::apiResource('goals', GoalController::class);
    Route::apiResource('settings', SettingController::class);
    Route::apiResource('icons', IconController::class);
    Route::apiResource('categories', CategoryController::class);

    // Dashboard Data APIs
    Route::get('/transactions-income-expense-data', [TransactionController::class, 'fetchTransactionsIncomeExpenseData']);
    Route::get('/top-spending-transactions', [TransactionController::class, 'topSpendingTransactions']);
});
