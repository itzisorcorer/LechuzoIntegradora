<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categorias extends Model
{
    //
    use HasFactory, Notifiable, HasApiTokens;
    //Estos son atributos que pueden ser asignados de manera masiva
    protected $fillable = [
        'nombre',
        'descripcion',
    ];
    public function productos(): HasMany
    {
        return $this->hasMany(Productos::class, 'categoria_id');
    }
    
}
