<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route to trigger 'storage:link'
Route::get('/command/storage', function () {
    Artisan::call('storage:link');
    return 'Storage link created successfully!';
});
