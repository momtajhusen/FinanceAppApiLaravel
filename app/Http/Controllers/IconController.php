<?php

namespace App\Http\Controllers;

use App\Models\Icon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


class IconController extends Controller
{
    // GET /icons
    public function index()
    {
        // Get all icons
        $icons = Icon::all();
    
        // Map through each icon and attach the count of wallets and categories using it
        $iconsWithUsage = $icons->map(function ($icon) {
            // Count how many wallets and categories are using the icon
            $walletCount = DB::table('wallets')->where('icon_id', $icon->id)->count();
            $categoryCount = DB::table('categories')->where('icon_id', $icon->id)->count();
    
            // Store the counts directly as properties of the $icon object
            $icon->wallets_in_use = $walletCount;
            $icon->categories_in_use = $categoryCount;
    
            return $icon; // Return the modified icon object
        });
    
        return response()->json($iconsWithUsage);
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
                'type' => 'required|in:Wallet,Categories,Other',
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
            'type' => 'required|in:Wallet,Categories,Other',
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
    
            // Check if the icon is used in any wallets or categories
            $walletsUsingIcon = \DB::table('wallets')
                ->where('icon_id', $id)
                ->pluck('name'); // Get the names of the wallets using this icon
    
            $categoriesUsingIcon = \DB::table('categories')
                ->where('icon_id', $id)
                ->pluck('name'); // Get the names of the categories using this icon
    
            if ($walletsUsingIcon->isNotEmpty() || $categoriesUsingIcon->isNotEmpty()) {
                $message = "This icon cannot be deleted because it is being used in the following:\n";
                
                if ($walletsUsingIcon->isNotEmpty()) {
                    $message .= "Wallets: " . implode(', ', $walletsUsingIcon->toArray()) . ".\n";
                }
    
                if ($categoriesUsingIcon->isNotEmpty()) {
                    $message .= "Categories: " . implode(', ', $categoriesUsingIcon->toArray()) . ".\n";
                }
    
                // Adding step-by-step instructions with specific wallet and category names
                $message .= "\nTo delete this icon, follow these steps:\n";
                
                if ($walletsUsingIcon->isNotEmpty()) {
                    $message .= "1. Update the following wallets to use a different icon: " . implode(', ', $walletsUsingIcon->toArray()) . ".\n";
                }
                
                if ($categoriesUsingIcon->isNotEmpty()) {
                    $message .= "2. Update the following categories to use a different icon: " . implode(', ', $categoriesUsingIcon->toArray()) . ".\n";
                }
    
                $message .= "3. Once no wallets or categories are using this icon, try deleting it again.\n";
    
                \Log::info("Icon ID {$id} is in use and cannot be deleted.");
                return response()->json(['error' => $message], 400);
            }
    
            // If icon is not used, proceed to delete
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
