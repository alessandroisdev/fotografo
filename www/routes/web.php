<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\GalleryController;
use App\Http\Controllers\Admin\PhotoController;

use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return view('home');
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/logout', [AuthController::class, 'logout']); // Forca bruta em links a href

// Admin Area Protected
Route::get('/admin', function() { return redirect()->route('admin.dashboard'); })->middleware('auth');

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Clients CRUD
    Route::resource('clients', ClientController::class);
    
    // Galleries CRUD
    Route::resource('galleries', GalleryController::class);
    
    // Photo Upload Endpoint via Dropzone
    Route::post('galleries/{gallery}/photos', [PhotoController::class, 'store'])->name('galleries.photos.store');
    
    // Packages CRUD
    use App\Http\Controllers\Admin\PackageController;
    Route::resource('packages', PackageController::class);
});

// Client Area Protected
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Client\GalleryController as ClientGalleryController;

Route::get('/client', function() { return redirect()->route('client.dashboard'); })->middleware('auth');

Route::middleware(['auth'])->prefix('client')->name('client.')->group(function () {
    Route::get('/dashboard', [ClientDashboardController::class, 'index'])->name('dashboard');
    Route::get('/gallery/{uuid}', [ClientGalleryController::class, 'show'])->name('galleries.show');
});
