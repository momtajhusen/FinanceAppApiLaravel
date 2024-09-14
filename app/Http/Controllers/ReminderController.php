<?php

namespace App\Http\Controllers;

use App\Models\Reminder;
use Illuminate\Http\Request;

class ReminderController extends Controller
{
    /**
     * Display a listing of the reminders.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $reminders = Reminder::all();
        return response()->json($reminders);
    }

    /**
     * Store a newly created reminder in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'bill_id' => 'required|exists:bills,id',
            'reminder_date' => 'required|date',
            'message' => 'nullable|string',
        ]);

        $reminder = Reminder::create($request->all());
        return response()->json($reminder, 201);
    }

    /**
     * Display the specified reminder.
     *
     * @param \App\Models\Reminder $reminder
     * @return \Illuminate\Http\Response
     */
    public function show(Reminder $reminder)
    {
        return response()->json($reminder);
    }

    /**
     * Update the specified reminder in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Reminder $reminder
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Reminder $reminder)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'bill_id' => 'required|exists:bills,id',
            'reminder_date' => 'required|date',
            'message' => 'nullable|string',
        ]);

        $reminder->update($request->all());
        return response()->json($reminder);
    }

    /**
     * Remove the specified reminder from storage.
     *
     * @param \App\Models\Reminder $reminder
     * @return \Illuminate\Http\Response
     */
    public function destroy(Reminder $reminder)
    {
        $reminder->delete();
        return response()->json(null, 204);
    }
}
