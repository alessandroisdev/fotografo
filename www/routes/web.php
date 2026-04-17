<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\GalleryController;
use App\Http\Controllers\Admin\PhotoController;

Route::get('/', function () {
    return view('home');
});

// Admin Area (TODO: add auth middleware when login exists)
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Clients CRUD
    Route::resource('clients', ClientController::class);
    
    // Galleries CRUD
    Route::resource('galleries', GalleryController::class);
    
    // Photo Upload Endpoint via Dropzone
    Route::post('galleries/{gallery}/photos', [PhotoController::class, 'store'])->name('galleries.photos.store');
});
