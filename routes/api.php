<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DataController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;


Route::get('/products', [DataController::class, 'index']); // ispis svih podataka vezanih za proizvod
Route::get('/products-specific', [ProductController::class, 'index']); // ispis podataka svih proizvoda

// Prikaz proizvoda specificne kategorije
// Dodati GET request parametar: ?category=id
Route::get('/category-products', [ProductController::class, 'show']);

// Azuriranje proizvoda
// Poslati PUT request na rutu: /api/products/{product}, sa request body { "polje": "Nova vrijednost" } (JSON)
Route::put('/products/{product}', [ProductController::class, 'update']);
Route::delete('/products/{product}', [ProductController::class, 'destroy']); // brisanje proizvoda
Route::get('/categories', [CategoryController::class, 'index']); // prikaz kategorija

// Azuriranje naziva kategorije
// Poslati PUT request na rutu: /api/categories/{category}, sa request body { "name": "Naziv kategorije" } (JSON)
Route::put('/categories/{category}', [CategoryController::class, 'update']);
Route::delete('/categories/{category}', [CategoryController::class, 'destroy']); // brisanje kategorije

// Cuvanje podataka o proizvodima specificne kategorije u CSV fajl
// Poslati POST request na rutu: /api/category-products, sa request body {"id": int} (JSON)
Route::post('/category-products', [ProductController::class, 'generateCategoryProductsCSV']);
