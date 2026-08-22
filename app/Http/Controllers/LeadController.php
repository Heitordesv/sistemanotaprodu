<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\LeadObservacao;
use App\Jobs\EnviarMensagemWhatsAppLeds;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class LeadController extends Controller
{
    protected $statusPrioridade = ['Novo', 'Em Contato', 'Qualificado', 'Convertido', 'Descartado'];

    /**
     * LISTAGEM DE LEADS (Com Filtros, Permissões e Dashboard)
     */
    public function index(Request $request)
    {
        if (!session()->has('user_logged')) {
            return redirect('/login')
                ->with('flash_erro', 'Sessão expirada.');
        }

        $userLogged = session('user_logged');
        $isAdmin = ($userLogged['adm'] ?? 0) == 1 || ($userLogged['super'] ?? 0) == 1;

        $query = Lead::with([
            'observacoes',
            'vendedor'
        ]);

        if (!$isAdmin) {
            $query->where('id_vendedor', $userLogged['id']);
        }

        $query->when(
            $request->status && $request->status !== 'Todos',
            function ($q) use ($request) {
                return $q->where('status', $request->status);
            }
        );

        $query->when(
            $request->nome,
            function ($q) use ($request) {
                return $q->where(
                    'nome_completo',
                    'like',
                    '%' . $request->nome . '%'
                );
            }
        );

        $query->when(
            $request->origem,
            function ($q) use ($request) {
                return $q->where('origem_lead', $request->origem);
            }
        );

        $query->when(
            $request->telefone,
            function ($q) use ($request) {
                $tel = preg_replace('/\D/', '', $request->telefone);

                return $q->whereRaw(
                    '
                    REPLACE(
                        REPLACE(
                            REPLACE(
                                whatsapp,
                                "+",
                                ""
                            ),
                            " ",
                            ""
                        ),
                        "-",
                        ""
                    ) LIKE ?
                    ',
                    ["%{$tel}%"]
                );
            }
        );

        $ordemStatus = implode("','", $this->statusPrioridade);

        $query->orderByRaw("FIELD(status, '{$ordemStatus}')");
        $query->orderBy('data_cadastro', 'desc');
        $query->orderBy('id', 'desc');

        $leads = $query
            ->paginate(60)
            ->appends($request->query());

        $statusCountsQuery = Lead::select(
            'status',
            DB::raw('count(*) as total')
        )->groupBy('status');

        if (!$isAdmin) {
            $statusCountsQuery->where(
                'id_vendedor',
                $userLogged['id']
            );
        }

        $statusCounts = $statusCountsQuery
            ->pluck('total', 'status')
            ->toArray();

        $leadsAgrupados = $leads
            ->getCollection()
            ->groupBy('status');

        return view('leads.index', [
            'leads'             => $leads,
            'leadsAgrupados'    => $leadsAgrupados,
            'statusCounts'      => $statusCounts,
            'statusPrioridade'  => $this->statusPrioridade,
            'temPermissao'      => $isAdmin,
            'filtros'           => $request->all()
        ]);
    }

    /**
     * FORMULÁRIO DE CRIAÇÃO
     */
    public function create()
    {
        return view('leads.create');
    }

    /**
     * SALVAR NOVO LEAD (Manual)
     */
    public function store(Request $request)
    {
        $userLogged = session('user_logged');

        if (!is_array($userLogged) || empty($userLogged['id'])) {
            return redirect('/login')
                ->with('flash_erro', 'Sessão expirada.');
        }

        $data = $request->validate([
            'nome_completo' => 'required|string|max:255',
            'email'         => 'nullable|email|max:255',
            'whatsapp'      => 'required|string|max:20',
            'tipo_loja'     => 'nullable|string|max:100',
            'origem_lead'   => 'nullable|string|max:100',
            'status'        => 'nullable|string|max:50',
            'empresa'       => 'nullable|string|max:255'
        ]);

        $data['whatsapp'] = preg_replace('/\D/', '', $data['whatsapp']);

        if (strlen($data['whatsapp']) < 10) {
            return back()
                ->withErrors(['whatsapp' => 'Informe um WhatsApp válido com DDD.'])
                ->withInput();
        }

        $data['id_vendedor'] = (int) $userLogged['id'];
        $data['status'] = !empty($data['status']) ? $data['status'] : 'Novo';
        $data['origem_lead'] = !empty($data['origem_lead']) ? $data['origem_lead'] : 'Cadastro manual';
        $data['data_cadastro'] = now();
        $data['ip_origem'] = $request->ip();

        $lead = Lead::create($data);

        return redirect()
            ->route('leads.show', $lead->id)
            ->with('success', 'Lead cadastrado com sucesso!');
    }

    /**
     * VISUALIZAR DETALHES
     */
    public function show($id)
    {
        $lead = Lead::with(['observacoes.vendedor', 'vendedor'])->findOrFail($id);
        $userLogged = session('user_logged', []);
        $isAdmin = ($userLogged['adm'] ?? 0) == 1 || ($userLogged['super'] ?? 0) == 1;

        if (!$isAdmin && $lead->id_vendedor != ($userLogged['id'] ?? null)) {
            return redirect()->route('leads.index')->with('error', 'Acesso negado.');
        }

        return view('leads.show', compact('lead'));
    }

    /**
     * FORMULÁRIO DE EDIÇÃO
     */
    public function edit($id)
    {
        $lead = Lead::findOrFail($id);
        $userLogged = session('user_logged', []);
        $isAdmin = ($userLogged['adm'] ?? 0) == 1 || ($userLogged['super'] ?? 0) == 1;

        if (!$isAdmin && $lead->id_vendedor != ($userLogged['id'] ?? null)) {
            return redirect()->route('leads.index')->with('error', 'Acesso negado.');
        }

        return view('leads.edit', compact('lead'));
    }

    /**
     * ATUALIZAR DADOS DO LEAD
     */
    public function update(Request $request, $id)
    {
        $lead = Lead::findOrFail($id);

        $data = $request->validate([
            'nome_completo' => 'required|string|max:255',
            'email'         => 'nullable|email|max:255',
            'whatsapp'      => 'required|string|max:20',
            'tipo_loja'     => 'nullable|string|max:100',
            'status'        => 'required|string',
            'origem_lead'   => 'nullable|string'
        ]);

        $data['whatsapp'] = preg_replace('/\D/', '', $data['whatsapp']);
        $lead->update($data);

        return redirect()->route('leads.show', $id)->with('success', 'Dados atualizados!');
    }

    /**
     * ATUALIZAÇÃO RÁPIDA DE STATUS (Via Botão/Modal)
     */
    public function updateStatus(Request $request, $id)
    {
        $lead = Lead::findOrFail($id);
        $lead->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Status alterado para ' . $request->status);
    }

    /**
     * SALVAR OBSERVAÇÃO/TIMELINE
     */
    public function storeObservation(Request $request, $leadId)
    {
        $userLogged = session('user_logged');

        if (!is_array($userLogged) || empty($userLogged['id'])) {
            return redirect('/login')
                ->with('flash_erro', 'Sessão expirada.');
        }

        $validated = $request->validate([
            'observacao' => 'required|string|max:2000'
        ]);

        $lead = Lead::findOrFail($leadId);
        $isAdmin = ($userLogged['adm'] ?? 0) == 1 || ($userLogged['super'] ?? 0) == 1;

        if (!$isAdmin && $lead->id_vendedor != $userLogged['id']) {
            return redirect()->route('leads.index')->with('error', 'Acesso negado.');
        }

        $observationData = [
            'observacao' => trim($validated['observacao']),
            'data_observacao' => now(),
        ];

        // Mantém compatibilidade com bases antigas que ainda não tenham estes campos.
        if (Schema::hasColumn('lead_observacoes', 'id_vendedor')) {
            $observationData['id_vendedor'] = (int) $userLogged['id'];
        }

        if (Schema::hasColumn('lead_observacoes', 'usuario_responsavel')) {
            $observationData['usuario_responsavel'] = $userLogged['nome'] ?? 'Sistema';
        }

        $lead->observacoes()->create($observationData);

        return redirect()->back()->with('success', 'Observação adicionada!');
    }

    /**
     * IMPORTAÇÃO VIA API (CASA DOS DADOS)
     */
    public function importar(Request $request)
    {
        // ... (Lógica de importação que já vimos, mantendo id_vendedor = session)
        // Certifique-se de usar $contagem para o feedback do usuário
    }

    /**
     * DISPARO DE WHATSAPP (Via Job)
     */
    public function enviarMensagem(Request $request, $id)
    {
        $lead = Lead::findOrFail($id);
        $atendente = session('user_logged')['nome'] ?? 'Equipe';
        $mensagem = $request->mensagem ?? "Olá {$lead->nome_completo}, como podemos ajudar?";

        EnviarMensagemWhatsAppLeds::dispatch($lead->id, $mensagem, $atendente);

        return redirect()->back()->with('success', 'Mensagem enviada para a fila!');
    }

    /**
     * EXCLUSÃO
     */
    public function destroy($id)
    {
        $lead = Lead::findOrFail($id);
        $lead->delete();

        return redirect()->route('leads.index')->with('success', 'Lead removido.');
    }
}