<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;


class CategoryController extends Controller
{
    public function index()
    {
        // Check if the authenticated user is a developer or not
        if (Auth::user()->isDeveloper()) {
            // Developers can see all categories
            $categories = Category::with('icon', 'children')->get();
        } else {
            // Regular users can only see their own categories
            $categories = Category::where('user_id', Auth::id())->with('icon', 'children')->get();
        }

        return response()->json($categories);
    }

    public function store(Request $request)
    {
        try {
            // $request->validate([
            //     'name' => 'required|string|max:255',
            //     'type' => 'required|in:Income,Expense',
            //     'icon_id' => 'required|exists:icons,id',
            //     'parent_id' => 'nullable|exists:categories,id',
            // ]);
    
            $category = Category::create([
                'name' => $request->name,
                'type' => $request->type,
                'icon_id' => $request->icon_id,
                'parent_id' => $request->parent_id,
                'user_id' => Auth::id(),
            ]);
    
            return response()->json($category, 201);
        } catch (\Exception $e) {
            // Log the exception message for debugging
            \Log::error('Error creating category: ' . $e->getMessage());
            return response()->json(['error' => 'Server Error'], 500);
        }
    }
    

    public function show($id)
    {
        $category = Category::with('icon', 'children')->findOrFail($id);

        // Check if the user is allowed to view this category
        if (Auth::user()->isDeveloper() || $category->user_id === Auth::id()) {
            return response()->json($category);
        } else {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:Income,Expense',
            'icon_id' => 'required|exists:icons,id',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        $category = Category::findOrFail($id);

        // Check if the user is allowed to update this category
        if (Auth::user()->isDeveloper() || $category->user_id === Auth::id()) {
            $category->update($request->all());
            return response()->json($category);
        } else {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        // Check if the user is allowed to delete this category
        if (Auth::user()->isDeveloper() || $category->user_id === Auth::id()) {
            $category->delete();
            return response()->json(null, 204);
        } else {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    }
}
