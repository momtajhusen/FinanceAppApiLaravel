<?php

namespace App\Http\Controllers;

use App\Models\Icon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;


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
        try {
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
            } else {
                return response()->json(['error' => 'Image upload failed.'], 400);
            }
        
            $icon = Icon::create([
                'name' => $request->name,
                'type' => $request->type,
                'path' => $imagePath,
            ]);
        
            return response()->json($icon, 201);
        } catch (\Exception $e) {
            // Log the exception
            \Log::error('Failed to create icon: ' . $e->getMessage());
    
            // Return a more specific error message
            return response()->json(['error' => 'An unexpected error occurred. Please try again later.'], 500);
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

    public function destroy($id)
    {
        // Validate that $id is numeric
        if (!is_numeric($id)) {
            \Log::warning("Invalid icon ID: {$id}");
            return response()->json(['error' => 'Invalid ID provided.'], 400);
        }
    
        try {
            $icon = Icon::findOrFail($id);
    
            // Additional validation (if needed) can go here before deletion
    
            $icon->delete();
            \Log::info("Icon deleted successfully: ID {$id}");
            return response()->json(null, 204);
    
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error("Icon not found: ID {$id}");
            return response()->json(['error' => 'Icon not found.'], 404);
        } catch (\Exception $e) {
            \Log::error("Error deleting icon: " . $e->getMessage());
            return response()->json(['error' => 'Could not delete the icon.'], 500);
        }
    }

}
