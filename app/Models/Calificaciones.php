<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Calificaciones extends Model
{
    //
    use HasFactory, Notifiable, HasApiTokens;
    protected $fillable = [
        'estudiante_id',
        'vendedor_id',
        'valor_de_calificacion',
        'comentario',
    ];

    //RELACIONES
    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiantes::class, 'estudiante_id');
    }
    public function vendedor(): BelongsTo
    {
        return $this->belongsTo(Vendedores::class, 'vendedor_id');
    }
}
