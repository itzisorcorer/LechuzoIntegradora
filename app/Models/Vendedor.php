<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;


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
        'description',
        'matricula',
        'programa_educativo_id',
        'url_foto',
    ];
    public function urlFoto(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Storage::url($value) : null,
        );
    }
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
