<?php

use App\Http\Controllers\ActionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ActionController::class, 'home'])->name('home');
Route::post('/journey/add', [ActionController::class, 'addJourney'])->name('journey.add');
Route::post('/journey/update', [ActionController::class, 'updateJourney'])->name('journey.update');
Route::post('/gallery/add', [ActionController::class, 'addGallery'])->name('gallery.add');
Route::post('/gallery/update', [ActionController::class, 'updateGallery'])->name('gallery.update');
Route::post('/cover/add', [ActionController::class, 'addCover'])->name('cover.add');
Route::post('/cover/update', [ActionController::class, 'updateCover'])->name('cover.update');
Route::fallback(function(){
    return redirect('/');
});

require __DIR__.'/settings.php';
