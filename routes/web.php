<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminClientController;
use App\Http\Controllers\AdminClientAssetController;
use App\Models\Client;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('root');
Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::view('/inquiry', 'inquiry')->name('inquiry');
Route::post('/inquiry', [InquiryController::class, 'store'])->name('inquiry.store');

Route::get('/gallery', [ClientController::class, 'galleryClients'])->name('gallery');
Route::get('/gallery/{client}', [ClientController::class, 'galleryDetail'])->name('gallery.detail');

Route::get('/photobooth', [ClientController::class, 'photoboothClients'])->name('photobooth');
Route::get('/photobooth/{client}', [ClientController::class, 'photoboothDetail'])->name('photobooth.detail');

Route::get('/lumenara', [ClientController::class, 'lumenaraClients'])->name('lumenara');
Route::get('/lumenara/{client}', [ClientController::class, 'lumenaraDetail'])->name('lumenara.detail');

Route::get('/livestreaming', [ClientController::class, 'livestreamingClients'])->name('livestreaming');
Route::get('/livestreaming/{client}', [ClientController::class, 'livestreamDetail'])->name('livestreaming.detail');

// Auth (admin login)
Route::get('/login', [AdminAuthController::class, 'create'])->name('login');
Route::post('/login', [AdminAuthController::class, 'store'])->name('login.store');
Route::post('/logout', [AdminAuthController::class, 'destroy'])->name('logout');

// Admin routes (dilindungi auth)
Route::middleware(['auth'])->group(function () {
    Route::get('/admin', function () {
        return redirect()->route('admin.clients.index');
    })->name('admin.dashboard');

    Route::get('/admin/clients', [AdminClientController::class, 'index'])->name('admin.clients.index');
    Route::get('/admin/clients/create', [AdminClientController::class, 'create'])->name('admin.clients.create');
    Route::post('/admin/clients', [AdminClientController::class, 'store'])->name('admin.clients.store');
    Route::get('/admin/clients/{client}/edit', [AdminClientController::class, 'edit'])->name('admin.clients.edit');
    Route::put('/admin/clients/{client}', [AdminClientController::class, 'update'])->name('admin.clients.update');
    Route::delete('/admin/clients/{client}', [AdminClientController::class, 'destroy'])->name('admin.clients.destroy');

    Route::get('/admin/clients/{client}/assets', function (Client $client) {
        return view('admin.clients.assets', compact('client'));
    })->name('admin.clients.assets');
    Route::post('/admin/clients/{client}/assets/sync', [AdminClientAssetController::class, 'sync'])->name('admin.clients.assets.sync');
    Route::delete('/admin/assets/{clientAsset}', [AdminClientAssetController::class, 'destroy'])->name('admin.assets.destroy');
});
