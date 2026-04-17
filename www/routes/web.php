<?php

use Illuminate\Support\Facades\Route;

// Auth
use App\Http\Controllers\AuthController;

// Admin
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\GalleryController;
use App\Http\Controllers\Admin\PhotoController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\OrderController;

// Client
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Client\GalleryController as ClientGalleryController;
use App\Http\Controllers\Client\CheckoutController as ClientCheckoutController;

use App\Models\Gallery;

Route::get('/', function () {
    $galleries = Gallery::with(['photos' => function($query) {
                        $query->where('status', 'ready')->inRandomOrder()->take(1);
                    }])
                    ->withCount('photos')
                    ->where('status', '!=', 'draft')
                    ->latest()
                    ->take(6)
                    ->get();
                    
    return view('home', compact('galleries'));
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/logout', [AuthController::class, 'logout']); // Força bruta em links

// Admin Area Protected
Route::get('/admin', function() { return redirect()->route('admin.dashboard'); })->middleware('auth');

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Admin CRUDs
    Route::resource('clients', ClientController::class);
    Route::resource('galleries', GalleryController::class);
    
    // Upload, Poll & Delete Photos
    Route::post('galleries/{gallery}/photos', [PhotoController::class, 'store'])->name('galleries.photos.store');
    Route::get('galleries/{gallery}/photos/poll', [PhotoController::class, 'poll'])->name('galleries.photos.poll');
    Route::delete('galleries/{gallery}/photos/{photo}', [PhotoController::class, 'destroy'])->name('galleries.photos.destroy');
    
    Route::resource('packages', PackageController::class);
    Route::resource('orders', OrderController::class)->only(['index', 'update']);
    // Settings
    Route::get('settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [\App\Http\Controllers\Admin\SettingController::class, 'store'])->name('settings.store');
});

// Client Area Protected
Route::get('/client', function() { return redirect()->route('client.dashboard'); })->middleware('auth');

Route::middleware(['auth'])->prefix('client')->name('client.')->group(function () {
    Route::get('/dashboard', [ClientDashboardController::class, 'index'])->name('dashboard');
    Route::get('/gallery/{uuid}', [ClientGalleryController::class, 'show'])->name('galleries.show');
    
    // Checkout Flow
    Route::post('/gallery/{uuid}/checkout', [ClientCheckoutController::class, 'review'])->name('checkout.review');
    Route::post('/gallery/{uuid}/order', [ClientCheckoutController::class, 'process'])->name('checkout.process');
});
