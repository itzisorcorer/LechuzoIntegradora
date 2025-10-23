<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Items_Ordenes extends Model
{
    //
    use HasApiTokens, HasFactory, Notifiable;
    /**
     * El nombre de la tabla asociada con el modelo.
     * (Laravel buscaría 'items_ordens' si no ponemos esto)
     */
    protected $table = 'items_ordenes';

    //Aqui se designan los atributos que podemos asignar en masa
    protected $fillable = [
        'orden_id',
        'producto_id',
        'cantidad',
        'precio_de_compra',
    ];
    //define los casts de atributos
    protected $casts = [
        'precio_de_compra' => 'decimal:2',
    ];

    //RELACIONES
    public function orden(): BelongsTo
    {
        return $this->belongsTo(Ordenes::class, 'orden_id');
    }
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Productos::class, 'producto_id');
    }
}
