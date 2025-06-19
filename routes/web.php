<?php

use Illuminate\Support\Facades\Route;

Route::get('/{any}', function () {
    return view('app'); // o 'welcome' si pusiste React en welcome.blade.php
})->where('any', '.*');
