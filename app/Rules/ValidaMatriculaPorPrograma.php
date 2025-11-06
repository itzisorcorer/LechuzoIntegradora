<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\DataAwareRule;
use App\Models\ProgramaEducativo;

class ValidaMatriculaPorPrograma implements ValidationRule, DataAwareRule
{
    protected $data = [];

    public function setData(array $data): static
    {
        $this->data = $data;
        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // 1. Validar que tengamos un ID de programa
        if (empty($this->data['programa_educativo_id'])) {
            $fail('El programa educativo es requerido para validar la matrícula.');
            return;
        }

        $programaId = $this->data['programa_educativo_id'];
        $programa = ProgramaEducativo::find($programaId);

        if (!$programa) {
            $fail('El programa educativo seleccionado no es válido.');
            return;
        }

        // 2. Partir la matrícula
        if (strlen($value) !== 10) {
            $fail('La matrícula debe tener 10 dígitos.');
            return;
        }

        $uni = substr($value, 0, 2);
        $anio = substr($value, 2, 2);
        $periodo = substr($value, 4, 1);
        $consecutivo = (int) substr($value, 5, 5);

        // 3. Aplicar las reglas de negocio
        
        // a) Código de Universidad
        if ($uni !== '25') {
            $fail('La matrícula no corresponde a esta universidad (código de universidad incorrecto).');
        }

        // b) Año (CORREGIDO: Acepta años desde 20 en adelante, ej. 20, 21, 22, 23, 24...)
        if (!is_numeric($anio) || (int)$anio < 20) { 
            $fail('La matricula es invalida (año incorrecto).');
        }

        // c) Periodo
        if (!in_array($periodo, ['1', '2', '3'])) {
            $fail('La matrícula es inválida (Periodo incorrecto).');
        }

        // d) Consecutivo vs Rango del Programa
        if ($consecutivo < $programa->rango_inicio || $consecutivo > $programa->rango_fin) {
            $fail('El consecutivo ('.$consecutivo.') no corresponde al programa "'.$programa->nombre.'". Rango esperado: '.$programa->rango_inicio.'-'.$programa->rango_fin.'.');
        }
    }
}
