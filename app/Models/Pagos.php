<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pagos extends Model
{
    //
    use HasApiTokens, Notifiable, HasFactory;
    protected $fillable = [
        'orden_id',
        'cantidad',
        'metodo',
        'status',
        'id_transaccion',
    ];
    protected $casts = [
        'cantidad' => 'decimal:2',
    ];

    //RELACIONES
    public function orden(): BelongsTo
    {
        return $this->belongsTo(Ordenes::class, 'orden_id');
    }

}
