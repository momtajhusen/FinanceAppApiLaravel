<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CurrencyController extends Controller
{
    // Display a listing of the currencies
    public function index()
    {
        $currencies = Currency::all();
        return response()->json([
            'success' => true,
            'message' => 'Currencies fetched successfully.',
            'data' => $currencies
        ], 200);
    }

    // Store a newly created currency
    // app/Http/Controllers/CurrencyController.php
    public function store(Request $request)
    {
        // Validation rules
        $validator = Validator::make($request->all(), [
            'flag' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // validate image
            'currency_code' => 'required|string|max:3|unique:currencies,currency_code',
            'currency_name' => 'required|string',
        ]);

        // If validation fails
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error.',
                'errors' => $validator->errors()
            ], 422);
        }

        // Handle the flag (image) upload
        if ($request->hasFile('flag')) {
            // Store the image in the 'flags' folder within the 'public' disk
            $flagPath = $request->file('flag')->store('flags', 'public');
        } else {
            // Default to null if no image is uploaded
            $flagPath = null;
        }

        // Create the new currency
        $currency = Currency::create([
            'flag' => $flagPath,
            'currency_code' => $request->currency_code,
            'currency_name' => $request->currency_name,
            'exchange_rate_to_base' => $request->exchange_rate_to_base
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Currency created successfully.',
            'data' => $currency
        ], 201);
    }


    // Display the specified currency
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

    // Update the specified currency
    public function update(Request $request, $id)
    {
        $currency = Currency::find($id);

        if (!$currency) {
            return response()->json([
                'success' => false,
                'message' => 'Currency not found.'
            ], 404);
        }

        // Validation rules for update
        $validator = Validator::make($request->all(), [
            'flag' => 'nullable|string',
            'currency_code' => 'required|string|max:3|unique:currencies,currency_code,' . $id,
            'currency_name' => 'required|string',
            'exchange_rate_to_base' => 'required|numeric|between:0,9999999999.999999'
        ]);

        // If validation fails
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error.',
                'errors' => $validator->errors()
            ], 422);
        }

        // Update the currency
        $currency->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Currency updated successfully.',
            'data' => $currency
        ], 200);
    }

    // Remove the specified currency
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
