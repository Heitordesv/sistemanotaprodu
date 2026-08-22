<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ContaReceber;
use App\Models\ContaPagar;
use App\Models\Produto;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\PlanoEmpresa;
use App\Models\PedidoEcommerce;
use App\Models\Usuario;
use App\Models\UsuarioAcesso;
use Carbon\Carbon;

class NotificacaoController extends Controller
{
    public function index(Request $request)
    {
        /*
         * Segurança das notificações:
         * - somente administrador da empresa ou super administrador;
         * - sessão de acesso precisa estar ativa (status = 0);
         * - usuário precisa pertencer à mesma empresa do hash informado;
         * - nunca confiar apenas em empresa_id/usuario_id enviados pelo navegador.
         */
        $hashEmpresa = (string) $request->get('hash', '');
        $usuarioId = (int) $request->get('usuario_id', 0);
        $sessaoHash = (string) $request->get('sessao_hash', '');

        if ($hashEmpresa === '' || $usuarioId <= 0 || $sessaoHash === '') {
            abort(403, 'Acesso não autorizado às notificações.');
        }

        $acessoAtivo = UsuarioAcesso::where('usuario_id', $usuarioId)
            ->where('hash', $sessaoHash)
            ->where('status', 0)
            ->exists();

        if (!$acessoAtivo) {
            abort(403, 'Sessão inválida ou encerrada.');
        }

        $usuario = Usuario::select('id', 'empresa_id', 'adm', 'login')
            ->where('id', $usuarioId)
            ->first();

        if (!$usuario) {
            abort(403, 'Usuário inválido.');
        }

        $isAdministrador = (bool) $usuario->adm || isSuper($usuario->login);

        if (!$isAdministrador) {
            abort(403, 'Somente administradores podem visualizar notificações.');
        }

        $empresa = Empresa::select('id')
            ->where('hash', $hashEmpresa)
            ->first();

        if (!$empresa || (int) $usuario->empresa_id !== (int) $empresa->id) {
            abort(403, 'Empresa inválida para este usuário.');
        }

        $empresa_id = (int) $empresa->id;

        $hoje = Carbon::today();
        $limiteVencimento = $hoje->copy()->addDays(90);

        // --- 1. Alerta de Plano ---
        $plano = PlanoEmpresa::where('empresa_id', $empresa_id)->first();
        $alertaPlano = null;

        if ($plano && $plano->expiracao) {
            $dataExpiracao = Carbon::parse($plano->expiracao);
            $diasParaVencer = $hoje->diffInDays($dataExpiracao, false);

            if ($diasParaVencer <= 7) {
                $financeiroPlano = ContaReceber::where('empresa_id_emp', $empresa_id)
                    ->where('status', 0)
                    ->orderBy('data_vencimento', 'desc')
                    ->first();

                $baseUrl = rtrim(config('app.url') ?? url('/'), '/');
                $alertaPlano = [
                    'status' => $diasParaVencer < 0 ? 'vencido' : 'alerta',
                    'dias'   => $diasParaVencer,
                    'data'   => $dataExpiracao->format('d/m/Y'),
                    'valor'  => $financeiroPlano->valor_integral ?? $plano->valor,
                    'link'   => $financeiroPlano ? $baseUrl . '/pgempresa/' . $financeiroPlano->id : '#'
                ];
            }
        }

        // --- 2. Contas a Receber ---
        // O tenant é SEMPRE definido por conta_recebers.empresa_id.
        $contasReceberLista = ContaReceber::where('empresa_id', $empresa_id)
            ->where('status', 0)
            ->whereColumn('valor_recebido', '<', 'valor_integral')
            ->whereDate('data_vencimento', '<=', $hoje)
            ->with(['cliente', 'empresa_id_emp_rel'])
            ->orderBy('data_vencimento', 'asc')
            ->get();

        $dadosReceber = [];
        foreach ($contasReceberLista as $c) {
            $nome = 'Não identificado';
            $celular = '';

            if ($c->cliente) {
                $nome = $c->cliente->razao_social ?? $c->cliente->nome_fantasia;
                $celular = $c->cliente->celular ?? $c->cliente->telefone ?? '';
            } elseif ($c->empresa_id_emp_rel) {
                $nome = $c->empresa_id_emp_rel->nome_fantasia;
                $celular = $c->empresa_id_emp_rel->celular ?? $c->empresa_id_emp_rel->telefone ?? '';
            }

            $saldoPendente = max(
                0,
                (float) $c->valor_integral - (float) ($c->valor_recebido ?? 0)
            );

            $dadosReceber[] = [
                'id'         => $c->id,
                'nome'       => $nome,
                'celular'    => $celular,
                'valor'      => $saldoPendente,
                'vencimento' => Carbon::parse($c->data_vencimento)->format('d/m/Y')
            ];
        }

        // --- Pedidos do e-commerce ---
        $pedidosPendentes = PedidoEcommerce::where('empresa_id', $empresa_id)
            ->where('status', '1')
            ->orderBy('created_at', 'desc')
            ->get();

        $dadosPedidos = [];
        foreach ($pedidosPendentes as $p) {
            $cliente = $p->cliente;
            $nomeCliente = $cliente
                ? ($cliente->nome . ' ' . ($cliente->sobre_nome ?? ''))
                : 'Cliente não identificado';

            $dadosPedidos[] = [
                'id'      => $p->id,
                'cliente' => $nomeCliente,
                'valor'   => $p->valor_total,
                'data'    => Carbon::parse($p->created_at)->format('d/m/Y'),
                'link'    => route('pedidos.show', $p->id)
            ];
        }

        // --- 3. Contas a Pagar ---
        $contasPagarLista = ContaPagar::where('empresa_id', $empresa_id)
            ->where('status', 0)
            ->whereDate('data_vencimento', '<=', $hoje)
            ->with(['fornecedor'])
            ->get();

        $dadosPagar = [];
        foreach ($contasPagarLista as $cp) {
            $dadosPagar[] = [
                'id'         => $cp->id,
                'nome'       => $cp->fornecedor->razao_social ?? 'Fornecedor não identificado',
                'valor'      => $cp->valor_integral,
                'vencimento' => Carbon::parse($cp->data_vencimento)->format('d/m/Y')
            ];
        }

        // --- 4. Estoque Mínimo ---
        $produtosComAlertaEstoque = [];
        $produtos = Produto::where('empresa_id', $empresa_id)
            ->where('estoque_minimo', '>', 0)
            ->get();

        foreach ($produtos as $p) {
            if ($p->estoqueAtual2() < $p->estoque_minimo) {
                $produtosComAlertaEstoque[] = [
                    'id'      => $p->id,
                    'nome'    => $p->nome,
                    'estoque' => $p->estoqueAtual()
                ];
            }
        }

        // --- 5. Validade de Produtos ---
        $alertasVencimento = [];
        $produtosVencimento = Produto::where('empresa_id', $empresa_id)
            ->whereNotNull('vencimento')
            ->whereNotIn('vencimento', ['0000-00-00', ''])
            ->whereDate('vencimento', '<=', $limiteVencimento)
            ->get();

        foreach ($produtosVencimento as $p) {
            $venc = Carbon::parse($p->vencimento);
            $dias = $hoje->diffInDays($venc, false);
            $alertasVencimento[] = [
                'id'         => $p->id,
                'nome'       => $p->nome,
                'vencimento' => $venc->format('d/m/Y'),
                'status'     => $dias < 0 ? 'vencido' : ($dias <= 30 ? 'critico' : 'atencao')
            ];
        }

        // --- 6. Aniversariantes ---
        $aniversariantes = Cliente::where('empresa_id', $empresa_id)
            ->whereMonth('data_aniversario', $hoje->month)
            ->whereDay('data_aniversario', $hoje->day)
            ->get();

        return view('notificacoes.index', compact(
            'dadosReceber',
            'dadosPagar',
            'produtosComAlertaEstoque',
            'alertasVencimento',
            'aniversariantes',
            'alertaPlano',
            'dadosPedidos'
        ));
    }
}