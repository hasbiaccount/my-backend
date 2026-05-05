<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventLinkController;
use App\Http\Controllers\ImageController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware(['api'])->prefix('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::post('/me', [AuthController::class, 'me']);
});

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);

Route::middleware('auth:api')->group(function () {
    Route::post('/categories', [CategoryController::class, 'store'])
        ->middleware('permission:create categories');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])
        ->middleware('permission:update categories');
    Route::patch('/categories/{category}', [CategoryController::class, 'update'])
        ->middleware('permission:update categories');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])
        ->middleware('permission:delete categories');
});

Route::get('/events', [EventController::class, 'index']);
Route::get('/events/{event}', [EventController::class, 'show']);
Route::get('/events/{event}/images', [EventController::class, 'getImages']);
Route::get('/events/{event}/links', [EventLinkController::class, 'index']);

Route::middleware('auth:api')->group(function () {
    Route::post('/events', [EventController::class, 'store'])
        ->middleware('permission:create events');
    Route::put('/events/{event}', [EventController::class, 'update'])
        ->middleware('permission:update events');
    Route::patch('/events/{event}', [EventController::class, 'update'])
        ->middleware('permission:update events');
    Route::delete('/events/{event}', [EventController::class, 'destroy'])
        ->middleware('permission:delete events');
        
    // Event links
    Route::post('/events/{event}/links', [EventLinkController::class, 'store'])
        ->middleware('permission:update events');
    Route::patch('/events/{event}/links/{link}', [EventLinkController::class, 'update'])
        ->middleware('permission:update events');
    Route::delete('/events/{event}/links/{link}', [EventLinkController::class, 'destroy'])
        ->middleware('permission:update events');
});

// Image related routes
Route::get('/images/{image}', [ImageController::class, 'show']);

Route::middleware('auth:api')->group(function () {
    Route::post('/events/{event}/image', [ImageController::class, 'upload'])
        ->middleware('permission:update events');
    Route::delete('/images/{image}', [ImageController::class, 'destroy'])
        ->middleware('permission:update events');

    Route::apiResource('cart-acara', \App\Http\Controllers\CartAcaraController::class)->except(['show']);
});
