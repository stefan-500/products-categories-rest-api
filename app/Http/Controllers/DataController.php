<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;

class DataController extends Controller
{
    public function index()
    {
        // Prikaz podataka o proizvodima

        $products = Product::with(['manufacturer', 'categories.departments'])
            ->get();

        // Formatiranje cijena
        foreach ($products as $product) {
            $product->regular_price = formatirajCijenu($product->regular_price);
            $product->sale_price = formatirajCijenu($product->sale_price);
        }

        return response()->json($products);

    }
}
