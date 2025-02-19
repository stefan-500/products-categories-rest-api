<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Carbon\Carbon;
use ErrorException;
use Illuminate\Http\Request;
use Str;
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
            return response()->json(["error" => $e->getMessage()]);
        } catch (Throwable $throwable) {
            return response()->json(["error" => $throwable->getMessage()]);
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
            return response()->json(['error' => 'Neuspiješno brisanje.'], 500);
        }

        return response(null, 202); // uspijeh
    }

    public function generateCategoryProductsCSV(Request $request)
    {
        // Validacija
        $validated = $request->validate([
            'id' => 'required|integer'
        ]);
        $category_id = $validated['id'];

        // Proizvodi kategorije po POST parametru id
        $products = Product::whereHas('categories', function ($query) use ($category_id) {
            $query->where('categories.id', $category_id);
        })
            ->with(['manufacturer', 'categories.departments'])
            ->get();

        // Kategorija po POST parametru id
        $category = Category::findOrFail($category_id);
        $category_name = $category->name;

        // Kreiranje naziva fajla
        $category_name = Str::lower($category->name); // pretvaranje slova u lowercase
        // Zamjena ne-alfanumerickih karaktera razmakom
        $alnum_category_name = Str::replaceMatches('/[^A-Za-z0-9]/', ' ', $category_name);
        $formatted_category_name = Str::deduplicate($alnum_category_name); // uklanjanje suvisnih razmaka
        $category_name = Str::replaceMatches('/ /', '_', $formatted_category_name); // zamjena razmaka sa _

        $date_time = Carbon::now();
        $year = $date_time->get('year');
        $month = $date_time->get('month');
        $day = $date_time->get('day');
        $hour = $date_time->get('hour');
        $minute = $date_time->get('minute');

        $folder_path = storage_path('app\\csv\\'); // folder za CSV fajl
        $filename = $category_name . "_" . $year . "_" . $month . "_" . $day . "-" . $hour . "_" . $minute . ".csv";
        $file_path = $folder_path . $filename;

        $file = fopen($file_path, 'w'); // kreira fajl ako ne postoji
        $columns = array('product_number', 'category_name', 'department_name', 'manufacturer_name', 'upc', 'sku', 'regular_price', 'sale_price', 'description');
        fputcsv($file, $columns); // dodaje nazive kolona u CSV fajl

        // Uzima polje name iz niza, ako postoji vise department-a dodaje ;
        $category_departments = $category->departments->pluck('name')->implode('; ');

        // Dodavanje podataka o proizvodima u CSV fajl
        foreach ($products as $product) {
            $product->regular_price = formatirajCijenu($product->regular_price);
            $product->sale_price = formatirajCijenu($product->sale_price);
            fputcsv(
                $file,
                [
                    $product->product_number,
                    $category->name,
                    $category_departments,
                    $product->manufacturer->name,
                    $product->upc,
                    $product->sku,
                    $product->regular_price,
                    $product->sale_price,
                    $product->description,
                ]
            );
        }
        fclose($file);

        // Uspijeh
        return response()->json([
            "success" => true,
            "message" => "Uspiješno sačuvani podaci u CSV fajlu",
            "file" => [
                "path" => $file_path
            ]
        ]);
    }
}
