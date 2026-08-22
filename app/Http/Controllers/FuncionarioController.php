<?php

namespace App\Http\Controllers;

use App\Models\Cidade;
use App\Models\Funcionario;
use App\Models\Usuario;
use App\Models\ComissaoVenda;

use Illuminate\Http\Request;
use App\Rules\ValidaDocumento;
use Illuminate\Support\Facades\DB;


class FuncionarioController extends Controller
{

    public function index(Request $request)
    {
        $data = Funcionario::where('empresa_id', request()->empresa_id)
            ->when(!empty($request->nome), function ($q) use ($request) {
                return  $q->where(function ($quer) use ($request) {
                    return $quer->where('nome', 'LIKE', "%$request->nome%");
                });
            })
            ->when(!empty($request->cpf), function ($q) use ($request) {
                return  $q->where(function ($quer) use ($request) {
                    return $quer->where('cpf', 'LIKE', "%$request->cpf%");
                });
            })
            ->paginate(env("PAGINACAO"));
        return view('funcionarios.index', compact('data'));
    }

    public function create()
    {
        $usuarios = Usuario::where('empresa_id', request()->empresa_id);
        $cidades = Cidade::all();
        return view('funcionarios.create', compact('cidades', 'usuarios'));
    }

    public function store(Request $request)
    {
        $this->_validate($request);
        try {
            $request->merge([
                'salario' => __convert_value_bd($request->salario),
                'email' => $request->email ?? '',
                'percentual_comissao' => $request->percentual_comissao ? __convert_value_bd($request->percentual_comissao) : 0,
                'usuario_id' => $request->usuario_id ?? null
            ]);
            DB::transaction(function () use ($request) {
                Funcionario::create($request->all());
            });
            session()->flash("flash_sucesso", "Funcionário cadastrado!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('funcionarios.index');
    }

    private function _validate(Request $request)
    {
        $rules = [
            'nome' => 'required|max:80',
            'cpf' => ['required'],
            'rua' => 'required|max:80',
            'numero' => 'required|max:10',
            'bairro' => 'required|max:50',
            'telefone' => 'required|max:20',
            'celular' => 'required|max:20',
            'email' => 'max:40',
            'cidade_id' => 'required',
            'rg' => 'required',
            'salario' => 'required',
            'data_registro' => 'required',
            'data_nasci' => 'required'

        ];
        $messages = [
            'nome.required' => 'Nome é obrigatório.',
            'nome.max' => 'Nome no máximo 80 caracter.',
            'rua.required' => 'O campo Rua é obrigatório.',
            'rua.max' => '80 caracteres maximos permitidos.',
            'numero.required' => 'O campo Numero é obrigatório.',
            'cidade_id.required' => 'O campo Cidade é obrigatório.',
            'numero.max' => '10 caracteres maximos permitidos.',
            'bairro.required' => 'O campo Bairro é obrigatório.',
            'bairro.max' => '50 caracteres maximos permitidos.',
            'telefone.required' => 'O campo Telefone é obrigatório.',
            'telefone.max' => '20 caracteres maximos permitidos.',
            'celular.required' => 'Campo Obrigatório',
            'celular.max' => '20 caracteres maximos permitidos.',
            'email.required' => 'O campo Email é obrigatório.',
            'email.max' => '40 caracteres maximos permitidos.',
            'email.email' => 'Email inválido.',
            'rg.required' => 'Campo Obrigatório',
            'cpf.required' => 'Campo Obrigatório',
            'salario.required' => 'Campo Obrigatório',
            'data_registro.required' => 'Escolha uma Data',
            'data_nasci.required' => 'Escolha uma Data'

        ];
        $this->validate($request, $rules, $messages);
    }

    public function edit($id)
    {
        $item = Funcionario::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        $usuarios = Usuario::where('empresa_id', request()->empresa_id)->get();
        $cidades = Cidade::all();
        return view('funcionarios.edit', compact('item', 'usuarios', 'cidades'));
    }

    public function update(Request $request, $id)
    {
        $item = Funcionario::findOrFail($id);
        try {
            $request->merge([
                'salario' => __convert_value_bd($request->salario),
                'email' => $request->email ?? '',
                'percentual_comissao' => $request->percentual_comissao ? __convert_value_bd($request->percentual_comissao) : 0,
                'usuario_id' => $request->usuario_id ?? null
            ]);
            DB::transaction(function () use ($request, $item) {
                $item->fill($request->all())->save();
            });
            session()->flash("flash_sucesso", "Cadastro atualizado!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('funcionarios.index');
    }
public function comissao(Request $request)
{
    $empresa_id = $request->empresa_id;

    // 1. Lista de Vendedores para o Select
    $vendedor = Funcionario::where('empresa_id', $empresa_id)
        ->orderBy('nome', 'asc')
        ->get();

    // 2. Inicia a Query Base com filtros
    $query = ComissaoVenda::with(['funcionario', 'valodDaVenda'])
        ->where('empresa_id', $empresa_id);

    // 3. Aplica os filtros (Sintaxe PHP 8.2)
    $query->when($request->filled('nome'), fn($q) => $q->where('funcionario_id', $request->nome))
          ->when($request->filled('data_inicial'), fn($q) => $q->whereDate('created_at', '>=', $request->data_inicial))
          ->when($request->filled('data_final'), fn($q) => $q->whereDate('created_at', '<=', $request->data_final))
          ->when($request->filled('estado'), fn($q) => $q->where('status', $request->estado));

    // 4. Cálculos de Totais (Usando clones da query filtrada)
    // Total Geral (Independente de status, mas respeitando datas/nome)
    $totalGeral = (float) (clone $query)->sum('valor');

    // Total Pago (Status 0 - ajuste conforme seu banco: se 0 for pago ou 1)
    $totalPago = (float) (clone $query)->where('status', 1)->sum('valor');

    // Total Pendente (Status 1)
    $totalPendente = (float) (clone $query)->where('status', 0)->sum('valor');

    // 5. Busca os resultados finais
    $comissoes = $query->orderBy('created_at', 'desc')->get();

    return view('funcionarios.comissao', [
        'vendedor'      => $vendedor,
        'comissoes'     => $comissoes,
        'totalGeral'    => $totalGeral,
        'totalPago'     => $totalPago,
        'totalPendente' => $totalPendente
    ]);
}
 public function pagarComissao(Request $request)
{
    try {
        $query = DB::table('comissao_vendas')
            ->where('empresa_id', $request->empresa_id)
            ->where('status', 0); // Paga apenas as PENDENTES

        // Se houver um vendedor selecionado, paga só dele
        if ($request->filled('nome')) {
            $query->where('funcionario_id', $request->nome);
        }

        // Se houver filtro de data no request, aplica ao pagamento também
        if ($request->filled('data_inicial')) {
            $query->whereDate('created_at', '>=', $request->data_inicial);
        }
        if ($request->filled('data_final')) {
            $query->whereDate('created_at', '<=', $request->data_final);
        }

        $query->update([
            'status' => 1, // Muda para Pago
            'updated_at' => now()
        ]);

        session()->flash("flash_sucesso", "Comissões baixadas com sucesso!");
    } catch (\Exception $e) {
        session()->flash("flash_erro", "Erro ao processar pagamento: " . $e->getMessage());
    }
    return redirect()->back();
} public function destroy(Request $request, $id)
    {
        $item = Funcionario::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        try {
            $item->delete();
            session()->flash("flash_sucesso", "Deletado com Sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Deletado com Sucesso!" . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('funcionarios.index');
    }

    public function show()
    {
        $vendedor = Funcionario::where('empresa_id', request()->empresa_id)->get();
        if (!__valida_objeto($vendedor)) {
            abort(403);
        }
        return view('funcionarios.comissao', compact('vendedor'));
    }
}
