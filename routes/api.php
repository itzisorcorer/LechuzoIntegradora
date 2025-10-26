<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
// --- RUTAS DE AUTENTICACIÓN PÚBLICAS ---
// Cualquiera puede acceder a estas
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// --- RUTAS PROTEGIDAS ---
// Solo usuarios autenticados (con un token válido) pueden acceder a estas
Route::middleware('auth:sanctum')->group(function () {
    
    // Ruta para cerrar sesión (requiere estar logueado)
    Route::post('/logout', [AuthController::class, 'logout']);

    // Ruta de ejemplo para obtener datos del usuario autenticado
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // ... Aquí irán tus otras rutas protegidas (crear producto, ver orden, etc.)
});


