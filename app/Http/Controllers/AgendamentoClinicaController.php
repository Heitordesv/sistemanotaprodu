<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Empresa;
use App\Models\Funcionario; 
use App\Models\Servico;      
use App\Models\Agendaclinica; // O modelo da sua tabela de agendamentos
use App\Models\Cliente;      

class AgendamentoClinicaController extends Controller
{
    /**
     * 1. Exibe a tela de agendamento público para uma clínica específica.
     * Rota: GET /agendamento/{empresa_slug}
     */
    public function index($empresa_slug)
    {
        // Encontra a empresa pelo slug para personalizar a tela de agendamento
        $empresa = Empresa::where('nome_link', $empresa_slug)->firstOrFail();
        
        // Busca profissionais ativos da empresa
        $profissionais = Funcionario::where('empresa_id', $empresa->id)
                                    ->where('inativo', 0)
                                    ->orderBy('nome')
                                    ->get();

        // Busca serviços disponíveis para agendamento
        $servicos = Servico::where('empresa_id', $empresa->id)
                            ->orderBy('nome')
                            ->get();

        return view('agendamento.index', compact('empresa', 'profissionais', 'servicos'));
    }


    /**
     * 2. Busca e retorna os horários disponíveis (usado via AJAX pelo frontend).
     * Rota: POST /agendamento/disponibilidade
     */
    public function getDisponibilidade(Request $request)
    {
        $request->validate([
            'profissional_id' => 'required|exists:profissionais,id',
            'data_agendamento' => 'required|date_format:Y-m-d|after_or_equal:today',
            // Adicione servico_id se a duração variar
        ]);

        $profissionalId = $request->profissional_id;
        $data = $request->data_agendamento;
        
        // ** Implementação da Lógica de Disponibilidade **
        // A duração do serviço seria necessária aqui para calcular a ocupação correta.
        $intervaloPadraoMinutos = 30; // Exemplo, ajuste conforme a duração do serviço

        // Busca agendamentos ocupados (confirmados ou pendentes)
        $agendamentosOcupados = Agendaclinica::where('profissional_id', $profissionalId)
            ->where('data_agendamento', $data)
            ->whereIn('status', ['confirmado', 'pendente'])
            ->get();
        
        // Define as horas de trabalho do profissional (Exemplo: 9h às 17h)
        $horarioInicioTrabalho = '09:00:00';
        $horarioFimTrabalho = '17:00:00';
        
        // Gera todos os slots possíveis
        $slotsPossiveis = $this->generateSlots($horarioInicioTrabalho, $horarioFimTrabalho, $intervaloPadraoMinutos);

        $horariosDisponiveis = [];

        foreach ($slotsPossiveis as $slotInicio) {
            $isLivre = true;
            $slotFim = Carbon::createFromFormat('H:i:s', $slotInicio)->addMinutes($intervaloPadraoMinutos)->format('H:i:s');
            
            // Verifica conflito com agendamentos existentes
            foreach ($agendamentosOcupados as $agenda) {
                // Se o slot proposto começar ou terminar dentro de um agendamento existente
                if ($slotInicio < $agenda->hora_fim && $slotFim > $agenda->hora_inicio) {
                    $isLivre = false;
                    break;
                }
            }

            if ($isLivre) {
                $horariosDisponiveis[] = $slotInicio;
            }
        }

        return response()->json(['disponiveis' => $horariosDisponiveis]);
    }


    /**
     * 3. Salva o novo agendamento na tabela 'agendaclinica'.
     * Rota: POST /agendamento/store
     */
    public function store(Request $request)
    {
        $request->validate([
            'empresa_id' => 'required|exists:empresas,id',
            'servico_id' => 'required|exists:servicos,id',
            'profissional_id' => 'required|exists:profissionais,id',
            'data_agendamento' => 'required|date|after_or_equal:today',
            'hora_agendamento' => 'required|date_format:H:i:s',
            'nome_cliente' => 'required|string|max:255',
            'telefone_cliente' => 'required|string|max:20',
        ]);
        
        try {
            DB::beginTransaction();

            // 1. Encontrar ou Criar Cliente
            $cliente = Cliente::firstOrCreate(
                [
                    // Assumindo que a combinação de telefone e empresa é única para o cliente
                    'telefone' => $request->telefone_cliente, 
                    'empresa_id' => $request->empresa_id
                ],
                [
                    'nome' => $request->nome_cliente,
                    'email' => $request->email_cliente ?? null,
                ]
            );

            // 2. Definir Duração e Horários Finais
            // Idealmente, a duração viria do modelo Servico
            $duracaoMinutos = Servico::find($request->servico_id)->duracao_minutos ?? 30;

            $horaInicio = Carbon::createFromFormat('H:i:s', $request->hora_agendamento);
            $horaFim = $horaInicio->copy()->addMinutes($duracaoMinutos);
            
            // 3. Checagem de Conflito (Evitando inserções simultâneas)
            $conflito = Agendaclinica::where('profissional_id', $request->profissional_id)
                ->where('data_agendamento', $request->data_agendamento)
                ->where('hora_inicio', $horaInicio->format('H:i:s'))
                ->whereIn('status', ['confirmado', 'pendente'])
                ->exists();

            if ($conflito) {
                 DB::rollBack();
                 return response()->json(['message' => 'Horário foi ocupado. Por favor, tente novamente.'], 409);
            }

            // 4. Criação do Agendamento
            $agendamento = Agendaclinica::create([
                'cliente_id' => $cliente->id,
                'servico_id' => $request->servico_id,
                'profissional_id' => $request->profissional_id,
                'data_agendamento' => $request->data_agendamento,
                'hora_inicio' => $horaInicio->format('H:i:s'),
                'hora_fim' => $horaFim->format('H:i:s'),
                'status' => 'pendente', // Aguardando confirmação ou pagamento
                'observacao' => $request->observacao ?? null,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Agendamento criado com sucesso! Aguarde a confirmação.', 
                'agendamento_id' => $agendamento->id
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            // Log do erro
            return response()->json(['message' => 'Erro interno ao finalizar o agendamento.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Função auxiliar para gerar slots de horário.
     */
    private function generateSlots($start, $end, $intervalMinutes)
    {
        $slots = [];
        $current = Carbon::parse($start);
        $end = Carbon::parse($end);

        while ($current->lessThan($end)) {
            $slots[] = $current->format('H:i:s');
            $current->addMinutes($intervalMinutes);
        }
        return $slots;
    }
}