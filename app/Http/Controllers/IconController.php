<?php

namespace App\Http\Controllers;

use App\Models\Icon;
use Illuminate\Http\Request;

class IconController extends Controller
{
    // GET /icons
    public function index()
    {
        $icons = Icon::all();
        return response()->json($icons);
    }

    // GET /icons/{id}
    public function show($id)
    {
        $icon = Icon::findOrFail($id);
        return response()->json($icon);
    }

    // POST /icons
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'type' => 'required|in:Wallet,Categories',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        // Handle the image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('icons', 'public');
        }
    
        $icon = Icon::create([
            'name' => $request->name,
            'type' => $request->type,
            'path' => $imagePath,
        ]);
    
        return response()->json($icon, 201);
    }
    
    

    // PUT /icons/{id}
    public function update(Request $request, $id)
    {
        // Find the icon and validate
        $icon = Icon::findOrFail($id);
        $request->validate([
            'name' => 'required|string',
            'type' => 'required|in:Wallet,Categories',
            'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle image upload and saving logic if a new image is provided
        // ...

        $icon->update([
            'name' => $request->name,
            'type' => $request->type,
            'path' => $imagePath ?? $icon->path, // Update if a new image was uploaded
        ]);

        return response()->json($icon);
    }

    // DELETE /icons/{id}
    public function destroy($id)
    {
        $icon = Icon::findOrFail($id);
        $icon->delete();
        return response()->json(null, 204);
    }
}
