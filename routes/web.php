<?php

use App\Http\Controllers\LookupController;
use Illuminate\Support\Facades\Route;

Route::get('/lookup', [LookupController::class, '__invoke'])
    ->middleware('throttle:lookup');
