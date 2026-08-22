<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Link;
use App\Models\ContaReceber;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Lead; // Adicionando o modelo Lead

class ConsultarController extends Controller
{
    // Formulário onde o usuário digita CPF / CNPJ / nome_link
    public function consultaForm()
    {
        return view('minhafaturas.consulta');
    }

    // Processa o input e exibe as faturas correspondentes
    public function consultaSubmit(Request $request)
    {
        $request->validate([
            'identificador' => 'required|string',
        ]);

        $identificadorOriginal = $request->identificador;
        // Limpa o identificador apenas para busca por CPF/CNPJ
        $identificadorLimpo = preg_replace('/\D/', '', $identificadorOriginal);

        $cliente = null;
        $empresa = null;
        $lead = null; // Novo: Variável para Lead
        $empresaIdByLink = null;
        $contasQuery = ContaReceber::query();


        // 1. Tenta encontrar a Empresa através do nome do Link (URL)
        $link = Link::where('nome_link', $identificadorOriginal)->first();
        if ($link && $link->empresa_id) {
            $empresaIdByLink = $link->empresa_id;
            
            // O filtro de link é o mais importante e deve ser um AND: 
            // Todas as faturas devem pertencer a esta empresa
            $contasQuery->where('empresa_id_emp', $empresaIdByLink);
        }

        // 2. Tenta encontrar Cliente, Empresa ou Lead pelo Identificador
        // Tenta encontrar cliente com esse CPF/CNPJ
        $cliente = Cliente::whereRaw("REPLACE(cpf_cnpj, '\\D', '') = ?", [$identificadorLimpo])->first();

        // Tenta encontrar empresa com esse CNPJ
        $empresa = Empresa::whereRaw("REPLACE(cpf_cnpj, '\\D', '') = ?", [$identificadorLimpo])->first();
        
        // Novo: Tenta encontrar Lead pelo ID ou algum campo de identificação
        // Assumindo que o identificador original pode ser o ID do Lead
        if (is_numeric($identificadorOriginal)) {
             $lead = Lead::find($identificadorOriginal);
        }


        // 3. Aplica o filtro de Entidade (Cliente, Empresa ou Lead) se alguma foi encontrada
        if ($cliente || $empresa || $lead) {
            // Usa where com closure para garantir que a lógica OR funcione corretamente 
            // (cliente_id = X OR empresa_id_emp = Y OR lead_id = Z)
            $contasQuery->where(function ($query) use ($cliente, $empresa, $lead) {
                if ($cliente) {
                    // Filtra faturas do cliente
                    $query->orWhere('cliente_id', $cliente->id);
                }
                if ($empresa) {
                    // Filtra faturas em nome da própria empresa
                    $query->orWhere('empresa_id_emp', $empresa->id);
                }
                if ($lead) {
                    // Novo: Filtra faturas pelo ID do Lead
                    $query->orWhere('lead_id', $lead->id);
                }
            });
        }
        
        // 4. Verifica se alguma condição de filtro foi aplicada
        if (!$empresaIdByLink && !$cliente && !$empresa && !$lead) {
            // Se não achou nada (link, cliente, empresa, ou lead), retorna vazio.
            $contas = collect();
            return view('minhafaturas.resultado', compact('contas', 'request', 'cliente', 'empresa'));
        }

        // 5. Executa a consulta final
        $contas = $contasQuery->orderBy('data_vencimento', 'asc')->get();

        return view('minhafaturas.resultado', compact('contas', 'request', 'cliente', 'empresa'));
    }
}
