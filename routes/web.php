<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// storage link 
Route::get('/command/storage', function () {
    Artisan::call('storage:link');
  });