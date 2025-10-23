<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Estudiantes extends Model
{
    //
    use HasFactory, Notifiable, HasApiTokens;
    protected $fillable = [
        'user_id',
        'nombre_completo',
        'matricula',
    ];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function ordenes(): HasMany
    {
        return $this->hasMany(Ordenes::class, 'estudiante_id');
    }
    public function calificaciones(): HasMany
    {
        return $this->hasMany(Calificaciones::class, 'estudiante_id');
    }

}
