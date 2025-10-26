<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Productos extends Model
{
    //
    use Notifiable, HasApiTokens, HasFactory;
    // atributos asignables de forma masiva
    protected $fillable = [
        'categoria_id',
        'nombre',
        'descripcion',
        'precio',
        'cantidad_disponible',
        'disponible',
        'url_imagen',

    ];
    protected $casts = [
        'precio' => 'decimal:2',
        'disponible' => 'boolean',
    ];
    public function vendedor(): BelongsTo
    {
        return $this->belongsTo(Vendedor::class, 'vendedor_id');
    }
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categorias::class, 'categoria_id');
    }
    public function itemsOrdenes(): HasMany
    {
        return $this->hasMany(Items_Ordenes::class, 'producto_id');
    }
}
