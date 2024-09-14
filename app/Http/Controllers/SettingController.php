<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Display a listing of the settings.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $settings = Setting::all();
        return response()->json($settings);
    }

    /**
     * Store a newly created setting in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'default_currency' => 'required|string|max:3',
            'notification_preferences' => 'nullable|array',
            'language' => 'required|string|max:10',
        ]);

        $setting = Setting::create($request->all());
        return response()->json($setting, 201);
    }

    /**
     * Display the specified setting.
     *
     * @param \App\Models\Setting $setting
     * @return \Illuminate\Http\Response
     */
    public function show(Setting $setting)
    {
        return response()->json($setting);
    }

    /**
     * Update the specified setting in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Setting $setting
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Setting $setting)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'default_currency' => 'required|string|max:3',
            'notification_preferences' => 'nullable|array',
            'language' => 'required|string|max:10',
        ]);

        $setting->update($request->all());
        return response()->json($setting);
    }

    /**
     * Remove the specified setting from storage.
     *
     * @param \App\Models\Setting $setting
     * @return \Illuminate\Http\Response
     */
    public function destroy(Setting $setting)
    {
        $setting->delete();
        return response()->json(null, 204);
    }
}
