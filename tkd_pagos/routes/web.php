<?php

use Illuminate\Support\Facades\Route;

Route::get('/pagos', function () {
    return view('index'); // Apunta a tu archivo index.blade.php
});