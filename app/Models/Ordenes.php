<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany; 
use Illuminate\Database\Eloquent\Relations\HasOne;

class Ordenes extends Model
{
    //
    use HasFactory, Notifiable, HasApiTokens;
    protected $fillable = [
        'estudiante_id',
        'vendedor_id',
        'status',
        'cantidad_total',
    ];
    protected $casts = [
        'cantidad_total' => 'decimal:2',
    ];
    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiantes::class, 'estudiante_id');
    }
    public function vendedor(): BelongsTo
    {
        return $this->belongsTo(Vendedores::class, 'vendedor_id');
    }

    //estos son los items  (lineas de producto) de esta orden
    public function items(): HasMany
    {
        return $this->hasMany(Items_Ordenes::class, 'orden_id');
    }

    //paso asociado a esta orden
    public function pago(): HasOne
    {
        return $this->hasOne(Pagos::class, 'orden_id');
    }
}
