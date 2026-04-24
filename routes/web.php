<?php

use App\Http\Controllers\ActionController;
use App\Mail\AnniversaryMail;
use App\Services\AnniversaryService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

$anniversary = 

Route::get('/', [ActionController::class, 'home'])->name('home');
Route::post('/journey/add', [ActionController::class, 'addJourney'])->name('journey.add');
Route::post('/journey/update', [ActionController::class, 'updateJourney'])->name('journey.update');
Route::post('/gallery/add', [ActionController::class, 'addGallery'])->name('gallery.add');
Route::post('/gallery/update', [ActionController::class, 'updateGallery'])->name('gallery.update');
Route::post('/cover/add', [ActionController::class, 'addCover'])->name('cover.add');
Route::post('/cover/update', [ActionController::class, 'updateCover'])->name('cover.update');
Route::post('/wish/add', [ActionController::class, 'addWish'])->middleware('auth')->name('wish.add');
Route::fallback(function(){
    return redirect('/');
});

Route::get('test-email', function(){
    Mail::to('zinmgnyunt99@gmail.com')->queue(new AnniversaryMail(AnniversaryService::getAnniversary()));
    return back();
});

require __DIR__.'/settings.php';
