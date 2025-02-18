<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Throwable;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Prikaz podataka tabele products
        $products = Product::get();

        foreach ($products as $product) {
            $product->regular_price = formatirajCijenu($product->regular_price);
            $product->sale_price = formatirajCijenu($product->sale_price);
        }

        return response()->json($products);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Product $product)
    {
        // Prikaz svih proizvoda određene kategorije
        try {
            $category = $request['category'];

            if (!$category) {
                return response()->json(["Loš zahtijev" => "Parametar je obavezan."], 400); // 400 => Bad Request
            }

            // Proizvod specificne kategorije, sa relacijama
            $products = Product::whereHas('categories', function ($query) use ($category) {
                $query->where('categories.id', $category);
            })
                ->with(['manufacturer', 'categories.departments'])
                ->get();

            foreach ($products as $product) {
                $product->regular_price = formatirajCijenu($product->regular_price);
                $product->sale_price = formatirajCijenu($product->sale_price);
            }
        } catch (Throwable $e) { // generalni Exception
            return response()->json(["greška" => "Neuspiješno."], 500);
        }

        return response()->json($products); // uspijeh

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Brisanje proizvoda
    }
}
