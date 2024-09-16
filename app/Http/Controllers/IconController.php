<?php

namespace App\Http\Controllers;

use App\Models\Icon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


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
            'name' => 'required|string|max:255',
            'type' => 'required|in:Income,Expense',
            'icon_id' => 'required|exists:icons,id',
            'parent_id' => 'nullable|exists:categories,id',
        ], [
            'name.required' => 'The category name is required.',
            'name.string' => 'The category name must be a string.',
            'name.max' => 'The category name may not be greater than 255 characters.',
            'type.required' => 'The category type is required.',
            'type.in' => 'The category type must be either Income or Expense.',
            'icon_id.required' => 'The icon ID is required.',
            'icon_id.exists' => 'The selected icon ID does not exist.',
            'parent_id.exists' => 'The selected parent category does not exist.',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        try {
            $category = Category::create([
                'name' => $request->name,
                'type' => $request->type,
                'icon_id' => $request->icon_id,
                'parent_id' => $request->parent_id,
                'user_id' => Auth::id(),
            ]);
    
            return response()->json($category, 201);
        } catch (\Exception $e) {
            \Log::error('Error creating category: ' . $e->getMessage());
            return response()->json(['error' => 'Server Error'], 500);
        }
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
