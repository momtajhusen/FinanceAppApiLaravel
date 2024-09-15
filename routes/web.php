<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route to trigger 'storage:link'
Route::get('/command/storage', function () {
    $targetFolder = storage_path('app/public');
    $linkFolder = $_SERVEr['DOCUMENT_ROOT'] . '/storage';
    symlink($targetFolder, $linkFolder);
});
