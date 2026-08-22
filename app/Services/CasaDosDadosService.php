<?php

namespace App\Services;

use App\Models\Lead;
use Illuminate\Support\Facades\Http;

class CasaDosDadosService {
    public function buscarESalvarEmpresas($filtros) {
        try {
            // Substitua pela sua URL real da API
            $response = Http::post('https://api.casadosdados.com.br/v1/busca', $filtros);

            if (!$response->successful()) return false;

            $empresas = $response->json()['data'] ?? [];
            $contagem = 0;

            foreach ($empresas as $emp) {
                // Evita duplicados pelo whatsapp ou nome
                $existe = Lead::where('whatsapp', $emp['telefone'])->first();
                
                if (!$existe) {
                    Lead::create([
                        'nome_completo' => $emp['razao_social'] ?? 'Sem Nome',
                        'whatsapp'      => $emp['telefone'] ?? '00000000',
                        'tipo_loja'     => $emp['cnae_principal_descricao'] ?? 'Comércio',
                        'status'        => 'Novo',
                        'data_cadastro' => now(), // Preenchimento manual
                        'ip_origem'     => request()->ip()
                    ]);
                    $contagem++;
                }
            }
            return $contagem;
        } catch (\Exception $e) {
            return false;
        }
    }
}