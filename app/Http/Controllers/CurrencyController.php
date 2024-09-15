<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;



class CurrencyController extends Controller
{
    /**
     * Display a listing of the currencies.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $currencies = Currency::all();
        return response()->json($currencies);
    }

    /**
     * Store a newly created currency in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'currency_code' => 'required|string|size:3|unique:currencies,currency_code',
            'currency_name' => 'required|string',
            'exchange_rate_to_base' => 'required|numeric',
        ]);

        $currency = Currency::create($request->all());
        return response()->json($currency, 201);
    }

    /**
     * Display the specified currency.
     *
     * @param \App\Models\Currency $currency
     * @return \Illuminate\Http\Response
     */
    public function show(Currency $currency)
    {
        return response()->json($currency);
    }

    /**
     * Update the specified currency in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Currency $currency
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Currency $currency)
    {
        $request->validate([
            'currency_code' => 'required|string|size:3|unique:currencies,currency_code,' . $currency->id,
            'currency_name' => 'required|string',
            'exchange_rate_to_base' => 'required|numeric',
        ]);

        $currency->update($request->all());
        return response()->json($currency);
    }

    /**
     * Remove the specified currency from storage.
     *
     * @param \App\Models\Currency $currency
     * @return \Illuminate\Http\Response
     */
    public function destroy(Currency $currency)
    {
        $currency->delete();
        return response()->json(null, 204);
    }
}
