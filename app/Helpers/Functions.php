<?php

use App\Models\ErroLog;
use App\Models\RecordLog;
use Illuminate\Support\Str;

function __convert_value_bd($valor){
    if (str_contains((string)$valor, ".") && str_contains((string)$valor, ",")) {
        $valor = str_replace('.', '', $valor);
    }
    return str_replace(",", ".", $valor);
}

function __moeda($valor, $casas_decimais = 2){
    return number_format((float)$valor, $casas_decimais, ',', '.');
}

function __estoque($valor, $casas_decimais = 0){
    return number_format($valor, $casas_decimais, ',', '.');
}

function __data_pt($data, $hora = true){
    if($hora){
        return \Carbon\Carbon::parse($data)->format('d/m/Y H:i');
    }else{
        return \Carbon\Carbon::parse($data)->format('d/m/Y');
    }
}

function __valida_objeto($objeto){
    $usr = session('user_logged');
    if(!isset($objeto['empresa_id'])){
        return true;
    }
    return $objeto['empresa_id'] == $usr['empresa'];
}

function __array_select2($data){
    $r = [];
    foreach($data as $d){
        $r[$d] = $d;
    }
    return $r;
}


/**
 *  SALVAR ERROS COMPLETOS NO BANCO erro_logs
 */
function __saveLogError($error, $empresa_id = null)
{
    try {

        if ($error instanceof \Throwable) {

            ErroLog::create([
                'arquivo'     => $error->getFile(),
                'linha'       => $error->getLine(),
                'erro'        => $error->getMessage(),
                'empresa_id'  => $empresa_id,

                'level'       => 'error',
                'level_name'  => 'ERROR',

                'message'     => $error->getMessage(),
                'file'        => $error->getFile(),
                'line'        => $error->getLine(),
                'trace'       => $error->getTrace(),
                'context'     => null,
                'extra'       => null,

                'formatted'   => $error->__toString(),
            ]);

        } else {

            ErroLog::create([
                'arquivo'     => null,
                'linha'       => null,
                'erro'        => is_string($error) ? $error : json_encode($error),
                'empresa_id'  => $empresa_id,

                'level'       => 'error',
                'level_name'  => 'ERROR',

                'message'     => is_string($error) ? $error : json_encode($error),
                'file'        => null,
                'line'        => null,
                'trace'       => null,
                'context'     => null,
                'extra'       => null,

                'formatted'   => is_string($error) ? $error : json_encode($error),
            ]);
        }

    } catch (\Throwable $e) {
        \Log::error("Falha ao salvar log no banco: " . $e->getMessage());
    }
}


/**
 *  Salvar LOG simples (RecordLog)
 */
function __saveLog($record){
    RecordLog::create($record);
}


function erroFull($e){
    return [
        'file'    => $e->getFile(),
        'line'    => $e->getLine(),
        'message' => $e->getMessage(),
    ];
}


if (!function_exists('normalize_string')) {
    function normalize_string($string) {
        return preg_replace(
            '/[^a-zA-Z0-9]/',
            '',
            Str::of($string)->lower()->ascii()
        );
    }
}
