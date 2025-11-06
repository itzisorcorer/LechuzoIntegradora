<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendedor extends Model
{
    //
    use Notifiable, HasApiTokens, HasFactory;

    /** 
    @var string

    */
    protected $table = 'vendedores';
    //Aqui se designan los atributos que podemos asignar en masa
    protected $fillable = [
        'user_id',
        'nombre_tienda',
        'descripcion',
        'matricula',
        'programa_educativo_id',
    ];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function productos(): HasMany
    {
        return $this->hasMany(Productos::class, 'vendedor_id');
    }

    public function ordenes(): HasMany
    {
        return $this->hasMany(Ordenes::class, 'vendedor_id');
    }
    public function calificaciones(): HasMany
    {
        return $this->hasMany(Calificaciones::class, 'vendedor_id');
    }
}
