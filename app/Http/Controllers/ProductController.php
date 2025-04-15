<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Carbon\Carbon;
use ErrorException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
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
    public function show(Request $request, Product $product): JsonResponse
    {
        // Prikaz svih proizvoda određene kategorije
        try {
            $category = $request['category'];

            if (!$category) {
                return response()->json(["Loš zahtijev" => "Parametar je obavezan."], 400); // 400 => Bad Request
            }

            // Proizvod specificne kategorije, sa relacijama
            $products = Product::whereHas('categories', function ($query) use ($category): void {
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
    public function update(Request $request, Product $product): JsonResponse
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

            $updatedProduct = Product::findOrFail($product->id);
            // Formatiranje cijena iz bp za prikaz
            $updatedProduct->regular_price = formatirajCijenu($updatedProduct->regular_price);
            $updatedProduct->sale_price = formatirajCijenu($updatedProduct->sale_price);

        } catch (ErrorException $e) {
            return response()->json(["error" => $e->getMessage()]);
        } catch (Throwable $throwable) {
            return response()->json(["error" => $throwable->getMessage()]);
        }

        return response()->json($updatedProduct); // uspijeh
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product): Response
    {
        // Brisanje proizvoda
        try {
            $product->delete();
        } catch (Throwable $e) {
            return response()->json(['error' => 'Neuspiješno brisanje.'], 500);
        }

        return response(null, 202); // uspijeh
    }

    public function generateCategoryProductsCSV(Request $request): JsonResponse
    {
        // Validacija
        $validated = $request->validate([
            'id' => 'required|integer'
        ]);
        $categoryID = $validated['id'];

        // Proizvodi kategorije po POST parametru id
        $products = Product::whereHas('categories', function ($query) use ($categoryID): void {
            $query->where('categories.id', $categoryID);
        })
            ->with(['manufacturer', 'categories.departments'])
            ->get();

        // Kategorija po POST parametru id
        $category = Category::findOrFail($categoryID);
        $categoryName = $category->name;

        // Kreiranje naziva fajla
        $categoryName = Str::lower($category->name); // pretvaranje slova u lowercase
        // Zamjena ne-alfanumerickih karaktera razmakom
        $alNumCategoryName = Str::replaceMatches('/[^A-Za-z0-9]/', ' ', $categoryName);
        $formattedCategoryName = Str::deduplicate($alNumCategoryName); // uklanjanje suvisnih razmaka
        $categoryName = Str::replaceMatches('/ /', '_', $formattedCategoryName); // zamjena razmaka sa _

        $dateTime = Carbon::now()->setTimezone('Europe/Belgrade'); // DateTime instanca
        $year = $dateTime->get('year');
        $month = $dateTime->get('month');
        $day = $dateTime->get('day');
        $hour = $dateTime->get('hour');
        $minute = $dateTime->get('minute');

        $folderPath = storage_path('app\\csv\\'); // folder za CSV fajl
        if (!file_exists($folderPath)) {
            mkdir($folderPath);
        }
        $fileName = $categoryName . "_" . $year . "_" . $month . "_" . $day . "-" . $hour . "_" . $minute . ".csv";
        $filePath = $folderPath . $fileName;

        $file = fopen($filePath, 'w'); // kreira fajl ako ne postoji
        $columns = array('product_number', 'category_name', 'department_name', 'manufacturer_name', 'upc', 'sku', 'regular_price', 'sale_price', 'description');
        fputcsv($file, $columns); // dodaje nazive kolona u CSV fajl

        // Uzima polje name iz niza, ako postoji vise department-a dodaje ;
        $categoryDepartments = $category->departments->pluck('name')->implode('; ');

        // Dodavanje podataka o proizvodima u CSV fajl
        foreach ($products as $product) {
            $product->regular_price = formatirajCijenu($product->regular_price);
            $product->sale_price = formatirajCijenu($product->sale_price);
            fputcsv(
                $file,
                [
                    $product->product_number,
                    $category->name,
                    $categoryDepartments,
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
            "message" => "Uspiješno sačuvani podaci u CSV fajl",
            "file" => [
                "path" => $filePath
            ]
        ]);
    }
}
