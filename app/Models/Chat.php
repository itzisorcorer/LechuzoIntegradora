<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $fillable = [
        'estudiante_id',
        'vendedor_id',
    ];
    
    // Relación con mensajes
    public function messages()
    {
        return $this->hasMany(Message::class);
    }
        // Relaciones con los participantes
    public function estudiante()
    {
        return $this->belongsTo(Estudiantes::class, 'estudiante_id');
    }

    public function vendedor()
    {
        return $this->belongsTo(Vendedor::class, 'vendedor_id');
    }
}
