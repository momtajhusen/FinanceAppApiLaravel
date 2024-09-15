<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;


class CurrencyController extends Controller
{
    public function index()
    {
        $currencies = Currency::all();
        return response()->json([
            'success' => true,
            'message' => 'Currencies fetched successfully.',
            'data' => $currencies
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'flag' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'currency_code' => 'required|string|max:3|unique:currencies',
            'currency_name' => 'required|string|unique:currencies',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error.',
                'errors' => $validator->errors()
            ], 422);
        }
    
        // Check if the file is received properly
        if ($request->hasFile('flag')) {
            // Log to check file details
            \Log::info('File details', [$request->file('flag')]);
    
            // Handle the flag (image) upload
            $flagPath = $request->file('flag')->store('flags', 'public');
        } else {
            return response()->json([
                'success' => false,
                'message' => 'File not received.',
            ], 400);
        }
    
        // Create the new currency
        $currency = Currency::create([
            'flag' => $flagPath,
            'currency_code' => $request->currency_code,
            'currency_name' => $request->currency_name,
        ]);
    
        return response()->json([
            'success' => true,
            'message' => 'Currency created successfully.',
            'data' => $currency
        ], 201);
    }
    

    public function show($id)
    {
        $currency = Currency::find($id);

        if (!$currency) {
            return response()->json([
                'success' => false,
                'message' => 'Currency not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Currency fetched successfully.',
            'data' => $currency
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $currency = Currency::find($id);

        if (!$currency) {
            return response()->json([
                'success' => false,
                'message' => 'Currency not found.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'flag' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'currency_code' => 'required|string|max:3|unique:currencies,currency_code,' . $id,
            'currency_name' => 'required|string',
            'exchange_rate_to_base' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error.',
                'errors' => $validator->errors()
            ], 422);
        }

        if ($request->hasFile('flag')) {
            $flagPath = $request->file('flag')->store('flags', 'public');
            $currency->flag = $flagPath;
        }

        $currency->currency_code = $request->currency_code;
        $currency->currency_name = $request->currency_name;
        $currency->exchange_rate_to_base = $request->exchange_rate_to_base;

        $currency->save();

        return response()->json([
            'success' => true,
            'message' => 'Currency updated successfully.',
            'data' => $currency
        ], 200);
    }

    public function destroy($id)
    {
        $currency = Currency::find($id);

        if (!$currency) {
            return response()->json([
                'success' => false,
                'message' => 'Currency not found.'
            ], 404);
        }

        $currency->delete();

        return response()->json([
            'success' => true,
            'message' => 'Currency deleted successfully.'
        ], 200);
    }
}
