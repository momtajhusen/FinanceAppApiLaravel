<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    /**
     * Display a listing of the budgets.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $budgets = Budget::with('user', 'category')->get();
        return response()->json($budgets);
    }

    /**
     * Store a newly created budget in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        $budget = Budget::create($request->all());
        return response()->json($budget, 201);
    }

    /**
     * Display the specified budget.
     *
     * @param \App\Models\Budget $budget
     * @return \Illuminate\Http\Response
     */
    public function show(Budget $budget)
    {
        return response()->json($budget->load('user', 'category'));
    }

    /**
     * Update the specified budget in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Budget $budget
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Budget $budget)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        $budget->update($request->all());
        return response()->json($budget);
    }

    /**
     * Remove the specified budget from storage.
     *
     * @param \App\Models\Budget $budget
     * @return \Illuminate\Http\Response
     */
    public function destroy(Budget $budget)
    {
        $budget->delete();
        return response()->json(null, 204);
    }
}
