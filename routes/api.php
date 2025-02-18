<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DataController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;


Route::get('/products', [DataController::class, 'index']); // ispis svih podataka vezanih za proizvod
Route::get('/products-specific', [ProductController::class, 'index']); // ispis podataka svih proizvoda

// Prikaz proizvoda specificne kategorije
// Dodati request parametar: ?category=id
Route::get('/category-products', [ProductController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']); // prikaz kategorija

// Izmijena naziva kategorije
// Poslati PUT request na rutu: /api/categories/{category}, sa request body { "name": "Naziv kategorije" } (JSON)
Route::put('/categories/{category}', [CategoryController::class, 'update']);
Route::delete('/categories/{category}', [CategoryController::class, 'destroy']); // brisanje kategorije
