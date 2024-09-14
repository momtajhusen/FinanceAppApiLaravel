<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::get('/', function () {
    return view('welcome');
});

// storage link 
Route::get('/command/storage', function () {
    Artisan::call('storage:link');
  });