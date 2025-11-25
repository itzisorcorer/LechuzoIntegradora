<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductoController;

use App\Models\ProgramaEducativo;
use App\Models\Categorias;
use App\Http\Controllers\OrdenController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\VendedorController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\EstudianteController;





Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
// --- RUTAS DE AUTENTICACIÓN PÚBLICAS ---
//--- RUTAS PÚBLICAS ---
// Cualquiera puede acceder a estas
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

//esta es la de los programas educativos
Route::get('/programas-educativos', function(){
    return ProgramaEducativo::all();
});
//recuperar contraseña
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail']);
Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword']);

Route::post('/webhooks/mercadopago', [PagoController::class, 'recibirWebhook']);

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

    //esta ruta es para que los vendedores puedan obtener SUS productos
    Route::get('/vendedor/productos', [ProductoController::class, 'misProductos']);

    //ruta del carrito de compras (checkout)
    Route::post('/checkout', [OrdenController::class, 'store']);

    //ruta para obtener las cateogias
    Route::get('/categorias', function(){
        return Categorias::all();
    
    });
    //RUTA DE MERCADO PAGO
    Route::post('/pagos/crear-preferencia/{id}', [PagoController::class, 'crearPreferenciaDePago']);

    //RUTA PARA OBTENER LAS COMPRAS DEL USUARIO AUTENTICADO
    Route::get('/estudiante/ordenes', [OrdenController::class, 'misOrdenes']);

    //ruta para que e vendedor pueda ver sus ventas (u ordenes)
    Route::get('/vendedor/ordenes', [OrdenController::class, 'misVentas']);

    //Ruta para actualizar el estado de una orden (vendedor)
    Route::put('/vendedor/ordenes/{id}', [OrdenController::class, 'updateStatus']);

    //editar datos del vendedor:
    Route::get('/vendedor/perfil', [VendedorController::class, 'show']);
    Route::put('/vendedor/perfil', [VendedorController::class, 'update']);

    //para subir la foto de perfil del estudiante
    Route::get('/estudiante/perfil', [EstudianteController::class, 'show']);
    Route::put('/estudiante/perfil', [EstudianteController::class, 'update']);
});


