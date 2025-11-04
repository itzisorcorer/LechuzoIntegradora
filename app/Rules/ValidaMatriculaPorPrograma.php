<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\DataAwareRule;
use App\Models\ProgramaEducativo;

class ValidaMatriculaPorPrograma implements ValidationRule
{
    protected $data = [];

    public function setData(array $data): static
    {
        $this->data = $data;
        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // $value es la matricula
        //$this->data['programa_educativo_id'] es el ID del programa educativo

        //1. validar que tengamos un ID de programa educativo
        if(!isset($this->data['programa_educativo_id'])) {
            $fail('El programa educativo es requerido para validar la matrícula.');
            return;

        }
        $programaId = $this->data['programa_educativo_id'];
        $programa = ProgramaEducativo::find($programaId);

        if(!$programa) {
            $fail('El programa educativo seleccionado no es válido.');
            return;
        }

        //2. Partir la matrícula
        if(strlen($value) !==10) {
            $fail('La matrícula debe tener 10 caracteres.');
            return;

        }
        $uni = substr($value, 0, 2); // a) este es universidad
        $anio = substr($value, 2, 2); // b) este es año de ingreso
        $periodo = substr($value, 4, 1); //c)  este es periodo
        $consecutivo = (int) substr($value, 5, 5); //d)  este es consecutivo

        //3. tenemo que aplicar las reglas
        //a)digitos de la uni
        if($uni !== '25'){
            $fail('La matrícula no corresponde a esta universidad (código de universidad incorrecto).');

        }
        //b) digito del año escolar
        if(!is_numeric($anio) || (int)$anio <24){
            $fail('La matricula es invalida (año incorrecto)');

        }
        //c) digito del periodo (1, 2 o 3)
        if(!in_array($periodo, ['1','2','3'])){
            $fail('La matrícula es inválida (periodo escolar incorrecto).');

        }
        //d) digitos del consecutivo
        if($consecutivo < $programa->rango_inicio || $consecutivo > $programa->rango_fin){
            $fail('La matrícula es inválida (consecutivo fuera de rango para el programa educativo seleccionado).');

        }

        
    }
}
