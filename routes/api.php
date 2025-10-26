<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductoController;


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

    //--------------RUTAS DE PRODUCTOS----------------
    // obtener todos lo productos
    Route::get('/productos', [ProductoController::class, 'index']);

    //crear un producto
    Route::post('/productos', [ProductoController::class, 'store']);

    //obtener un producto especifico por id
    Route::get('/productos/{id}', [ProductoController::class, 'show']);

    //Actualizar un producto existente
    Route::put('/productos/{id}', [ProductoController::class, 'update']);

    //Eliminar un producto
    Route::delete('/productos/{id}', [ProductoController::class, 'destroy']);
});


