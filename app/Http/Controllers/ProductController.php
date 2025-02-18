<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use ErrorException;
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
    public function update(Request $request, Product $product)
    {
        try {

            if (isset($request['regular_price'])) {
                // Osiguravanje dvije decimale, jer Laravel makne poslednju nulu
                $request['regular_price'] = number_format($request['regular_price'], 2);
            }

            if (isset($request['sale_price'])) {
                $request['sale_price'] = number_format($request['sale_price'], 2);
            }

            // Validacija
            $data = $request->validate([
                'sku' => 'string|min:7|max:7',
                'regular_price' => 'numeric|min:1.00|regex:/^[1-9][0-9]{0,3}\.[0-9]{2}$/', // ne pocinje nulom
                'sale_price' => 'numeric|min:1.00|regex:/^[1-9][0-9]{0,3}\.[0-9]{2}$/',
                'description' => 'string|min:50|max:500'
            ]);

            if (isset($data['regular_price'])) {
                $data['regular_price'] = $data['regular_price'] * 100; // cijene su tipa integer u bazi podataka
            }

            if (isset($data['sale_price'])) {
                $data['sale_price'] = $data['sale_price'] * 100;
            }

            $product->update($data); // azuriranje

            $updated_product = Product::findOrFail($product->id);
            // Formatiranje cijena iz bp za prikaz
            $updated_product->regular_price = formatirajCijenu($updated_product->regular_price);
            $updated_product->sale_price = formatirajCijenu($updated_product->sale_price);

        } catch (ErrorException $e) {
            return response()->json(["greška" => $e->getMessage()]);
        } catch (Throwable $throwable) {
            return response()->json(["greška" => $throwable->getMessage()]);
        }

        return response()->json($updated_product); // uspijeh
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        // Brisanje proizvoda
        try {
            $product->delete();
        } catch (Throwable $e) {
            return response()->json(['greška' => 'Neuspiješno brisanje.'], 500);
        }

        return response(null, 202); // uspijeh
    }
}
