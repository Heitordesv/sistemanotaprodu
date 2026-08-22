<?php

namespace App\Http\Controllers;

use App\Http\Middleware\LimiteClientes;
use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\ConfigNota;
use App\Models\ApiBrasil;
use App\Models\Pais;
use App\Models\Cidade;
use App\Models\GrupoCliente;
use App\Models\Acessor;
use App\Models\Funcionario;
use Illuminate\Support\Facades\DB;
use App\Rules\ValidaDocumento;
use App\Utils\UploadUtil;
use App\Imports\ProdutoImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Jobs\EnviarMensagemWhatsAppCliente;

class ClienteController extends Controller
{
    protected $util;

    public function __construct(UploadUtil $util)
    {
        $this->middleware(LimiteClientes::class)->only('create');
        $this->util = $util;
    }

    public function index(Request $request)
    {
        $data = Cliente::where('empresa_id', request()->empresa_id)
            ->when(!empty($request->razao_social), function ($q) use ($request) {
                return $q->where('razao_social', 'LIKE', "%$request->razao_social%");
            })
            ->when(!empty($request->cpf_cnpj), function ($q) use ($request) {
                return $q->where('cpf_cnpj', 'LIKE', "%$request->cpf_cnpj%");
            })
            ->paginate(env("PAGINACAO"));
        return view('clientes/index', compact('data'));
    }
public function atualizarLimite(Request $request)
{
    try {
        $cliente = Cliente::findOrFail($request->cliente_id);
        
        // Validação
        if ($request->novo_limite < 0) {
            return response()->json(['status' => 'error', 'message' => 'Valor inválido'], 400);
        }

        $cliente->limite_venda = $request->novo_limite;
        $cliente->save();

        return response()->json([
            'status' => 'success', 
            'message' => 'Limite atualizado com sucesso!',
            'novo_limite' => $cliente->limite_venda
        ]);

    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
}
    public function create()
    {
        $paises = Pais::all();
        $grupos = GrupoCliente::where('empresa_id', request()->empresa_id)->get();
        $acessores = Acessor::where('empresa_id', request()->empresa_id)->get();
        $funcionarios = Funcionario::where('empresa_id', request()->empresa_id)->get();

        return view('clientes/create', compact('paises', 'grupos', 'acessores', 'funcionarios'));
    }

  public function store(Request $request)
{
    $this->_validate($request);
    try {
        $file_name = '';
        if ($request->hasFile('image')) {
            $file_name = $this->util->uploadImage($request, '/clients', 'image');
        }

        $request->merge([
            // GARANTE QUE NÃO SEJA NULO SE O BANCO NÃO PERMITIR
            'cpf_cnpj' => $request->cpf_cnpj ?? '', 
            'limite_venda' => $request->limite_venda ? __convert_value_bd($request->limite_venda) : 0,
            'ie_rg' => $request->ie_rg ?? '',
            'observacao' => $request->observacao ?? '',
            'imagem' => $file_name,
            'nome_fantasia' => $request->nome_fantasia ?? '',
            'acessor_id' => $request->acessor_id ?? 0,
            'grupo_id' => $request->grupo_id ?? 0
        ]);

        $cliente = null;
        DB::transaction(function () use ($request, &$cliente) {
            $cliente = Cliente::create($request->all());
        });

        // ... resto do seu código de WhatsApp ...

        session()->flash("flash_sucesso", "Cliente cadastrado!");
    } catch (\Exception $e) {
        session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
        __saveLogError($e, request()->empresa_id);
    }
    return redirect()->route('clientes.index');
}

    public function update(Request $request, $id)
    {
        $this->_validate($request);
        $item = Cliente::findOrFail($id);

        try {
            // Atualiza a imagem caso tenha sido enviada
            $file_name = $item->imagem;
            if ($request->hasFile('image')) {
                $this->util->unlinkImage($item, '/clients', 'image');
                $file_name = $this->util->uploadImage($request, '/clients', 'image');
            }

            // Atualiza os dados do cliente
            $request->merge([
                'limite_venda' => __convert_value_bd($request->limite_venda),
                'ie_rg' => $request->ie_rg ?? '',
                'imagem' => $file_name,
                'nome_fantasia' => $request->nome_fantasia ?? '',
                'acessor_id' => $request->acessor_id ?? 0,
                'grupo_id' => $request->grupo_id ?? 0,
                'observacao' => $request->observacao ?? '',
            ]);

            DB::transaction(function () use ($request, $item) {
                $item->fill($request->all())->save();
            });

            // Buscar configuração da empresa ApiBrasil (usar mesma fonte que no store)
            $apiBrasil = ApiBrasil::where('empresa_id', request()->empresa_id)->first();

            if ($apiBrasil && $apiBrasil->DeviceToken && $apiBrasil->Bearer && $item->celular) {
                $numero = preg_replace('/\D/', '', $item->celular);
                $numero = '55' . $numero;

                EnviarMensagemWhatsAppCliente::dispatch(
                    $apiBrasil->empresa_id,
                    $numero,
                    "Olá {$item->razao_social}, seu cadastro foi *atualizado* com sucesso! Obrigado por continuar conosco! {$apiBrasil->nome_fantasia}"
                );
            }

            session()->flash("flash_sucesso", "Cliente atualizado!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }

        return redirect()->route('clientes.index');
    }

    public function edit($id)
    {
        $item = Cliente::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }

        $cidades = Cidade::all();
        $paises = Pais::all();
        $grupos = GrupoCliente::where('empresa_id', request()->empresa_id)->get();
        $acessores = Acessor::where('empresa_id', request()->empresa_id)->get();
        $funcionarios = Funcionario::where('empresa_id', request()->empresa_id)->get();

        return view('clientes/edit', compact('cidades', 'paises', 'grupos', 'acessores', 'funcionarios', 'item'));
    }

    private function _validate(Request $request)
    {
        $doc = $request->cpf_cnpj;
        $rules = [
            'razao_social' => 'required|max:80',
'cpf_cnpj' => 'nullable',
'rua' => 'required|max:80',
            'numero' => 'required|max:10',
            'bairro' => 'required|max:50',
            'telefone' => 'max:20',
            'celular' => 'max:20',
            'email' => 'max:40',
            'cep' => 'required|min:9',
            'cidade_id' => 'required',
            'consumidor_final' => 'required',
            'contribuinte' => 'required',
            'rua_cobranca' => 'max:80',
            'numero_cobranca' => 'max:10',
            'bairro_cobranca' => 'max:50',
            'cep_cobranca' => 'max:9'
        ];
        $messages = [
            'razao_social.required' => 'O Razão social/Nome é obrigatório.',
            'razao_social.max' => '50 caracteres maximos permitidos.',
            'cpf_cnpj.required' => 'O campo CPF/CNPJ é obrigatório.',
            'cpf_cnpj.min' => strlen($doc) > 14 ? 'Informe 14 números para CNPJ.' : 'Informe 14 números para CPF.',
            'rua.required' => 'O campo Rua é obrigatório.',
            'rua.max' => '80 caracteres maximos permitidos.',
            'numero.required' => 'O campo Numero é obrigatório.',
            'cep.required' => 'O campo CEP é obrigatório.',
            'cep.min' => 'CEP inválido.',
            'cidade_id.required' => 'O campo Cidade é obrigatório.',
            'numero.max' => '10 caracteres maximos permitidos.',
            'bairro.required' => 'O campo Bairro é obrigatório.',
            'bairro.max' => '50 caracteres maximos permitidos.',
            'telefone.required' => 'O campo Telefone é obrigatório.',
            'telefone.max' => '20 caracteres maximos permitidos.',
            'consumidor_final.required' => 'O campo Consumidor final é obrigatório.',
            'contribuinte.required' => 'O campo Contribuinte é obrigatório.',
            'celular.max' => '20 caracteres maximos permitidos.',
            'email.required' => 'O campo Email é obrigatório.',
            'email.max' => '40 caracteres maximos permitidos.',
            'email.email' => 'Email inválido.',
            'rua_cobranca.max' => '80 caracteres maximos permitidos.',
            'numero_cobranca.max' => '10 caracteres maximos permitidos.',
            'bairro_cobranca.max' => '30 caracteres maximos permitidos.',
            'cep_cobranca.max' => '9 caracteres maximos permitidos.',
        ];
        $this->validate($request, $rules, $messages);
    }
public function destroy(Request $request, $id)
{
    $cliente = Cliente::findOrFail($id);

    if (!__valida_objeto($cliente)) {
        abort(403);
    }

    DB::beginTransaction();

    try {

        /* -------------------------------------------------------------
         | 1. REMESSAS (remessa_nves)
         -------------------------------------------------------------*/
        foreach ($cliente->remessas as $remessa) {
            if (method_exists($remessa, 'itens')) $remessa->itens()->delete();
            if (method_exists($remessa, 'fatura')) $remessa->fatura()->delete();
            if (method_exists($remessa, 'duplicatas')) $remessa->duplicatas()->delete();
            if (method_exists($remessa, 'referencias')) $remessa->referencias()->delete();
            $remessa->delete();
        }
foreach ($cliente->ordensServico as $os) {

    // Apagar produtos vinculados à ordem de serviço
    if (method_exists($os, 'produtos')) {
        $os->produtos()->delete();
    }

    // Apagar serviços e anexos
    if (method_exists($os, 'servicos')) {
        $os->servicos()->delete();
    }

    if (method_exists($os, 'anexos')) {
        $os->anexos()->delete();
    }

    // Apagar a ordem de serviço
    $os->delete();
}
// Apagar pré-vendas de caixa
foreach ($cliente->vendaCaixaPreVendas as $preVenda) {
    $preVenda->delete();
}

        /* -------------------------------------------------------------
         | 3. CONTAS A RECEBER
         -------------------------------------------------------------*/
        if (method_exists($cliente, 'contasReceber')) $cliente->contasReceber()->delete();

        /* -------------------------------------------------------------
         | 4. ORÇAMENTOS
         -------------------------------------------------------------*/
        foreach ($cliente->orcamentos as $orc) {
            if (method_exists($orc, 'itens')) $orc->itens()->delete();
            $orc->delete();
        }

        /* -------------------------------------------------------------
         | 5. VENDAS DE CAIXA
         -------------------------------------------------------------*/
        foreach ($cliente->vendasCaixa as $vendaCaixa) {
            if (method_exists($vendaCaixa, 'itens')) $vendaCaixa->itens()->delete();
            if (method_exists($vendaCaixa, 'pagamentos')) $vendaCaixa->pagamentos()->delete();
            if (method_exists($vendaCaixa, 'parcelas')) $vendaCaixa->parcelas()->delete();
            $vendaCaixa->delete();
        }

        /* -------------------------------------------------------------
         | 6. VENDAS
         -------------------------------------------------------------*/
        foreach ($cliente->vendas as $venda) {
            if (method_exists($venda, 'itens')) $venda->itens()->delete();
            if (method_exists($venda, 'pagamentos')) $venda->pagamentos()->delete();
            if (method_exists($venda, 'parcelas')) $venda->parcelas()->delete();
            $venda->delete();
        }

      /* -------------------------------------------------------------
 | 7. AGENDAMENTOS + ITENS
 -------------------------------------------------------------*/
if (method_exists($cliente, 'agendamentos')) {
    foreach ($cliente->agendamentos as $agendamento) {

        // Deleta itens do agendamento
        if (method_exists($agendamento, 'itens')) {
            $agendamento->itens()->delete();
        }

        // Deleta o agendamento
        $agendamento->delete();
    }
}


        /* -------------------------------------------------------------
         | 8. DELETAR O CLIENTE
         -------------------------------------------------------------*/
        $cliente->delete();

        DB::commit();

        session()->flash("flash_sucesso", "Cliente removido com sucesso!");

    } catch (\Exception $e) {

        DB::rollBack();

        session()->flash("flash_erro", "Erro ao remover: " . $e->getMessage());
        __saveLogError($e, $request->empresa_id);
    }

    return redirect()->route('clientes.index');
}



    public function import()
    {
        return view('clientes.import');
    }

    public function downloadModelo()
    {
        try {
            return response()->download(public_path('files/') . 'import_clients_csv_template.xlsx');
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    public function importStore(Request $request)
    {
        if ($request->hasFile('file')) {
            ini_set('max_execution_time', 0);
            ini_set('memory_limit', -1);

            $rows = Excel::toArray(new ProdutoImport, $request->file);
            $retornoErro = $this->validaArquivo($rows);

            if ($retornoErro == "") {
                $cont = 0;

                foreach ($rows as $row) {
                    foreach ($row as $r) {
                        if ($r[0] != 'RAZÃO SOCIAL*') {
                            try {
                                $objeto = $this->preparaObjeto($r);
                                Cliente::create($objeto);
                                $cont++;
                            } catch (\Exception $e) {
                                session()->flash('flash_erro', $e->getMessage());
                                return redirect()->back();
                            }
                        }
                    }
                }

                session()->flash('flash_sucesso', "Clientes inseridos: $cont!!");
                return redirect('/clientes');
            } else {
                session()->flash('flash_erro', $retornoErro);
                return redirect()->back();
            }
        } else {
            session()->flash('flash_erro', 'Nenhum Arquivo!!');
            return redirect()->back();
        }
    }

    private function preparaObjeto($row)
    {
        $cid = $row[7];
        $cidade = null;
        if (is_numeric($cid)) {
            $cidade = Cidade::find($cid);
        } else {
            $uf = "";
            $temp = explode("-", $cid);
            if (isset($temp[1])) {
                $uf = $temp[1];
                $cid = $temp[0];
            }
            if ($uf != "") {
                $cidade = DB::select("select * from cidades where nome = ? and uf = ?", [$cid, $uf]);
                if (!$cidade) {
                    $cidade = DB::select("select * from cidades where nome like ? and uf = ?", ["%$cid%", $uf]);
                }
            } else {
                $cidade = DB::select("select * from cidades where nome = ?", [$cid]);
                if (!$cidade) {
                    $cidade = DB::select("select * from cidades where nome like ?", ["%$cid%"]);
                }
            }
            $cidade = $cidade ? $cidade[0]->id : null;
        }

        $doc = $this->adicionaMascara($row[2]);
        $ie = $row[3] ?? '';

        return [
            'razao_social' => $row[0],
            'nome_fantasia' => $row[1] ?? $row[0],
            'bairro' => $row[6],
            'numero' => $row[5],
            'rua' => $row[4],
            'cpf_cnpj' => $doc,
            'telefone' => $row[8] ?? '',
            'celular' => $row[9] ?? '',
            'email' => $row[10] ?? '',
            'cep' => $row[11],
            'ie_rg' => $ie,
            'consumidor_final' => 1,
            'limite_venda' => $row[12] != "" ? __replace($row[12]) : 0,
            'cidade_id' => $cidade ?? 1,
            'contribuinte' => ($ie == '' || strtoupper($ie) == 'ISENTO') ? false : true,
            'rua_cobranca' => '',
            'numero_cobranca' => '',
            'bairro_cobranca' => '',
            'cep_cobranca' => '',
            'cidade_cobranca_id' => NULL,
            'empresa_id' => request()->empresa_id,
            'contador_nome' => $row[13] ?? '',
            'contador_telefone' => $row[14] ?? '',
            'contador_email' => $row[15] ?? '',
        ];
    }

    private function adicionaMascara($doc)
    {
        if (strlen($doc) == 14) {
            return substr($doc, 0, 2) . "." .
                substr($doc, 2, 3) . "." .
                substr($doc, 5, 3) . "/" .
                substr($doc, 8, 4) . "-" .
                substr($doc, 12, 2);
        } else {
            return substr($doc, 0, 3) . "." .
                substr($doc, 3, 3) . "." .
                substr($doc, 6, 3) . "-" .
                substr($doc, 9, 2);
        }
    }

    private function validaArquivo($rows)
    {
        $cont = 0;
        $msgErro = "";
        foreach ($rows as $row) {
            foreach ($row as $r) {
                $razaoSocial = $r[0];
                $cnpj = $r[2];
                $ie = $r[3];
                $rua = $r[4];
                $numero = $r[5];
                $bairro = $r[6];
                $cidade = $r[7];
                $cep = $r[11];

                if (strlen($razaoSocial) == 0) $msgErro .= "Coluna razão social em branco na linha: $cont | ";
                if (strlen($cnpj) == 0) $msgErro .= "Coluna cnpj/cpf em branco na linha: $cont | ";
                if (strlen($ie) == 0) $msgErro .= "Coluna ie/rg em branco na linha: $cont | ";
                if (strlen($rua) == 0) $msgErro .= "Coluna rua em branco na linha: $cont | ";
                if (strlen($numero) == 0) $msgErro .= "Coluna numero em branco na linha: $cont | ";
                if (strlen($bairro) == 0) $msgErro .= "Coluna bairro em branco na linha: $cont | ";
                if (strlen($cidade) == 0) $msgErro .= "Coluna cidade em branco na linha: $cont | ";
                if (strlen($cep) == 0) $msgErro .= "Coluna cep em branco na linha: $cont | ";

                if ($msgErro != "") return $msgErro;

                $cont++;
            }
        }
        return $msgErro;
    }
}
