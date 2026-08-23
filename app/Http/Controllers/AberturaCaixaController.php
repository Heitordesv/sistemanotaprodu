<?php

namespace App\Http\Controllers;

use App\Models\AberturaCaixa;
use App\Models\ConfigNota;
use App\Models\Usuario;
use App\Models\Venda;
use App\Models\VendaCaixa;
use App\Services\CaixaResumoService;
use Dompdf\Dompdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AberturaCaixaController extends Controller
{
    protected $empresa_id = null;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->empresa_id = $request->empresa_id;

            if (!session('user_logged')) {
                return redirect('/login');
            }

            return $next($request);
        });
    }

    public function store(Request $request)
    {
        $usuarioId = (int) get_id_user();
        $empresaId = (int) $this->empresa_id;

        $filialInformada = $request->input('filial_id');
        if ($filialInformada === null || $filialInformada === '' || (string) $filialInformada === '-1') {
            $request->merge(['filial_id' => null]);
        }

        $validated = $request->validate([
            'valor' => [
                'required',
                function ($attribute, $value, $fail) {
                    $valor = trim((string) $value);
                    $formatoValido = preg_match(
                        '/^(?:(?:\d{1,3}(?:\.\d{3})+|\d+)(?:,\d{1,2})?|\d+(?:\.\d{1,2})?)$/',
                        $valor
                    );

                    if (!$formatoValido) {
                        $fail('O valor de abertura deve ser um valor monetário válido e maior ou igual a zero.');
                        return;
                    }

                    $normalizado = __convert_value_bd($valor);
                    if (!is_numeric($normalizado) || !is_finite((float) $normalizado) || (float) $normalizado < 0) {
                        $fail('O valor de abertura deve ser um valor monetário válido e maior ou igual a zero.');
                    }
                },
            ],
            'filial_id' => [
                'nullable',
                'integer',
                Rule::exists('filials', 'id')->where(function ($query) use ($empresaId) {
                    return $query->where('empresa_id', $empresaId);
                }),
            ],
        ], [
            'filial_id.integer' => 'A filial informada é inválida.',
            'filial_id.exists' => 'A filial informada não pertence à empresa atual.',
        ]);

        $valorAbertura = round((float) __convert_value_bd($validated['valor']), 2);
        $filialId = isset($validated['filial_id']) ? (int) $validated['filial_id'] : null;

        try {
            $abertura = DB::transaction(function () use ($usuarioId, $empresaId, $valorAbertura, $filialId) {
                Usuario::query()
                    ->where('id', $usuarioId)
                    ->where('empresa_id', $empresaId)
                    ->lockForUpdate()
                    ->firstOrFail();

                $aberturaExistente = AberturaCaixa::query()
                    ->where('empresa_id', $empresaId)
                    ->where('usuario_id', $usuarioId)
                    ->where('status', 0)
                    ->lockForUpdate()
                    ->orderByDesc('id')
                    ->first();

                if ($aberturaExistente) {
                    throw new \DomainException('Você já possui o Caixa #' . $aberturaExistente->id . ' aberto.');
                }

                $ultimaVendaNfceId = (int) (VendaCaixa::query()
                    ->where('empresa_id', $empresaId)
                    ->where('usuario_id', $usuarioId)
                    ->max('id') ?? 0);

                $ultimaVendaNfeId = (int) (Venda::query()
                    ->where('empresa_id', $empresaId)
                    ->where('usuario_id', $usuarioId)
                    ->max('id') ?? 0);

                return AberturaCaixa::create([
                    'usuario_id' => $usuarioId,
                    'valor' => $valorAbertura,
                    'empresa_id' => $empresaId,
                    'primeira_venda_nfe' => $ultimaVendaNfeId,
                    'primeira_venda_nfce' => $ultimaVendaNfceId,
                    'status' => 0,
                    'filial_id' => $filialId,
                ]);
            });

            session()->flash('flash_sucesso', 'Caixa #' . $abertura->id . ' aberto com sucesso para o seu usuário!');
        } catch (\DomainException $e) {
            session()->flash('flash_warning', $e->getMessage());
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Não foi possível abrir o caixa. Tente novamente.');
            __saveLogError($e, $empresaId);
        }

        return redirect()->back();
    }

    private function verificaAberturaCaixa()
    {
        return AberturaCaixa::query()
            ->where('empresa_id', $this->empresa_id)
            ->where('usuario_id', get_id_user())
            ->where('status', 0)
            ->orderByDesc('id')
            ->first();
    }

    public function index()
    {
        $usuarioId = (int) get_id_user();

        $config = ConfigNota::where('empresa_id', $this->empresa_id)->first();
        if (!$config) {
            session()->flash('flash_erro', 'Configure o emitente');
            return redirect()->route('configNF.index');
        }

        $abertura = AberturaCaixa::with(['usuario', 'filial'])
            ->where('empresa_id', $this->empresa_id)
            ->where('usuario_id', $usuarioId)
            ->where('status', 0)
            ->orderByDesc('id')
            ->first();

        $caixa = $abertura ? $this->dadosDaAbertura($abertura) : [];
        $usuario = Usuario::findOrFail($usuarioId);

        return view('caixa.index', compact('config', 'abertura', 'caixa', 'usuario'));
    }

    private function dadosDaAbertura(AberturaCaixa $abertura): array
    {
        return app(CaixaResumoService::class)->resumir($abertura);
    }

    private function dadosSessaoLogada(): array
    {
        $user = session('user_logged');

        if (!$user) {
            abort(401, 'Sessão expirada.');
        }

        return [
            'empresa_id' => (int) (is_object($user) ? $user->empresa_id : $user['empresa']),
            'usuario_id' => (int) (is_object($user) ? $user->id : $user['id']),
            'is_admin' => (bool) (
                (is_object($user) ? ($user->adm ?? false) : ($user['adm'] ?? false))
                || (is_object($user) ? ($user->super ?? false) : ($user['super'] ?? false))
            ),
        ];
    }

    private function aberturaAutorizada(int $id): AberturaCaixa
    {
        $sessao = $this->dadosSessaoLogada();

        return AberturaCaixa::with(['usuario', 'filial'])
            ->where('id', $id)
            ->where('empresa_id', $sessao['empresa_id'])
            ->when(!$sessao['is_admin'], function ($query) use ($sessao) {
                return $query->where('usuario_id', $sessao['usuario_id']);
            })
            ->firstOrFail();
    }

    public function detalhes(Request $request, $id)
    {
        if (!session()->has('user_logged')) {
            return redirect('/login')->with('flash_erro', 'Sessão expirada. Faça login novamente.');
        }

        $abertura = $this->aberturaAutorizada((int) $id);
        $dados = $this->dadosDaAbertura($abertura);

        return view('caixa.detalhes', array_merge($dados, ['abertura' => $abertura]));
    }

    public function list(Request $request)
    {
        if (!session()->has('user_logged')) {
            return redirect('/login')->with('flash_erro', 'Sessão expirada. Faça login novamente.');
        }

        $sessao = $this->dadosSessaoLogada();
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $data = AberturaCaixa::with(['usuario', 'filial'])
            ->where('empresa_id', $sessao['empresa_id'])
            ->when(!$sessao['is_admin'], function ($query) use ($sessao) {
                return $query->where('usuario_id', $sessao['usuario_id']);
            })
            ->when(!empty($startDate), function ($query) use ($startDate) {
                return $query->whereDate('created_at', '>=', $startDate);
            })
            ->when(!empty($endDate), function ($query) use ($endDate) {
                return $query->whereDate('created_at', '<=', $endDate);
            })
            ->orderBy('status', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(env('PAGINACAO', 15))
            ->withQueryString();

        return view('caixa.list', compact('data'));
    }

    public function imprimir($id)
    {
        if (!session()->has('user_logged')) {
            return redirect('/login')->with('flash_erro', 'Sessão expirada. Faça login novamente.');
        }

        $abertura = $this->aberturaAutorizada((int) $id);
        $dados = $this->dadosDaAbertura($abertura);
        $usuario = $abertura->usuario ?: Usuario::findOrFail($abertura->usuario_id);
        $config = ConfigNota::where('empresa_id', $abertura->empresa_id)->first();

        $html = view('caixa.relatorio', array_merge($dados, compact('abertura', 'usuario', 'config')))->render();

        $domPdf = new Dompdf(['enable_remote' => true]);
        $domPdf->loadHtml($html);
        $domPdf->setPaper('A4');
        $domPdf->render();

        return $domPdf->stream('Relatorio_caixa_' . $abertura->id . '.pdf', ['Attachment' => false]);
    }

    public function imprimir80($id)
    {
        if (!session()->has('user_logged')) {
            return redirect('/login')->with('flash_erro', 'Sessão expirada. Faça login novamente.');
        }

        $abertura = $this->aberturaAutorizada((int) $id);
        $dados = $this->dadosDaAbertura($abertura);
        $usuario = $abertura->usuario ?: Usuario::find($abertura->usuario_id);
        $config = ConfigNota::where('empresa_id', $abertura->empresa_id)->first();

        $somaVendas = 0;
        foreach ($dados['vendas'] as $v) {
            if (
                strtoupper((string) ($v->estado ?? '')) !== 'CANCELADO'
                && strtoupper((string) ($v->estado_emissao ?? '')) !== 'CANCELADO'
                && !$v->rascunho
                && !$v->consignado
            ) {
                $total = (float) $v->valor_total;
                if (!isset($v->cpf)) {
                    $total = $total - (float) $v->desconto + (float) $v->acrescimo;
                }
                $somaVendas += $total;
            }
        }

        $html = view('caixa.relatorio80', array_merge($dados, compact('abertura', 'usuario', 'config', 'somaVendas')))->render();

        $dompdf = new Dompdf(['enable_remote' => true]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper([0, 0, 226.77, 2000]);
        $dompdf->render();
        $pdf = $dompdf->output();

        return response()->streamDownload(
            function () use ($pdf) {
                echo $pdf;
            },
            'fechamento_caixa_' . $abertura->id . '.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }
}
