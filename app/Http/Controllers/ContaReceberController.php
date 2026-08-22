<?php

namespace App\Http\Controllers;

use App\Models\Acessor;
use App\Models\ContaReceber;
use App\Models\ContaPagar;

use Illuminate\Http\Request;
use App\Models\CategoriaConta;
use App\Models\ConfigNota;
use App\Models\Cidade;
use App\Models\Cliente;
use App\Models\Funcionario;
use App\Models\GrupoCliente;
use App\Models\Pais;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ConfigEcommerce;
use App\Models\Empresa;
use MercadoPago\SDK;
use MercadoPago\Payment;
use App\Jobs\EnviarConfirmaWhatsAppJobs;
use App\Jobs\NotificarClienteVencimento;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;


class ContaReceberController extends Controller
{
public function index(Request $request)
{
    // 1. Verificação de Sessão
    if (!session()->has('user_logged')) {
        return redirect('/login')->with('flash_erro', 'Sessão expirada. Por favor, faça login novamente.');
    }

    $user = session('user_logged');
    $empresaId = is_object($user) ? $user->empresa_id : ($user['empresa'] ?? null);

    // 2. Captura de Filtros
    $filters = [
        'cliente_id'      => $request->get('cliente_id'),
        'empresa_id_emp'  => $request->get('empresa_id_emp'),
        'grupo_id'        => $request->get('grupo_id'),
        'start_date'      => $request->get('start_date'),
        'end_date'        => $request->get('end_date'),
        'type_search'     => $request->get('type_search') ?? 'data_vencimento',
        'status'          => $request->get('status'),
    ];

    // 3. Dados para os Selects
    $clientes = Cliente::where('empresa_id', $empresaId)->orderBy('razao_social')->get();
    $empresas = Empresa::orderBy('nome_fantasia')->get();
    $grupos   = GrupoCliente::where('empresa_id', $empresaId)->get();

    // 4. Construção da Query Base
    $baseQuery = ContaReceber::where('empresa_id', $empresaId);
    
    if ($filters['cliente_id'])     $baseQuery->where('cliente_id', $filters['cliente_id']);
    if ($filters['empresa_id_emp']) $baseQuery->where('empresa_id_emp', $filters['empresa_id_emp']);
    
    if ($filters['grupo_id']) {
        $baseQuery->whereHas('cliente', fn($q) => $q->where('grupo_id', $filters['grupo_id']));
    }

    if ($filters['status'] !== null && $filters['status'] !== '') {
        $baseQuery->where('status', $filters['status']);
    }

    // --- REGRA DE FILTRO DE DATA ATUALIZADA ---
    if ($filters['start_date']) {
        $baseQuery->whereDate($filters['type_search'], '>=', $filters['start_date']);
    }
    
    if ($filters['end_date']) {
        $baseQuery->whereDate($filters['type_search'], '<=', $filters['end_date']);
    }
    
    /**
     * REGRA SOLICITADA: 
     * Se NÃO informou data de início E NÃO informou data de fim E NÃO selecionou um cliente específico,
     * traz apenas o mês atual. Caso contrário (se houver cliente ou data), traz tudo o que foi filtrado.
     */
    if (!$filters['start_date'] && !$filters['end_date'] && !$filters['cliente_id']) {
        $baseQuery->whereBetween('data_vencimento', [now()->startOfMonth(), now()->endOfMonth()]);
    }

    // 5. Query da Tabela (Executa a paginação)
    $data = (clone $baseQuery)->orderBy('data_vencimento', 'asc')->paginate(20)->withQueryString();

    // 6. Query de Stats
    // Nota: Removi o limite de 7 dias do 'totalAVencer' para trazer TUDO o que for futuro, conforme solicitado.
    $stats = (clone $baseQuery)->selectRaw("
            SUM(CASE WHEN status = 0 THEN valor_integral ELSE 0 END) as totalPendentes,
            SUM(CASE WHEN status = 0 AND data_vencimento < CURDATE() THEN (valor_integral - COALESCE(valor_recebido, 0)) ELSE 0 END) as totalAtrasadas,
            SUM(valor_recebido) as totalRecebidas,
            SUM(CASE WHEN status = 0 AND data_vencimento >= CURDATE() THEN valor_integral ELSE 0 END) as totalAVencer,
            COUNT(CASE WHEN status = 1 THEN 1 END) as quantRecebidas,
            COUNT(CASE WHEN status = 0 THEN 1 END) as quantPendentes,
            COUNT(CASE WHEN status = 0 AND data_vencimento < CURDATE() THEN 1 END) as quantAtrasadas,
            COUNT(CASE WHEN status = 0 AND data_vencimento >= CURDATE() THEN 1 END) as quantAVencer,
            SUM(CASE WHEN status = 1 AND data_recebimento BETWEEN ? AND ? THEN valor_recebido ELSE 0 END) as totalRecebidoMes,
            SUM(valor_integral) as baseIntegralTotal
        ", [now()->startOfMonth(), now()->endOfMonth()])
        ->first();

    // 7. Lógica de Score e Limite
    $clienteLogado = $filters['cliente_id'] ? Cliente::find($filters['cliente_id']) : null;
    $pontosAtraso = 0;
    $limiteDisponivel = 0;

    if ($clienteLogado) {
        // O limite disponível considera o total pendente (atrasadas + futuras)
        $limiteDisponivel = max(0, ($clienteLogado->limite_venda ?? 0) - ($stats->totalPendentes ?? 0));
        
        // Score de Atraso (Histórico Geral)
        $pontosAtraso = ContaReceber::where('cliente_id', $filters['cliente_id'])
            ->where('status', 1)
            ->whereNotNull('data_recebimento')
            ->whereColumn('data_recebimento', '>', 'data_vencimento')
            ->count() * 20;
    }

    // 8. Variáveis calculadas para a View
    $totalAtrasadas          = $stats->totalAtrasadas ?? 0;
    $totalRecebidoMes        = $stats->totalRecebidoMes ?? 0;
    $indiceInadimplencia     = ($stats->baseIntegralTotal ?? 0) > 0 ? round(($totalAtrasadas / $stats->baseIntegralTotal) * 100, 2) : 0;
    $previsaoFaturamentoMes  = $totalRecebidoMes + ($stats->totalPendentes ?? 0);

    return view('conta_receber.index', array_merge(
        compact(
            'data', 'clientes', 'empresas', 'grupos', 'clienteLogado', 
            'limiteDisponivel', 'pontosAtraso', 'previsaoFaturamentoMes', 
            'indiceInadimplencia', 'filters'
        ), 
        $stats->toArray()
    ));
}
public function create(Request $request)
    {
        
$empresas = Empresa::orderBy('razao_social')->get();

        $clientes = Cliente::where('empresa_id', $request->empresa_id)->get();
        $cidades = Cidade::all();
        $grupos = GrupoCliente::where('empresa_id', $request->empresa_id)->get();
        $funcionarios = Funcionario::where('empresa_id', $request->empresa_id)->get();
        $acessores = Acessor::where('empresa_id', $request->empresa_id)->get();
        $paises = Pais::all();
        $categorias = CategoriaConta::where('empresa_id', $request->empresa_id)
            ->where('tipo', 'receber')
            ->orderBy('nome')
            ->get();
        return view('conta_receber.create', compact(
        'empresas',
        'categorias',
        'cidades',
        'paises',
        'grupos',
        'acessores',
        'funcionarios',
        'clientes'
    ));
}

public function edit(Request $request, $id)
{
    $item = ContaReceber::findOrFail($id);

    if (!__valida_objeto($item)) {
        abort(403);
    }

    $empresas = Empresa::orderBy('razao_social')->get();

    $clientes = Cliente::where('empresa_id', $item->empresa_id)
        ->orderBy('razao_social')
        ->get();

    // RESTO
    $paises = Pais::all();
    $grupos = GrupoCliente::where('empresa_id', $item->empresa_id)->get();
    $acessores = Acessor::where('empresa_id', $item->empresa_id)->get();
    $funcionarios = Funcionario::where('empresa_id', $item->empresa_id)->get();

    $categorias = CategoriaConta::where('empresa_id', $item->empresa_id)
        ->where('tipo', 'receber')
        ->orderBy('nome')
        ->get();

    return view('conta_receber.edit', compact(
        'categorias',
        'item',
        'paises',
        'grupos',
        'acessores',
        'funcionarios',
        'empresas',
        'clientes' // 🔥 IMPORTANTE
    ));
}

public function store(Request $request)
{
    $this->_validate($request);

    try {
        $result = DB::transaction(function () use ($request) {

            // Ajuste de campos opcionais
            $request->merge([
                'filial_id' => $request->filial_id == -1 ? null : $request->filial_id,
                'empresa_id_emp' => $request->empresa_id_emp ?? null,
                'empresa_id' => $request->empresa_id ?? null
            ]);

            $valor_parcela = __convert_value_bd($request->valor_integral); // valor fixo da parcela

            // Conta principal
            $data = [
                'venda_id' => null,
                'data_vencimento' => $request->data_vencimento,
                'data_recebimento' => $request->status ? $request->data_vencimento : $request->data_vencimento,
                'valor_integral' => $valor_parcela,
                'valor_recebido' => $request->status ? $valor_parcela : 0,
                'referencia' => $request->referencia,
                'categoria_id' => $request->categoria_id,
                'status' => $request->status ? 'aprovado' : 'pendente',
                'empresa_id' => $request->empresa_id,
                'empresa_id_emp' => $request->empresa_id_emp,
                'cliente_id' => $request->cliente_id,
                'tipo_pagamento' => $request->tipo_pagamento,
                'observacao' => $request->observacao ?? '',
                'filial_id' => $request->filial_id
            ];

            // Cria conta principal
            $item = ContaReceber::create($data);

            // Parcelas futuras (replicando valor da parcela)
            if ($request->quantidade_parcelas > 1) {
                $data_vencimento = Carbon::parse($request->data_vencimento);
                $dia_vencimento = $data_vencimento->day;

                for ($i = 1; $i < $request->quantidade_parcelas; $i++) {
                    $data_parcela = $data_vencimento->copy()->addMonth($i);
                    $data_parcela->day($dia_vencimento);

                    ContaReceber::create([
                        'recorrencia_id' => $item->id,
                        'data_vencimento' => $data_parcela->format('Y-m-d'),
                        'data_recebimento' => $request->status ? $data_parcela->format('Y-m-d') : $data_parcela->format('Y-m-d'),
                        'valor_integral' => $valor_parcela, // mesmo valor replicado
                        'valor_recebido' => $request->status ? $valor_parcela : 0,
                        'referencia' => $request->referencia,
                        'categoria_id' => $request->categoria_id,
                        'status' => 'pendente',
                        'empresa_id' => $request->empresa_id,
                        'empresa_id_emp' => $request->empresa_id_emp,
                        'cliente_id' => $request->cliente_id,
                        'tipo_pagamento' => $request->tipo_pagamento,
                        'observacao' => $request->observacao ?? '',
                        'filial_id' => $request->filial_id
                    ]);
                }
            }

            return $item;
        });

        session()->flash("flash_sucesso", "Conta a receber cadastrada com sucesso!");
    } catch (\Exception $e) {
        session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
        __saveLogError($e, $request->empresa_id);
    }

    return redirect()->route('conta-receber.index');
}

private function _validate(Request $request)
{
    $rules = [
        'valor_integral' => 'required',
        'data_vencimento' => 'required',
        'empresa_id' => 'required_without:empresa_id_emp',
        'empresa_id_emp' => 'required_without:empresa_id',
    ];
    $messages = [
        'valor_integral.required' => 'O campo valor é obrigatório.',
        'data_vencimento.required' => 'O campo vencimento é obrigatório.',
        'empresa_id.required_without' => 'Informe empresa_id ou empresa_id_emp.',
        'empresa_id_emp.required_without' => 'Informe empresa_id ou empresa_id_emp.'
    ];
    $this->validate($request, $rules, $messages);
}
public function update(Request $request, $id)
{
    $item = ContaReceber::findOrFail($id);

$empresas = Empresa::orderBy('razao_social')->get();

    $this->_validate($request);

    try {
        $request->merge([
            'valor_integral' => __convert_value_bd($request->valor_integral),
            'filial_id' => $request->filial_id == -1 ? null : $request->filial_id,
            'empresa_id_emp' => $request->empresa_id_emp ?? null,
            'empresa_id' => $request->empresa_id ?? null
        ]);

        // Atualiza apenas os campos existentes na tabela
        $item->fill($request->only([
            'valor_integral',
            'data_vencimento',
            'filial_id',
            'empresa_id',
            'empresa_id_emp',
            'cliente_id',
            'tipo_pagamento',
            'referencia',
            'categoria_id',
            'status',
            'observacao'
        ]))->save();

        session()->flash("flash_sucesso", "Conta a receber atualizada!");
    } catch (\Exception $e) {
        session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
        __saveLogError($e, $request->empresa_id);
    }


$previous = $request->input('previous_url', route('conta-receber.index'));
return redirect($previous);
    
}

    public function destroy($id)
    {
        $item = ContaReceber::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        try {
            $item->delete();
            session()->flash("flash_sucesso", "Conta removida!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('conta-receber.index');
    }

public function destroyMass(Request $request)
{
    if (!$request->ids) {
        session()->flash("flash_erro", "Nenhum item selecionado!");
        return redirect()->back();
    }

    $ids = explode(',', $request->ids);

    try {
        $itens = ContaReceber::whereIn('id', $ids)->get();

        foreach ($itens as $item) {
            if (__valida_objeto($item)) {
                $item->delete();
            }
        }

        session()->flash("flash_sucesso", count($ids) . " contas removidas!");
    } catch (\Exception $e) {
        session()->flash("flash_erro", "Erro ao excluir registros: " . $e->getMessage());
        __saveLogError($e, request()->empresa_id);
    }

    // Retorna para a página anterior (mantém paginação e filtros se vierem da index)
    return redirect()->back();
}
public function gerarBoletoMercadoPago($id)
{
    $conta = \App\Models\ContaReceber::findOrFail($id);

    // Primeiro tenta buscar o cliente
    $cliente = \App\Models\Cliente::find($conta->cliente_id);
    if (!$cliente) {
        $cliente = \App\Models\Empresa::find($conta->empresa_id_imp);
    }

    if (!$cliente) {
        return response()->json(['erro' => 'Cliente ou empresa não encontrado.'], 400);
    }

    $config = \App\Models\ConfigEcommerce::where('empresa_id', $conta->empresa_id)->first();
    if (!$config || empty($config->mercadopago_access_token)) {
        return response()->json(['erro' => 'Token do Mercado Pago não configurado.'], 400);
    }

    // --- FORMATAÇÃO DA DATA DE VENCIMENTO ---
    $dataVenc = new \DateTime($conta->data_vencimento, new \DateTimeZone('America/Sao_Paulo'));
    $dataVenc->setTime(12, 0, 0); 
    $dataExpiracao = $dataVenc->format('Y-m-d\TH:i:s.000P'); 
    // ----------------------------------------

    // Nome e sobrenome
    $nome = trim($cliente->razao_social ?? $cliente->nome_fantasia ?? "Cliente");
    $primeiroNome = explode(' ', $nome)[0];
    $sobrenome = trim(str_replace($primeiroNome, '', $nome)) ?: " ";

    // CPF ou CNPJ
    $cpfCnpj = preg_replace('/\D/', '', $cliente->cpf_cnpj ?? '00000000191');
    $tipoIdentificacao = strlen($cpfCnpj) === 11 ? 'CPF' : 'CNPJ';

    // Endereço
    $cidade = $cliente->cidade_id ? \App\Models\Cidade::find($cliente->cidade_id) : null;

    $dados = [
        "transaction_amount" => (float) $conta->valor_integral,
        "date_of_expiration" => $dataExpiracao, // Enviando a data correta
        "description" => "Pagamento referente ao título: {$conta->referencia}",
        "external_reference" => (string) $conta->id,
        "payment_method_id" => "bolbradesco",
        "notification_url" => url("/mercadopago/notification?id={$conta->id}"),
        "payer" => [
            "email" => $cliente->email ?? "cliente@exemplo.com",
            "first_name" => $primeiroNome,
            "last_name" => $sobrenome,
            "identification" => [
                "type" => $tipoIdentificacao,
                "number" => $cpfCnpj
            ],
            "address" => [
                "zip_code" => preg_replace('/\D/', '', $cliente->cep ?? '00000000'),
                "street_name" => $cliente->rua ?? $cliente->rua_cobranca ?? 'Endereço não informado',
                "street_number" => $cliente->numero ?? $cliente->numero_cobranca ?? 'S/N',
                "neighborhood" => $cliente->bairro ?? $cliente->bairro_cobranca ?? 'Bairro',
                "city" => $cidade->nome ?? 'Cidade',
                "federal_unit" => $cidade->uf ?? 'SP',
            ]
        ]
    ];

    $codigoKey = uniqid("boleto_{$conta->id}_", true);

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://api.mercadopago.com/v1/payments',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($dados),
        CURLOPT_HTTPHEADER => [
            'accept: application/json',
            'content-type: application/json',
            'X-Idempotency-Key: ' . $codigoKey,
            'Authorization: ' . 'Bearer ' . $config->mercadopago_access_token
        ],
    ]);

    $response = curl_exec($curl);
    $resultado = json_decode($response);
    curl_close($curl);

    if (isset($resultado->transaction_details->external_resource_url)) {
        $conta->update([
            'status' => 'aguardando_pagamento',
            'boleto_link' => $resultado->transaction_details->external_resource_url,
            'observacao' => 'Boleto Mercado Pago gerado com vencimento em ' . $dataVenc->format('d/m/Y')
        ]);

        return response()->json([
            'boleto_link' => $resultado->transaction_details->external_resource_url
        ]);
    }

    return response()->json(['erro' => $resultado->message ?? 'Erro ao gerar boleto.'], 400);
}
public function gerarBoletoEmpresaMercadoPago($id)
{
    // 1️⃣ Busca a conta a receber
    $conta = ContaReceber::findOrFail($id);

    // 2️⃣ Busca os dados da empresa na tabela config_notas
    $empresa = ConfigNota::where('empresa_id', $conta->empresa_id_emp)->first();

    if (!$empresa) {
        return response()->json(['erro' => 'Empresa não encontrada.'], 400);
    }

    // 3️⃣ Busca configuração do Mercado Pago
    $config = ConfigEcommerce::where('empresa_id', $conta->empresa_id)->first();
    if (!$config || empty($config->mercadopago_access_token)) {
        return response()->json(['erro' => 'Token do Mercado Pago não configurado.'], 400);
    }

    // --- ARRUMANDO SÓ A DATA AQUI ---
    $dataVenc = new \DateTime($conta->data_vencimento, new \DateTimeZone('America/Sao_Paulo'));
    $dataVenc->setTime(12, 0, 0); // Define um horário padrão para evitar erro de data retroativa
    $dataExpiracao = $dataVenc->format('Y-m-d\TH:i:s.000P'); 
    // --------------------------------

    // 4️⃣ Nome e sobrenome (para o "payer")
    $nome = trim($empresa->nome_fantasia ?? $empresa->razao_social ?? "Empresa");
    $primeiroNome = explode(' ', $nome)[0];
    $sobrenome = trim(str_replace($primeiroNome, '', $nome)) ?: " ";

    // 5️⃣ CNPJ
    $cnpj = preg_replace('/\D/', '', $empresa->cpf_cnpj ?? '00000000191');

    // 6️⃣ Cidade
    $cidade = $empresa->cidade_id ? Cidade::find($empresa->cidade_id) : null;

    // 7️⃣ Endereço obrigatório para boleto registrado
    $zip_code = preg_replace('/\D/', '', $empresa->cep ?? '00000000');
    $street_name = $empresa->logradouro ?? 'Endereço não informado';
    $street_number = $empresa->numero ?? 'S/N';
    $neighborhood = $empresa->bairro ?? 'Bairro';
    $city_name = $cidade->nome ?? 'Cidade';
    $federal_unit = $cidade->uf ?? 'SP';

    // 8️⃣ Monta os dados para envio ao Mercado Pago
    $dados = [
        "transaction_amount" => (float) $conta->valor_integral,
        "date_of_expiration" => $dataExpiracao, // <--- DATA ARRUMADA AQUI
        "description" => "Pagamento referente ao título: {$conta->referencia}",
        "external_reference" => (string) $conta->id,
        "payment_method_id" => "bolbradesco",
        "notification_url" => url("/mercadopago/notification?id={$conta->id}"),
        "payer" => [
            "email" => $empresa->email ?? "empresa@exemplo.com",
            "first_name" => $primeiroNome,
            "last_name" => $sobrenome,
            "identification" => [
                "type" => "CNPJ",
                "number" => $cnpj
            ],
            "address" => [
                "zip_code" => $zip_code,
                "street_name" => $street_name,
                "street_number" => $street_number,
                "neighborhood" => $neighborhood,
                "city" => $city_name,
                "federal_unit" => $federal_unit,
            ]
        ]
    ];

    // 9️⃣ Gera um X-Idempotency-Key para evitar duplicidade
    $codigoKey = uniqid("boleto_{$conta->id}_", true);

    // 10️⃣ Chamada para a API do Mercado Pago
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://api.mercadopago.com/v1/payments',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($dados),
        CURLOPT_HTTPHEADER => [
            'accept: application/json',
            'content-type: application/json',
            'X-Idempotency-Key: ' . $codigoKey,
            'Authorization: ' . 'Bearer ' . $config->mercadopago_access_token
        ],
    ]);

    $response = curl_exec($curl);
    $resultado = json_decode($response);
    curl_close($curl);

    // 11️⃣ Checa se o boleto foi gerado
    if (isset($resultado->transaction_details->external_resource_url)) {
        // ✅ Salva o link do boleto no sistema
        $conta->update([
            'status' => 'aguardando_pagamento',
            'boleto_link' => $resultado->transaction_details->external_resource_url,
            'observacao' => 'Boleto Mercado Pago gerado para empresa.'
        ]);

        return response()->json([
            'boleto_link' => $resultado->transaction_details->external_resource_url
        ]);
    }

    // 12️⃣ Se der erro na API
    return response()->json(['erro' => $resultado->message ?? 'Erro ao gerar boleto.'], 400);
}
public function verificarPagamentoMercadoPago($id)
{
    $conta = ContaReceber::findOrFail($id);

    if(empty($conta->boleto_link)) {
        return response()->json(['erro' => 'Boleto não gerado.'], 400);
    }

    $config = ConfigEcommerce::where('empresa_id', $conta->empresa_id)->first();
    if (!$config || empty($config->mercadopago_access_token)) {
        return response()->json(['erro' => 'Token do Mercado Pago não configurado.'], 400);
    }

    // Aqui você precisa do "payment_id" ou "collector_id" retornado na geração do boleto
    $payment_id = $conta->external_reference ?? null;
    if(!$payment_id) {
        return response()->json(['erro' => 'Payment ID não encontrado.'], 400);
    }

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://api.mercadopago.com/v1/payments/' . $payment_id,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => [
            'accept: application/json',
            'content-type: application/json',
            'Authorization: ' . 'Bearer ' . $config->mercadopago_access_token
        ],
    ]);

    $response = curl_exec($curl);
    $resultado = json_decode($response);
    curl_close($curl);

    if(isset($resultado->status) && $resultado->status === 'approved') {
        // Atualiza conta como paga
        if($conta->status != 1) {
            $conta->update([
                'status' => 1,
                'valor_recebido' => $resultado->transaction_amount,
                'data_recebimento' => now(),
                'observacao' => 'Pagamento aprovado via Mercado Pago'
            ]);
        }

        return response()->json([
            'status' => 1,
            'data_recebimento' => $conta->data_recebimento->format('d/m/Y H:i')
        ]);
    }

    return response()->json([
        'status' => $resultado->status ?? 'pending'
    ]);
}


 // Ação para exibir o formulário de pagamento
    public function pay($id)
    {
        $item = ContaReceber::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        return view('conta_receber.pay', compact('item'));
    }
    public function gerarPixMercadoPago($id)
    {
        $conta = ContaReceber::findOrFail($id);

        // Busca cliente ou empresa
        $cliente = Cliente::find($conta->cliente_id) ?? Empresa::find($conta->empresa_id_emp);

        if (!$cliente) {
            return response()->json(['erro' => 'Cliente ou empresa não encontrado.'], 400);
        }

        $config = ConfigEcommerce::where('empresa_id', $conta->empresa_id)->first();
        if (!$config || empty($config->mercadopago_access_token)) {
            return response()->json(['erro' => 'Token do Mercado Pago não configurado.'], 400);
        }

        // Nome do cliente
        $nome = trim($cliente->razao_social ?? $cliente->nome_fantasia ?? "Cliente");
        $primeiroNome = explode(' ', $nome)[0];
        $sobrenome = trim(str_replace($primeiroNome, '', $nome)) ?: " ";

        // CPF/CNPJ
        $cpfCnpj = preg_replace('/\D/', '', $cliente->cpf_cnpj ?? '00000000191');
        $identificacao = strlen($cpfCnpj) === 11 
            ? ['type' => 'CPF', 'number' => $cpfCnpj] 
            : ['type' => 'CNPJ', 'number' => $cpfCnpj];

        // Dados PIX
        $dados = [
            "transaction_amount" => (float) $conta->valor_integral,
            "description" => "Pagamento referente ao título: {$conta->referencia}",
            "external_reference" => (string) $conta->id,
            "payment_method_id" => "pix",
            "notification_url" => url("/mercadopago/notification?id={$conta->id}"),
            "payer" => [
                "email" => $cliente->email ?? "cliente@exemplo.com",
                "first_name" => $primeiroNome,
                "last_name" => $sobrenome,
                "identification" => $identificacao
            ]
        ];

        $codigoKey = uniqid("pix_{$conta->id}_", true);

        // Envia requisição para Mercado Pago
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.mercadopago.com/v1/payments',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($dados),
            CURLOPT_HTTPHEADER => [
                'accept: application/json',
                'content-type: application/json',
                'X-Idempotency-Key: ' . $codigoKey,
                'Authorization: Bearer ' . $config->mercadopago_access_token
            ],
        ]);

        $response = curl_exec($curl);
        $resultado = json_decode($response);
        curl_close($curl);

        if (isset($resultado->point_of_interaction->transaction_data)) {
            $pix = $resultado->point_of_interaction->transaction_data;
            $qr_code_base64 = $pix->qr_code_base64 ?? null;
            $pix_copia_cola = $pix->qr_code ?? null;

            $conta->update([
                'status' => 'aguardando_pagamento',
                'observacao' => "PIX Mercado Pago gerado.",
                'chave_pix' => $pix_copia_cola,
            ]);

            return response()->json([
                'qr_code_base64' => $qr_code_base64,
                'pix_copia_cola' => $pix_copia_cola
            ]);
        }

        return response()->json(['erro' => $resultado->message ?? 'Erro ao gerar PIX.'], 400);
    }
public function verificarStatus($id)
{
    $conta = \App\Models\ContaReceber::find($id);

    if (!$conta) {
        return response()->json(['erro' => 'Conta não encontrada'], 404);
    }

    $config = \App\Models\ConfigEcommerce::where('empresa_id', $conta->empresa_id)->first();
    if (!$config || empty($config->mercadopago_access_token)) {
        return response()->json(['erro' => 'Configuração Mercado Pago não encontrada'], 404);
    }

    // Consulta os pagamentos pelo external_reference
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://api.mercadopago.com/v1/payments/search?external_reference=' . $conta->id,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $config->mercadopago_access_token,
            'Content-Type: application/json'
        ],
    ]);

    $response = curl_exec($curl);
    curl_close($curl);

    $resultado = json_decode($response);

    if (!isset($resultado->results) || count($resultado->results) === 0) {
        return response()->json(['status' => 0]); // ainda não pago
    }

    // Pega a última transação
    $pagamento = collect($resultado->results)->sortByDesc('date_created')->first();

    if ($pagamento->status === 'approved') {
        // Atualiza conta como paga
        if ($conta->status != 1) {
            $conta->update([
                'status' => 1,
                'valor_recebido' => $pagamento->transaction_amount,
                'data_recebimento' => now(),
                'observacao' => 'Pagamento aprovado via Mercado Pago'
            ]);
        }

        return response()->json([
            'status' => 1,
            'data_recebimento' => \Carbon\Carbon::parse($conta->data_recebimento)->format('d/m/Y H:i')
        ]);
    }

    return response()->json(['status' => 0]); // ainda não pago
}

public function gerarPixMassa(Request $request)
    {
        try {
            // 1. Normalização dos IDs (aceita array ou string separada por vírgula)
            $ids = is_array($request->ids) ? $request->ids : explode(',', $request->ids);
            $ids = array_filter($ids);

            if (empty($ids)) {
                return response()->json(['erro' => 'Nenhum registro selecionado.'], 400);
            }

            // 2. Busca faturas pendentes
            $contas = ContaReceber::whereIn('id', $ids)
                ->where('status', 0)
                ->with('cliente')
                ->get();

            if ($contas->isEmpty()) {
                return response()->json(['erro' => 'Nenhuma fatura pendente encontrada.'], 404);
            }

            // 3. Configuração do SDK com o Token da Empresa
            $config = ConfigEcommerce::where('empresa_id', $contas->first()->empresa_id)->first();
            
            if (!$config || empty($config->mercadopago_access_token)) {
                return response()->json(['erro' => 'Configuração do Mercado Pago não encontrada para esta empresa.'], 422);
            }

            SDK::setAccessToken($config->mercadopago_access_token);

            // 4. Cálculo do Total (Arredondado para 2 casas decimais)
            $total = round($contas->sum(function($c) {
                return (float) ($c->valor_integral - ($c->valor_recebido ?? 0));
            }), 2);

            if ($total <= 0) {
                return response()->json(['erro' => 'O valor total das faturas deve ser maior que zero.'], 400);
            }

            // 5. Criação do Pagamento PIX
            $payment = new Payment();
            $payment->transaction_amount = $total;
            $payment->description = "Pagamento agrupado: " . $contas->count() . " contas";
            $payment->external_reference = 'MASSA_' . time();
            $payment->payment_method_id = "pix";
            
            // Notification URL apenas se estiver em produção (exige HTTPS)
            if (app()->environment('production')) {
                $payment->notification_url = url("/api/mercadopago/notification");
            }

            $payment->payer = [
                "email" => $contas->first()->cliente->email ?? "comercial@nfenotas.com.br",
                "first_name" => "Pagamento",
                "last_name" => "Massa"
            ];

            // Executa a transação com chave de idempotência
            $payment->save(['X-Idempotency-Key' => uniqid()]);

            // 6. Retorno da Resposta
            if ($payment->error) {
                return response()->json(['erro' => 'Mercado Pago: ' . $payment->error->message], 400);
            }

            if ($payment->status == 'pending' || $payment->status == 'approved') {
                $pixData = $payment->point_of_interaction->transaction_data;

                // Atualiza observação para controle interno
                ContaReceber::whereIn('id', $ids)->update([
                    'observacao' => "PIX Massa Gerado - Ref: {$payment->id}"
                ]);

                return response()->json([
                    'success' => true,
                    'qr_code_base64' => $pixData->qr_code_base64,
                    'pix_copia_cola' => $pixData->qr_code,
                    'total_formatado' => number_format($total, 2, ',', '.'),
                    'ids' => $ids
                ]);
            }

            return response()->json(['erro' => 'Status retornado: ' . $payment->status], 400);

        } catch (\Exception $e) {
            Log::error('Erro PIX Massa: ' . $e->getMessage());
            return response()->json([
                'erro' => 'Erro interno ao processar PIX.',
                'debug' => $e->getMessage()
            ], 500);
        }
    }public function verificarPixMassa(Request $request)
{
    $ids = $request->ids;

    if (is_string($ids)) {
        $ids = json_decode($ids, true);
    }

    if (!$ids || !is_array($ids)) {
        return response()->json(['status' => 0]);
    }

    $externalRef = 'massa_' . implode('-', $ids);

    $contas = ContaReceber::whereIn('id', $ids)->get();

    if ($contas->isEmpty()) {
        return response()->json(['status' => 0]);
    }

    $empresaId = $contas->first()->empresa_id;

    $config = ConfigEcommerce::where('empresa_id', $empresaId)->first();

    if (!$config) {
        return response()->json(['status' => 0]);
    }

    // 🔎 consulta mercado pago
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://api.mercadopago.com/v1/payments/search?external_reference=' . $externalRef,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $config->mercadopago_access_token
        ],
    ]);

    $response = curl_exec($curl);
    curl_close($curl);

    $resultado = json_decode($response);

    if (!isset($resultado->results) || count($resultado->results) === 0) {
        return response()->json(['status' => 0]);
    }

    $pagamento = collect($resultado->results)->sortByDesc('date_created')->first();

    if ($pagamento->status === 'approved') {

        foreach ($contas as $conta) {

            if ($conta->status != 1) {

                $conta->update([
                    'status' => 1,
                    'valor_recebido' => $conta->valor_integral,
                    'data_recebimento' => now(),
                    'observacao' => 'Pagamento PIX massa aprovado'
                ]);
            }
        }

        return response()->json(['status' => 1]);
    }

    return response()->json(['status' => 0]);
}
// Ação para processar o pagamento e despachar Job para enviar WhatsApp
public function payPut(Request $request, $id)
{
    $item = ContaReceber::findOrFail($id);

    if (!__valida_objeto($item)) {
        abort(403);
    }

    try {
        $empresaId = $request->empresa_id ?? session('empresa_id');
        $statusAnterior = (bool) $item->status;
        
        // 1. Converter o valor recebido na requisição (o valor de "hoje")
        $valorPagoAgora = __convert_value_bd($request->valor_pago);
        $valorIntegral = (float) $item->valor_integral;
        
        // 2. LÓGICA DE ACUMULAÇÃO: Soma o valor atual ao que já existia
        $item->valor_recebido = $item->valor_recebido + $valorPagoAgora;
        
        // 3. LÓGICA DE STATUS: Só define como pago (true) se igualar ou superar o total
        if ($item->valor_recebido >= $valorIntegral) {
            $item->status = true;
        } else {
            $item->status = false; // Permanece pendente/parcial
        }

        // Atualiza a data com a data do último pagamento
        $item->data_recebimento = $request->data_recebimento;
        $item->tipo_pagamento = $request->tipo_pagamento;
        $item->save();

        // 4. Envio de WhatsApp (agora envia sempre, diferenciando parcial de total)
        $cliente = Cliente::find($item->cliente_id);

        if ($cliente && !empty($cliente->telefone)) {
            $numeroCliente = preg_replace('/\D/', '', $cliente->telefone);
            if (!str_starts_with($numeroCliente, '55')) {
                $numeroCliente = '55' . $numeroCliente;
            }

            $nomeCliente = ($cliente->razao_social ?? $cliente->nome_fantasia);
            $valorFormatado = number_format($valorPagoAgora, 2, ',', '.');
            $restante = $item->valor_integral - $item->valor_recebido;

            // MENSAGENS HUMANIZADAS
            if ($item->status === true) {
                // MENSAGEM DE QUITAÇÃO (PAGAMENTO TOTAL)
                $mensagem = "Olá, *{$nomeCliente}*! 👋\n\n";
                $mensagem .= "Passando para avisar que recebemos o pagamento de *R$ {$valorFormatado}* referente ao título *{$item->referencia}*. ✅\n\n";
                $mensagem .= "Com isso, sua conta foi *quitada com sucesso*! 🎉\n\n";
                $mensagem .= "Agradecemos a confiança! 😊\n\n_" . config('app.name') . "_";
            } else {
                // MENSAGEM DE PAGAMENTO PARCIAL
                $restanteFormatado = number_format($restante, 2, ',', '.');
                $mensagem = "Olá, *{$nomeCliente}*! 👋\n\n";
                $mensagem .= "Recebemos sua parcela de *R$ {$valorFormatado}* referente ao título *{$item->referencia}*. 💰\n\n";
                $mensagem .= "Ainda resta um saldo de *R$ {$restanteFormatado}*. Estamos aguardando o restante para finalizar. 👍\n\n";
                $mensagem .= "Atenciosamente,\n_" . config('app.name') . "_";
            }

            EnviarConfirmaWhatsAppJobs::dispatch(
                $numeroCliente,
                $mensagem,
                $empresaId
            );

            Log::info("Job WhatsApp disparado para cliente ID {$cliente->id}, conta ID {$item->id}. Status Final: " . ($item->status ? 'Pago' : 'Parcial'));
        }

        session()->flash("flash_sucesso", "Pagamento de R$ " . number_format($valorPagoAgora, 2, ',', '.') . " registrado com sucesso!");
    } catch (\Exception $e) {
        session()->flash("flash_erro", "Erro ao registrar pagamento: " . $e->getMessage());
        __saveLogError($e, $empresaId ?? null);
        Log::error("Erro no payPut: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
    }

    $previous = $request->input('previous_url', route('conta-receber.index'));
    return redirect($previous);
}

public function receberMassa(Request $request)
{
    $ids = explode(',', $request->ids);

    foreach ($ids as $id) {

        $item = ContaReceber::find($id);
        if (!$item) continue;

        $restante = $item->valor_integral - $item->valor_recebido;

        if ($restante <= 0) continue;

        $item->valor_recebido += $restante;
        $item->status = 1;
        $item->data_recebimento = now();
        $item->tipo_pagamento = 'MASSA';

        $item->save();
    }

    return back()->with('success', 'Pagamentos realizados!');
}
private function enviarWhatsPagamento($item, $valorPago, $empresaId)
{
    $cliente = Cliente::find($item->cliente_id);

    if (!$cliente || empty($cliente->telefone)) return;

    $numero = preg_replace('/\D/', '', $cliente->telefone);
    if (!str_starts_with($numero, '55')) {
        $numero = '55' . $numero;
    }

    $nome = $cliente->razao_social ?? $cliente->nome_fantasia;
    $valorFormatado = number_format($valorPago, 2, ',', '.');

    if ($item->status) {
        $msg = "Olá {$nome}, pagamento de R$ {$valorFormatado} recebido. Conta quitada ✅";
    } else {
        $restante = $item->valor_integral - $item->valor_recebido;
        $msg = "Olá {$nome}, recebemos R$ {$valorFormatado}. Restante: R$ " . number_format($restante, 2, ',', '.');
    }

    EnviarConfirmaWhatsAppJobs::dispatch($numero, $msg, $empresaId);
}
public function payMass(Request $request)
{
    $ids = explode(',', $request->ids);

    try {
        $empresaId = $request->empresa_id ?? session('empresa_id');

        foreach ($ids as $id) {

            $item = ContaReceber::find($id);
            if (!$item || !__valida_objeto($item)) {
                continue;
            }

            // 🔥 DEFINE VALOR (total restante automaticamente)
            $valorRestante = $item->valor_integral - $item->valor_recebido;

            if ($valorRestante <= 0) {
                continue; // já pago
            }

            // 💰 aplica pagamento total automático
            $item->valor_recebido += $valorRestante;
            $item->status = true;
            $item->data_recebimento = now();
            $item->tipo_pagamento = 'MASSA';

            $item->save();

            // 📲 WHATSAPP (MESMA IDEIA DO SEU)
            $cliente = Cliente::find($item->cliente_id);

            if ($cliente && !empty($cliente->telefone)) {

                $numero = preg_replace('/\D/', '', $cliente->telefone);
                if (!str_starts_with($numero, '55')) {
                    $numero = '55' . $numero;
                }

                $nome = $cliente->razao_social ?? $cliente->nome_fantasia;
                $valorFormatado = number_format($valorRestante, 2, ',', '.');

                $mensagem = "Olá, *{$nome}*! 👋\n\n";
                $mensagem .= "Recebemos o pagamento de *R$ {$valorFormatado}* referente ao título *{$item->referencia}*. ✅\n\n";
                $mensagem .= "Sua conta foi *quitada com sucesso*! 🎉\n\n";
                $mensagem .= "Agradecemos! 😊\n\n_" . config('app.name') . "_";

                EnviarConfirmaWhatsAppJobs::dispatch(
                    $numero,
                    $mensagem,
                    $empresaId
                );
            }
        }

        return redirect()->back()->with('success', 'Pagamentos realizados com sucesso!');
        
    } catch (\Exception $e) {

        Log::error("Erro no payMass: " . $e->getMessage());

        return redirect()->back()->with('error', 'Erro ao processar pagamentos.');
    }
}

public function enviarCobranca(Request $request, $id)
{
    $conta = ContaReceber::findOrFail($id);

    if (!__valida_objeto($conta)) {
        abort(403);
    }

    // --------------------------------
    // BUSCAR DESTINATÁRIO
    // --------------------------------

    $cliente = null;
    $empresaDestino = null;

    if (!empty($conta->cliente_id)) {
        // cobrança para cliente
        $cliente = Cliente::find($conta->cliente_id);
    } elseif (!empty($conta->empresa_id_emp)) {
        // cobrança para empresa
        $empresaDestino = Empresa::find($conta->empresa_id_emp);
    }

    // empresa emissora
    $empresa = Empresa::with('configEcommerce')->find($conta->empresa_id);

    if (!$empresa) {
        return back()->with('flash_erro', 'Empresa não encontrada.');
    }

    // --------------------------------
    // DEFINIR NOME E CELULAR
    // --------------------------------

    if ($cliente) {

        $nome = $cliente->razao_social;
        $celular = $cliente->celular;

    } elseif ($empresaDestino) {

        $nome = $empresaDestino->razao_social;
        $celular = $empresaDestino->celular ?? $empresaDestino->telefone;

    } else {

        return back()->with('flash_erro', 'Destinatário não encontrado.');

    }

    if (empty($celular)) {
        return back()->with('flash_erro', 'Destinatário não possui número de celular.');
    }

    // --------------------------------
    // NORMALIZAR NÚMERO
    // --------------------------------

    $numero = preg_replace('/\D/', '', $celular);

    if (!str_starts_with($numero, '55')) {
        $numero = '55' . $numero;
    }

    // --------------------------------
    // DADOS DA COBRANÇA
    // --------------------------------

    $dataVenc = \Carbon\Carbon::parse($conta->data_vencimento)->format('d/m/Y');
    $valor = number_format($conta->valor_integral, 2, ',', '.');
    $referencia = $conta->referencia ?? 'Fatura';

if ($cliente) {
    // cobrança para cliente
    $linkPagamento = url("/pg/{$conta->id}");
} else {
    // cobrança para empresa
    $linkPagamento = url("/pgempresa/{$conta->id}");
}
    // --------------------------------
    // MENSAGEM
    // --------------------------------

    $mensagem  = "*Olá {$nome}!* 👋\n\n";
    $mensagem .= "Estamos passando para lembrar da sua cobrança:\n\n";
    $mensagem .= "📄 *Referência:* {$referencia}\n";
    $mensagem .= "💰 *Valor:* R$ {$valor}\n";
    $mensagem .= "📅 *Vencimento:* {$dataVenc}\n\n";
    $mensagem .= "💳 *Pague facilmente pelo link:*\n{$linkPagamento}\n\n";
    $mensagem .= "Caso já tenha efetuado o pagamento, por favor desconsidere.\n\n";
    $mensagem .= "_{$empresa->razao_social}_";

    // --------------------------------
    // ENVIO
    // --------------------------------

    try {

        NotificarClienteVencimento::dispatch(
            $empresa->id,
            $numero,
            $mensagem
        );

        return back()->with('flash_sucesso', 'Mensagem enviada com sucesso!');

    } catch (\Exception $e) {

        \Log::error("Erro ao enviar cobrança", [
            'erro' => $e->getMessage(),
            'conta_id' => $conta->id
        ]);

        return back()->with('flash_erro', 'Erro ao enviar mensagem.');

    }
}
public function gerarPix($id)
{
    $conta = ContaReceber::findOrFail($id);
    $config = ConfigEcommerce::where('empresa_id', $conta->empresa_id)->first();

    if (!$config || empty($config->mercadopago_access_token)) {
        return redirect()->back()->with('flash_erro', 'Configuração Mercado Pago não encontrada.');
    }

    SDK::setAccessToken($config->mercadopago_access_token);

    try {
        $payment = new Payment();
        $payment->transaction_amount = floatval($conta->valor_integral);
        $payment->description = "Pagamento conta a receber: " . $conta->referencia;
        $payment->payment_method_id = "pix";
        $payment->payer = [
            "email" => $conta->cliente->email ?? "cliente@example.com",
        ];

        $payment->save();

        if (in_array($payment->status, ['approved', 'pending'])) {
            $qrCode = $payment->point_of_interaction->transaction_data->qr_code;
            $qrCodeBase64 = $payment->point_of_interaction->transaction_data->qr_code_base64;

            return view('conta_receber.pix', compact('conta', 'qrCode', 'qrCodeBase64'));
        }

        return redirect()->back()->with('flash_erro', 'Erro ao gerar pagamento PIX: ' . $payment->status);

    } catch (\Exception $e) {
        \Log::error('Erro ao gerar PIX: ' . $e->getMessage());
        return redirect()->back()->with('flash_erro', 'Erro ao gerar PIX: ' . $e->getMessage());
    }
}

public function formRateioGrupo()
{
    $user = session('user_logged');
    $empresa_id = is_object($user) ? $user->empresa_id : $user['empresa'];

    $grupos = GrupoCliente::where('empresa_id', $empresa_id)
        ->orderBy('nome')
        ->get();

    $categorias = CategoriaConta::where('empresa_id', $empresa_id)
        ->where('tipo', 'receber')
        ->orderBy('nome')
        ->get();

    return view('conta_receber.rateio-grupo', compact('grupos', 'categorias', 'empresa_id'));
}

public function getClientesGrupo($grupo_id)
{
    $user = session('user_logged');
    $empresa_id = is_object($user) ? $user->empresa_id : $user['empresa'];

    return Cliente::where('grupo_id', $grupo_id)
        ->where('empresa_id', $empresa_id)
        ->select('id', 'razao_social')
        ->get();
}

public function storeRateioGrupo(Request $request)
{
    $request->validate([
        'valor_total' => 'required',
        'parcelas' => 'required|integer|min:1',
        'data_vencimento' => 'required|date',
        'categoria_id' => 'required',
        'tipo_pagamento' => 'required', // Agora é obrigatório
        'grupo_id' => 'required',
        'clientes' => 'required|array'
    ]);

    $user = session('user_logged');
    $empresa_id = is_object($user) ? $user->empresa_id : $user['empresa'];

    DB::beginTransaction();

    try {
        $valorTotalGeral = __convert_value_bd($request->valor_total);
        $qtdParcelas = (int)$request->parcelas;

        foreach ($request->clientes as $c) {
            $percentual = (float) ($c['percentual'] ?? 0);

            if ($percentual <= 0) continue;

            // Valor que este cliente pagará no total
            $valorTotalCliente = round(($valorTotalGeral * $percentual) / 100, 2);

            // Valor de cada parcela
            $valorParcela = round($valorTotalCliente / $qtdParcelas, 2);
            
            // Ajuste de centavos na última parcela
            $valorUltimaParcela = $valorTotalCliente - ($valorParcela * ($qtdParcelas - 1));

            for ($i = 0; $i < $qtdParcelas; $i++) {
                // Carbon para manipular data
                $vencimento = \Carbon\Carbon::parse($request->data_vencimento)->addMonths($i);

                $valorFinal = ($i == $qtdParcelas - 1) ? $valorUltimaParcela : $valorParcela;

                ContaReceber::create([
                    'cliente_id'      => $c['id'],
                    'grupo_id'        => $request->grupo_id,
                    'empresa_id'      => $empresa_id,
                    'categoria_id'    => $request->categoria_id,
                    'valor_integral'  => $valorFinal,
                    'valor_recebido'  => 0,
                    'data_vencimento' => $vencimento->format('Y-m-d'),
                    'status'          => 'pendente',
                    'tipo_pagamento'  => $request->tipo_pagamento, // Pegando da View
                    'observacao'      => ($request->observacao_geral ?? "Rateio GRUPO") . " - Parcela " . ($i + 1) . "/$qtdParcelas",
                   'referencia'      => ($request->observacao_geral ?? "Rateio GRUPO") . " - Parcela " . ($i + 1) . "/$qtdParcelas",
                    'date_register'   => date('Y-m-d'),
                ]);
            }
        }

        DB::commit();
        return redirect()->route('conta-receber.index')->with('flash_sucesso', '✅ Rateio gerado com sucesso!');

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('flash_erro', 'Erro ao salvar: ' . $e->getMessage())->withInput();
    }
}


public function gerarCarne(Request $request, $referencia)
{
    // Pega cliente_id da query string (opcional)
    $clienteId = $request->query('cliente_id', null);

    // Define local para meses em português
    Carbon::setLocale('pt_BR');

    // Busca parcelas pendentes filtrando referência e, se informado, cliente_id
    $parcelas = ContaReceber::where('referencia', $referencia)
        ->when($clienteId, function($query, $clienteId) {
            return $query->where('cliente_id', $clienteId);
        })
        ->where('status', 'pendente')
        ->orderBy('data_vencimento')
        ->get();

    if ($parcelas->isEmpty()) {
        return back()->with('error', 'Não existem parcelas pendentes.');
    }

    $primeira = $parcelas->first();
    $empresa = Empresa::find($primeira->empresa_id);
    $cidade = Cidade::find($empresa->cidade_id ?? null);
//dd($empresa);
$config = ConfigNota::where('empresa_id', $empresa->id ?? null)->first();
//dd($config);

    if (!$empresa) {
        return back()->with('error', 'Empresa não encontrada.');
    }

    // 4. Define cliente
    if ($clienteId) {
        $cliente = Cliente::find($clienteId);
        if (!$cliente) {
            return back()->with('error', 'Cliente não encontrado.');
        }
    } else {
        $cliente = $primeira->cliente ?? null;
    }

    $chavePix = preg_replace('/[^0-9]/', '', $empresa->cpf_cnpj);

    // 5. Gera payload PIX e QR Code para cada parcela
    foreach ($parcelas as $parcela) {
        $carbonVenc = Carbon::parse($parcela->data_vencimento);
        $dataVencFormatada = $carbonVenc->format('d/m/Y');

        $descricaoComprovante = "Venc: {$dataVencFormatada} - Ref: {$parcela->referencia}";

        // Mês abreviado em português e limpo
        $mesAbreviado = strtoupper($carbonVenc->translatedFormat('M'));
        $mesLimpo = preg_replace('/[^A-Z]/', '', $mesAbreviado);

        $identificador = $mesLimpo . $carbonVenc->format('Y') . "ID" . $parcela->id;

        // Gera payload PIX
        $payload = $this->gerarPixPayload(
            $chavePix,
            $empresa->razao_social,
            $cidade->nome ?? 'SAO PAULO',
            $parcela->valor_integral,
            $descricaoComprovante,
            $identificador
        );

        $parcela->pix_payload = $payload;

        // Gera QR Code base64
        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($payload);
        try {
            $image = @file_get_contents($qrUrl);
            $parcela->qrcode = $image ? base64_encode($image) : null;
        } catch (\Exception $e) {
            $parcela->qrcode = null;
        }
    }

    // 6. Renderiza PDF usando view
    $pdf = Pdf::loadView(
        'conta_receber.carne_pdf',
        compact('cliente', 'empresa', 'cidade', 'parcelas','config')
    )
    ->setPaper('a4')
    ->setOption('isRemoteEnabled', true);

    return $pdf->stream("carne-{$referencia}.pdf");
}

// ---------------------------
// Métodos auxiliares
// ---------------------------
private function gerarPixPayload($chavePix, $nome, $cidade, $valor, $descricao = '', $txid = '***')
{
    $valor = number_format($valor, 2, '.', '');
    $nome = $this->limparString(substr($nome, 0, 25));
    $cidade = $this->limparString(substr($cidade, 0, 15));
    $descricao = $this->limparString(substr($descricao, 0, 40));
    $txid = $this->limparString(substr($txid, 0, 25));

    $payload = "000201";

    $gui = "0014BR.GOV.BCB.PIX";
    $key = "01" . sprintf("%02d", strlen($chavePix)) . $chavePix;
    $infoAdicional = !empty($descricao) ? "02" . sprintf("%02d", strlen($descricao)) . $descricao : "";
    $merchantAccount = $gui . $key . $infoAdicional;

    $payload .= "26" . sprintf("%02d", strlen($merchantAccount)) . $merchantAccount;
    $payload .= "52040000";
    $payload .= "5303986";
    $payload .= "54" . sprintf("%02d", strlen($valor)) . $valor;
    $payload .= "5802BR";
    $payload .= "59" . sprintf("%02d", strlen($nome)) . $nome;
    $payload .= "60" . sprintf("%02d", strlen($cidade)) . $cidade;

    $txidField = "05" . sprintf("%02d", strlen($txid)) . $txid;
    $payload .= "62" . sprintf("%02d", strlen($txidField)) . $txidField;

    $payload .= "6304";
    $crc = strtoupper(dechex($this->crc16($payload)));
    $payload .= str_pad($crc, 4, '0', STR_PAD_LEFT);

    return $payload;
}

private function limparString($string)
{
    $string = preg_replace(
        array("/[ÁÀÂÃÄ]/u", "/[ÉÈÊË]/u", "/[ÍÌÎÏ]/u", "/[ÓÒÔÕÖ]/u", "/[ÚÙÛÜ]/u", "/[Ç]/u"),
        array("A","E","I","O","U","C"),
        mb_strtoupper($string, 'UTF-8')
    );

    return preg_replace('/[^A-Z0-9\s\-\:\.\/]/', '', $string);
}
    private function crc16($payload)
    {
        $polinomio = 0x1021;
        $resultado = 0xFFFF;
        for ($i = 0; $i < strlen($payload); $i++) {
            $resultado ^= (ord($payload[$i]) << 8);
            for ($j = 0; $j < 8; $j++) {
                if ($resultado & 0x8000) {
                    $resultado = ($resultado << 1) ^ $polinomio;
                } else {
                    $resultado <<= 1;
                }
                $resultado &= 0xFFFF;
            }
        }
        return $resultado;
    }

public function dashboard(Request $request)
{
    $user = session('user_logged');

    if (!$user) abort(403, 'Usuário não logado');

    $empresa_id = is_object($user)
        ? $user->empresa_id
        : ($user['empresa'] ?? null);

    if (!$empresa_id) abort(403, 'Empresa não encontrada');

    $mes = $request->mes ?? now()->month;
    $ano = $request->ano ?? now()->year;

    /* ================= BASE ================= */
    $receberQuery = ContaReceber::where('empresa_id', $empresa_id);
    $pagarQuery   = ContaPagar::where('empresa_id', $empresa_id);

    /* ================= MÉTRICAS ================= */
    $recebidoMes = (clone $receberQuery)
        ->where('status', 1)
        ->whereMonth('data_recebimento', $mes)
        ->whereYear('data_recebimento', $ano)
        ->sum('valor_recebido');

    $pagoMes = (clone $pagarQuery)
        ->where('status', 1)
        ->whereMonth('data_pagamento', $mes)
        ->whereYear('data_pagamento', $ano)
        ->sum('valor_pago');

    $totalReceber = (clone $receberQuery)->sum('valor_integral');
    $totalPagar   = (clone $pagarQuery)->sum('valor_integral');

    $saldoMes = $recebidoMes - $pagoMes;
    $fluxoPrevisto = $totalReceber - $totalPagar;

    /* ================= INADIMPLÊNCIA ================= */
    $vencido = (clone $receberQuery)
        ->where('status', 0)
        ->whereDate('data_vencimento', '<', now())
        ->sum('valor_integral');

    $inadimplencia = $totalReceber > 0
        ? round(($vencido / $totalReceber) * 100, 2)
        : 0;

    /* ================= GRÁFICO MENSAL ================= */
    $receberMensal = [];
    $pagarMensal = [];

    for ($i = 1; $i <= 12; $i++) {

        $receberMensal[] = (clone $receberQuery)
            ->where('status', 1)
            ->whereMonth('data_recebimento', $i)
            ->whereYear('data_recebimento', $ano)
            ->sum('valor_recebido');

        $pagarMensal[] = (clone $pagarQuery)
            ->where('status', 1)
            ->whereMonth('data_pagamento', $i)
            ->whereYear('data_pagamento', $ano)
            ->sum('valor_pago');
    }

    /* ================= ACUMULADO ================= */
    $fluxoAcumulado = [];
    $acumulado = 0;

    for ($i = 1; $i <= 12; $i++) {

        $r = (clone $receberQuery)
            ->where('status', 1)
            ->whereMonth('data_recebimento', $i)
            ->whereYear('data_recebimento', $ano)
            ->sum('valor_recebido');

        $p = (clone $pagarQuery)
            ->where('status', 1)
            ->whereMonth('data_pagamento', $i)
            ->whereYear('data_pagamento', $ano)
            ->sum('valor_pago');

        $acumulado += ($r - $p);

        $fluxoAcumulado[] = $acumulado;
    }

    /* ================= CATEGORIA ================= */
    $graficoCategoria = ContaReceber::where('empresa_id', $empresa_id)
        ->where('status', 1)
        ->whereMonth('data_recebimento', $mes)
        ->whereYear('data_recebimento', $ano)
        ->with('categoria')
        ->get()
        ->groupBy('categoria_id')
        ->map(function ($items) {
            return [
                'label' => $items->first()->categoria->nome ?? 'Sem categoria',
                'total' => $items->sum('valor_recebido')
            ];
        })
        ->values();

    /* ================= GRUPO ================= */
    $graficoGrupo = ContaReceber::where('empresa_id', $empresa_id)
        ->where('status', 1)
        ->whereMonth('data_recebimento', $mes)
        ->whereYear('data_recebimento', $ano)
        ->with('grupo')
        ->get()
        ->groupBy('grupo_id')
        ->map(function ($items) {
            return [
                'label' => $items->first()->grupo->nome ?? 'Sem grupo',
                'total' => $items->sum('valor_recebido')
            ];
        })
        ->values();

    return view('conta_receber.dashboard', compact(
        'mes',
        'ano',
        'recebidoMes',
        'pagoMes',
        'totalReceber',
        'totalPagar',
        'saldoMes',
        'fluxoPrevisto',
        'inadimplencia',
        'receberMensal',
        'pagarMensal',
        'fluxoAcumulado',
        'graficoCategoria',
        'graficoGrupo'
    ));
}
    }