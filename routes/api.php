<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DataController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/products', [DataController::class, 'index']); // ispis podataka o proizvodima
Route::get('/categories', [CategoryController::class, 'index']); // prikaz kategorija
Route::put('/categories/{category}', [CategoryController::class, 'update']); // izmjena naziva kategorije
Route::delete('/categories/{category}', [CategoryController::class, 'destroy']); // brisanje kategorije
