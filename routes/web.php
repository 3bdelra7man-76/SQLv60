<?php

use App\Http\Controllers\ChallengeController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->name('dashboard');
Route::get('/challenges/{day}', [ChallengeController::class, 'show'])->whereNumber('day')->name('challenges.show');
Route::post('/challenges/{day}/attempt', [ChallengeController::class, 'attempt'])->whereNumber('day')->name('challenges.attempt');
Route::patch('/challenges/{day}/draft', [ChallengeController::class, 'draft'])->whereNumber('day')->name('challenges.draft');
