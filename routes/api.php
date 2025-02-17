<?php

use App\Http\Controllers\DataController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/products', [DataController::class, 'index']); // ispis podataka o proizvodima
