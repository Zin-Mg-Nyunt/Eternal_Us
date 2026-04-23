<?php

use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');
Route::fallback(function(){
    return redirect('/');
});

require __DIR__.'/settings.php';
