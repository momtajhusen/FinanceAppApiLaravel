<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\Icon; 
use Illuminate\Support\Facades\DB;


class CategoryController extends Controller
{
    
public function index(Request $request)
{
    try {
        // Define the query based on user role
        if (Auth::user()->role === 'developer') {
            $categories = Category::where('categories.user_id', Auth::id());
        } else {
            $categories = Category::where('categories.user_id', Auth::id())
                ->orWhereIn('categories.user_id', function ($query) {
                    $query->select('id')->from('users')->where('role', 'developer');
                });
        }

$categories = $categories->leftJoin('transactions', function($join) {
        $join->on('categories.id', '=', 'transactions.category_id')
            ->where('transactions.user_id', Auth::id());
    })
    ->select('categories.*', DB::raw('COUNT(transactions.id) as transaction_count'))
    ->groupBy('categories.id', 'categories.user_id', 'categories.name', 'categories.type', 'categories.icon_id', 'categories.parent_id', 'categories.created_at', 'categories.updated_at')
    ->orderByRaw('transaction_count DESC') // First, order by transaction count in descending order
    ->orderBy('categories.parent_id', 'ASC') // Then, order by parent_id
    ->orderBy('categories.id', 'ASC') // Finally, order by id
    ->get();


        // Now, we will ensure that parent categories appear before their subcategories

        // Fetch icon paths for categories
        $iconIds = $categories->pluck('icon_id')->unique();
        $icons = Icon::whereIn('id', $iconIds)->pluck('path', 'id');

        // Fetch user roles
        $userIds = $categories->pluck('user_id')->unique();
        $userRoles = User::whereIn('id', $userIds)->pluck('role', 'id');

        // Sort categories such that parent categories come before their subcategories
        $sortedCategories = $categories->sortBy(function($category) {
            return $category->parent_id ? 1 : 0; // Sort: parents (0) first, subcategories (1) second
        })->values(); // Reset keys after sorting

        // Map icon paths and user roles to categories
        $sortedCategories->map(function ($category) use ($icons, $userRoles) {
            $category->icon_path = $icons->get($category->icon_id);
            $category->user_role = $userRoles->get($category->user_id);
            return $category;
        });

        // Return the sorted categories along with the transaction count
        return response()->json($sortedCategories);
    } catch (\Exception $e) {
        // Log the error for debugging
        Log::error('Failed to fetch categories', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        return response()->json([
            'error' => 'Failed to fetch categories. Please try again later.',
            'details' => $e->getMessage()
        ], 500);
    }
}






    public function store(Request $request)
    {
        // $validator = Validator::make($request->all(), [
        //     'name' => 'required|string|max:255',
        //     'type' => 'required|in:Income,Expense',
        //     'icon_id' => 'required|exists:icons,id', // Assuming 'icons' is the correct table name
        //     'parent_id' => 'nullable|exists:categories,id',
        // ]);
    
        // if ($validator->fails()) {
        //     return response()->json(['errors' => $validator->errors()], 422);
        // }
    
        try {
            $category = Category::create([
                'name' => $request->name,
                'type' => $request->type,
                'icon_id' => $request->icon_id,
                'parent_id' => $request->parent_id ?? null, // Set to null if not provided
                'user_id' => Auth::id(),
            ]);            
    
            return response()->json($category, 201);
        } catch (\Exception $e) {
            \Log::error('Error creating category: ' . $e->getMessage());
            return response()->json(['error' => 'Server Error'], 500);
        }
    }
    

    public function show($id)
    {
        $category = Category::with('icon', 'children')->findOrFail($id);

        // Check if the user is allowed to view this category
        if (Auth::user()->role === 'developer' || $category->user_id === Auth::id()) {
            return response()->json($category);
        } else {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    }


public function update(Request $request, $id)
{
    try {
        // Validate the incoming request
        // $request->validate([
        //     'name' => 'required|string',
        //     'type' => 'required|in:Income,Expense',
        //     'icon_id' => 'required|exists:icons,id',
        //     'parent_id' => 'nullable|exists:categories,id',
        // ]);

        $category = Category::findOrFail($id);

        // Check if the user is allowed to update this category
        if (Auth::user()->role === 'developer' || $category->user_id === Auth::id()) {
            // Update only the specified fields for security
            $category->update($request->only(['name', 'type', 'icon_id', 'parent_id']));

            // Log the successful update
            Log::info('Category updated successfully', [
                'category_id' => $category->id,
                'user_id' => Auth::id(),
                'updated_data' => $request->only(['name', 'type', 'icon_id', 'parent_id'])
            ]);

            return response()->json([
                'message' => 'Category updated successfully.',
                'data' => $category
            ], 200);
        } else {
            // Log the unauthorized access attempt
            Log::warning('Unauthorized category update attempt', [
                'category_id' => $id,
                'user_id' => Auth::id(),
                'attempted_data' => $request->all()
            ]);

            return response()->json(['error' => 'Unauthorized access. You are not allowed to update this category.'], 403);
        }
    } catch (\Illuminate\Validation\ValidationException $e) {
        // Log validation errors
        Log::error('Validation error during category update', [
            'errors' => $e->errors(),
            'user_id' => Auth::id(),
            'request_data' => $request->all()
        ]);

        return response()->json(['error' => 'Validation failed', 'details' => $e->errors()], 422);
    } catch (\Exception $e) {
        // Log unexpected errors
        Log::error('An error occurred during category update', [
            'exception_message' => $e->getMessage(),
            'user_id' => Auth::id(),
            'category_id' => $id,
            'request_data' => $request->all()
        ]);

        return response()->json(['error' => 'An unexpected error occurred. Please try again later.'], 500);
    }
}


public function destroy($id)
{
    try {
        // Retrieve the category or fail with a 404 response.
        $category = Category::findOrFail($id);

        // Step 1: Check for associated transactions
        $transactionCount = DB::table('transactions')->where('category_id', $id)->count();

        if ($transactionCount > 0) {
            // If there are transactions, return an error response
            return response()->json(['error' => 'Cannot delete category with existing transactions.'], 400);
        }

        // Step 2: Delete the category
        $category->delete();

        // Step 3: Return a 204 No Content response on successful deletion.
        return response()->json(['message' => 'Category deleted successfully.'], 204);

    } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
        // Handle unauthorized access with a 403 response.
        return response()->json(['error' => 'Unauthorized access.'], 403);
    } catch (\Exception $e) {
        // Handle any other exceptions with a 500 response.
        return response()->json(['error' => 'Something went wrong.'], 500);
    }
}


}
