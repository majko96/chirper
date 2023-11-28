<?php

use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\ChirpController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('chirps.index');
})->middleware(['auth', 'verified']);

Route::resource('chirps', ChirpController::class)
    ->only(['index', 'store', 'edit', 'update', 'destroy'])
    ->middleware(['auth', 'verified']);

Route::resource('users', UsersController::class)
    ->only(['index'])
    ->middleware(['auth', 'verified']);

Route::middleware('auth')->group(function () {
    Route::get('/chirp-detail/{id}', [ChirpController::class, 'detail'])->name('chirp.detail');
    Route::post('/chirp-detail/{id}/add-comment', [ChirpController::class, 'storeComment'])->name('chirp.storeComment');
    Route::post('/chirp-detail/{id}/{commentId}/remove-comment', [ChirpController::class, 'removeComment'])->name('chirp.removeComment');
    Route::post('/like/chirp/{id}', [ChirpController::class, 'likeChirp'])->name('like.chirp');
    Route::delete('/unlike/chirp/{id}', [ChirpController::class, 'unlikeChirp'])->name('unlike.chirp');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/profile/{id}', [ProfileController::class, 'show'])->name('profile.show');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::controller(UserController::class)->group(function(){
    Route::get('autocomplete', 'autocomplete')->name('autocomplete')->middleware(['auth', 'verified']);
});

require __DIR__.'/auth.php';
