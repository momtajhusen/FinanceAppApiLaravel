<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class GoalController extends Controller
{
    /**
     * Display a listing of the goals.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $goals = Goal::all();
        return response()->json($goals);
    }

    /**
     * Store a newly created goal in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'target_amount' => 'required|numeric',
            'current_savings' => 'nullable|numeric',
            'deadline' => 'nullable|date',
        ]);

        $goal = Goal::create($request->all());
        return response()->json($goal, 201);
    }

    /**
     * Display the specified goal.
     *
     * @param \App\Models\Goal $goal
     * @return \Illuminate\Http\Response
     */
    public function show(Goal $goal)
    {
        return response()->json($goal);
    }

    /**
     * Update the specified goal in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Goal $goal
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Goal $goal)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'target_amount' => 'required|numeric',
            'current_savings' => 'nullable|numeric',
            'deadline' => 'nullable|date',
        ]);

        $goal->update($request->all());
        return response()->json($goal);
    }

    /**
     * Remove the specified goal from storage.
     *
     * @param \App\Models\Goal $goal
     * @return \Illuminate\Http\Response
     */
    public function destroy(Goal $goal)
    {
        $goal->delete();
        return response()->json(null, 204);
    }
}
