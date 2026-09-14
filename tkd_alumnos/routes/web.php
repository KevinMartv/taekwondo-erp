<?php

use Illuminate\Support\Facades\Route;

Route::get('/alumnos', function () {
    return view('index');
});