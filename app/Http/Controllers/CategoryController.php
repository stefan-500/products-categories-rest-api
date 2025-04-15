<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        // Prikaz kategorija
        $categories = Category::with('departments')->get();
        return response()->json($categories);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category): JsonResponse
    {
        // Validacija
        $data = $request->validate([
            'name' => 'required|min:5|max:50'
        ]);

        // Azuriranje
        $category->update([
            'name' => $data['name']
        ]);

        return response()->json($category); // uspijeh
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category): Response
    {
        try {
            $category->delete();
        } catch (Throwable $e) {
            return response()->json(['greška' => 'Neuspiješno brisanje.'], 500);
        }

        return response(null, 202); // uspijeh
    }
}
