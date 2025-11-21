<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;
class Estudiantes extends Model
{
    //
    use HasFactory, Notifiable, HasApiTokens;

    protected $table = 'estudiantes';

    protected $fillable = [
        'user_id',
        'nombre_completo',
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

    /**
     * Un estudiante (comprador) realiza muchas ordenes.
     */
    public function ordenes(): HasMany
    {
        return $this->hasMany(Ordenes::class, 'estudiante_id');
    }

    /**
     * Un estudiante (comprador) escribe muchas calificaciones.
     */
    public function calificaciones(): HasMany
    {
        return $this->hasMany(Calificaciones::class, 'estudiante_id');
    }

}
