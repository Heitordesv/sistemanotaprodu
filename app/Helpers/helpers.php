<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ErroLog;

if (!function_exists('__saveLogError')) {
    function __saveLogError($error, $empresa_id = null)
    {
        try {
            $payload = [
                'arquivo'    => null,
                'linha'      => null,
                'erro'       => null,
                'empresa_id' => $empresa_id,
                'level'      => 'error',
                'level_name' => 'ERROR',
                'message'    => null,
                'file'       => null,
                'line'       => null,
                'trace'      => null,
                'context'    => null,
                'extra'      => null,
                'formatted'  => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if ($error instanceof \Throwable) {
                $payload['arquivo']   = $error->getFile();
                $payload['linha']     = $error->getLine();
                $payload['erro']      = $error->getMessage();
                $payload['message']   = $error->getMessage();
                $payload['file']      = $error->getFile();
                $payload['line']      = $error->getLine();
                $payload['trace']     = json_encode($error->getTrace(), JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
                $payload['formatted'] = $error->__toString();
            } else {
                // string / array
                $txt = is_string($error) ? $error : json_encode($error, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
                $payload['erro']      = $txt;
                $payload['message']   = $txt;
                $payload['formatted'] = $txt;
            }

            // Primeiro tente via Eloquent (model)
            try {
                ErroLog::create($payload);
                return true;
            } catch (\Throwable $eModel) {
                // Se falhar, tenta insert direto via query builder (reduz chance de MassAssignment)
                try {
                    DB::table('erro_logs')->insert($payload);
                    return true;
                } catch (\Throwable $eDb) {
                    // Fallback: grava em arquivo para não perder a informação
                    $fallback = [
                        'when'      => now()->toDateTimeString(),
                        'payload'   => $payload,
                        'error'     => $eDb->getMessage(),
                        'model_err' => $eModel->getMessage(),
                    ];
                    $txt = json_encode($fallback, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                    // grava em storage/logs/erro_logs_debug.txt
                    try {
                        file_put_contents(storage_path('logs/erro_logs_debug.txt'), $txt . PHP_EOL . str_repeat('-',80) . PHP_EOL, FILE_APPEND | LOCK_EX);
                    } catch (\Throwable $eFile) {
                        // se até o arquivo falhar, registra no laravel.log
                        Log::error("Falha crítica ao salvar erro no arquivo: " . $eFile->getMessage());
                    }

                    // registra no laravel.log os dois erros para inspeção
                    Log::error("Falha ao inserir erro_logs (DB): " . $eDb->getMessage(), $fallback);
                    return false;
                }
            }

        } catch (\Throwable $outer) {
            // último recurso: grava no laravel.log
            Log::error("Falha inesperada em __saveLogError: " . $outer->getMessage());
            return false;
        }
    }
}
