<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Vendedores extends Model
{
    //
    use Notifiable, HasApiTokens, HasFactory;
    //Aqui se designan los atributos que podemos asignar en masa
    protected $fillable = [
        'user_id',
        'nombre_tienda',
        'descripcion',
    ];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function productos(): BelongsTo
    {
        return $this->belongsTo(Productos::class, 'vendedor_id');
    }
    public function ordenes(): BelongsTo
    {
        return $this->belongsTo(Ordenes::class, 'vendedor_id');
    }
    public function calificaciones(): BelongsTo
    {
        return $this->belongsTo(Calificaciones::class, 'vendedor_id');
    }
}
