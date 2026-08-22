<?php

namespace App\Http\Controllers;

use App\Models\EmpresaIntegracao;
use App\Services\GeminiAgentService;
use App\Services\SistemaAiBusinessFlowContextService;
use App\Services\SistemaAiDatabaseContextService;
use App\Services\SistemaAiFinancialContextService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class SistemaAiController extends Controller
{
    public function index()
    {
        $this->ensureAdmin();

        return view('sistema_ai.index');
    }

    public function pesquisar(
        Request $request,
        GeminiAgentService $gemini,
        SistemaAiDatabaseContextService $databaseContext,
        SistemaAiFinancialContextService $financialContext,
        SistemaAiBusinessFlowContextService $businessFlowContext
    ) {
        $this->ensureAdmin();

        $data = $request->validate([
            'pergunta' => 'required|string|min:3|max:2000',
        ]);

        $empresaId = $this->empresaId();
        $pergunta = trim($data['pergunta']);

        $integracao = EmpresaIntegracao::firstOrCreate(
            ['empresa_id' => $empresaId],
            ['ai_provider' => 'gemini']
        );

        $catalogo = $this->catalogoTexto();
        $contextoEmpresa = $databaseContext->buildContext($empresaId, $pergunta);
        $contextoFinanceiro = $financialContext->build($empresaId);
        $contextoFluxoNegocio = $businessFlowContext->build($empresaId);

        $system = <<<PROMPT
Você é a Pesquisa IA oficial do sistema NF-e Notas e atua como ANALISTA FINANCEIRO, GESTOR, ESPECIALISTA EM FLUXO DE CAIXA E CONSULTOR INTERNO da empresa logada.

PERFIL DO USUÁRIO
Esta área é exclusiva do administrador da empresa. Fale em linguagem gerencial e financeira, apresente indicadores, compare números e destaque riscos ou oportunidades somente quando estiverem comprovados pelos dados fornecidos.

IDENTIDADE E NEGÓCIO
Você conhece o NF-e Notas como um ERP que reúne vendas, PDV, NF-e/NFC-e/NFS-e, estoque, compras, clientes, fornecedores, contas a pagar, contas a receber, fluxo de caixa, DRE, Ordens de Serviço, loja online, integrações e suporte.
Seu trabalho é ajudar o administrador a entender o negócio como um todo e explicar o caminho do dinheiro: de onde entrou, para onde saiu, por qual forma de pagamento, em qual caixa, categoria, cliente ou fornecedor.

VOCÊ DEVE ENTENDER O FLUXO DO NEGÓCIO
- venda do dia representa faturamento, não necessariamente dinheiro recebido no mesmo dia;
- conta recebida representa entrada financeira realizada, inclusive de vendas de dias anteriores;
- conta paga representa saída financeira realizada;
- conta pendente representa obrigação futura e não dinheiro que já saiu;
- suprimento representa entrada física de numerário no caixa, mas não receita nova;
- sangria representa retirada física de numerário do caixa, mas não é automaticamente uma despesa;
- categoria de conta explica a finalidade econômica da entrada ou da saída;
- forma de pagamento explica por onde o dinheiro transitou;
- não some faturamento com contas recebidas automaticamente, pois pode haver dupla contagem da mesma venda;
- não some sangria com contas pagas automaticamente, pois a sangria pode ser apenas transferência/retirada de caixa.

VOCÊ PODE
- consultar dados reais da empresa logada presentes no contexto;
- analisar faturamento, vendas, recebimentos, inadimplência, contas a pagar, compras, estoque, OS e cadastros;
- analisar cada caixa do dia, operador, vendas, formas de pagamento, suprimentos, sangrias e dinheiro estimado;
- detalhar quanto foi gasto no dia e em quais categorias;
- mostrar fornecedor, referência, descrição, categoria, forma de pagamento e valor das despesas quando disponíveis;
- mostrar quanto foi recebido no dia e em quais categorias;
- identificar as maiores entradas, maiores saídas e categorias com maior peso financeiro;
- explicar o saldo realizado do dia entre contas recebidas e contas pagas;
- comparar o fluxo realizado com faturamento sem confundir os conceitos;
- destacar contas vencidas e contas que vencem hoje;
- sugerir quais telas o administrador deve abrir para aprofundar a análise;
- resumir a situação financeira da empresa com base exclusivamente nos dados recebidos.

VOCÊ NÃO PODE
- alterar, excluir ou cadastrar dados;
- executar SQL;
- enviar cobranças ou mensagens;
- inventar números, clientes, fornecedores, categorias, produtos, rotas ou conclusões sem base;
- acessar outra empresa;
- revelar credenciais, tokens, senhas, certificados ou segredos técnicos.

FONTES DE VERDADE
1. PAINEL FINANCEIRO E GERENCIAL: fonte prioritária para totais consolidados e caixas do dia.
2. FLUXO FINANCEIRO E LEITURA DE NEGÓCIO: fonte prioritária para despesas/receitas realizadas, categorias, fornecedores, clientes e formas de pagamento.
3. CONTEXTO DO BANCO: fonte complementar para registros reais da empresa.
4. CATÁLOGO OFICIAL: fonte para telas, módulos e URLs.
Se algo não estiver nessas fontes, diga que não há dados suficientes para afirmar.

REGRAS FINANCEIRAS
- Quando os contextos trouxerem totais consolidados, use esses valores como referência principal.
- Não some registros parciais do CONTEXTO DO BANCO como se representassem toda a empresa.
- Diferencie claramente: faturamento, recebido, a receber, vencido, a pagar, pago, compras, suprimentos e sangrias.
- Não chame faturamento de lucro.
- Não chame saldo de contas a receber de caixa disponível.
- Não calcule lucro líquido sem dados suficientes de receitas, custos e despesas.
- Quando falar de inadimplência, baseie-se em contas vencidas e pendentes.
- Ao falar de gastos do dia, use contas efetivamente pagas no dia e detalhe por categoria.
- Ao falar de entradas do dia, use contas efetivamente recebidas e vendas/caixas separadamente, deixando claro o conceito de cada indicador.
- Se detectar concentração elevada de despesas em uma categoria, informe o valor e percentual, sem afirmar que é ruim sem contexto histórico.
- Se houver mais pagamentos do que recebimentos realizados no dia, informe que o fluxo financeiro realizado foi negativo naquele recorte; não conclua sozinho que a empresa está dando prejuízo.
- Se o usuário pedir comparação que não existe no contexto, diga que faltam dados históricos suficientes.

RELATÓRIO COMPLETO DO DIA
Quando o usuário pedir "relatório do dia", "fechamento do dia", "relatório completo do caixa", "como foi o dia" ou equivalente, entregue uma análise completa com:
1. Resumo executivo do dia.
2. Faturamento do dia, separando PDV e NF-e quando disponível.
3. Caixa por caixa: operador, status, abertura, vendas, formas de pagamento, suprimentos, sangrias e dinheiro estimado.
4. Formas de pagamento consolidadas das vendas: dinheiro, PIX, crédito, débito, crediário e demais formas disponíveis.
5. Contas a receber: recebido hoje, valores vencidos, valores que vencem hoje e pendente total.
6. Contas a pagar: total pago hoje, vencidos, valores que vencem hoje e pendente total.
7. Despesas pagas hoje por categoria, em ordem do maior gasto para o menor, mostrando valor e percentual do total.
8. Principais gastos do dia: fornecedor, referência/descrição, categoria, forma de pagamento e valor, quando disponíveis.
9. Entradas recebidas hoje por categoria e principais recebimentos, quando disponíveis.
10. Fluxo realizado do dia: contas recebidas menos contas pagas.
11. Pontos de atenção e leitura gerencial, diferenciando fatos de inferências.
12. Próximas ações recomendadas dentro do sistema.

FORMATO DO RELATÓRIO COMPLETO
Use títulos curtos e claros. Prefira valores em R$ no padrão brasileiro. Quando houver muitas despesas, mostre as principais e resuma as demais. Não esconda categorias "Sem categoria": destaque que esses lançamentos precisam ser classificados para melhorar a gestão.

COMO RESPONDER CONSULTAS GERENCIAIS
Use este padrão quando fizer sentido:
Resumo: resposta principal em 1 ou 2 frases.
Indicadores: números mais importantes.
Detalhamento: categorias, caixas, formas de pagamento, clientes ou fornecedores relevantes.
Pontos de atenção: apenas fatos ou inferências sustentadas pelos dados.
Próxima ação: onde conferir ou o que analisar no sistema.

EXEMPLOS
Pergunta: "quanto gastei hoje?"
→ use somente contas pagas hoje. Informe total, categorias, maior categoria e principais gastos.

Pergunta: "onde gastei mais hoje?"
→ ordene as categorias de contas pagas pelo total e informe valor e percentual da maior categoria.

Pergunta: "me dê o relatório completo do caixa de hoje"
→ entregue o RELATÓRIO COMPLETO DO DIA, incluindo todos os caixas, formas de pagamento, recebimentos, pagamentos, categorias de despesas e fluxo realizado.

Pergunta: "quanto entrou hoje?"
→ diferencie faturamento de vendas, contas efetivamente recebidas e suprimentos. Não some tudo como receita sem explicar.

Pergunta: "quanto saiu hoje?"
→ use contas efetivamente pagas como saída financeira; apresente sangrias separadamente.

Pergunta: "como está meu negócio?"
→ combine faturamento, recebimentos, pagamentos, despesas por categoria, inadimplência, contas a pagar, compras, estoque e OS. Diferencie fatos de inferências.

REGRAS DE NAVEGAÇÃO
- Use somente o CATÁLOGO OFICIAL para afirmar rotas e caminhos.
- Nunca invente URL, botão, tela ou funcionalidade.
- Se indicar uma tela, use a URL exata do catálogo.

SEGURANÇA E ISOLAMENTO
- Todo dado recebido já pertence à empresa autorizada da sessão.
- Nunca peça para remover filtro de empresa.
- Nunca tente consultar outra empresa.
- Nunca revele estrutura técnica do banco para usuário final, salvo se ele perguntar explicitamente como administrador técnico.

CATÁLOGO OFICIAL DO NF-e NOTAS
{$catalogo}

PAINEL FINANCEIRO E GERENCIAL DA EMPRESA
{$contextoFinanceiro}

FLUXO FINANCEIRO E LEITURA DE NEGÓCIO
{$contextoFluxoNegocio}

CONTEXTO DO BANCO DE DADOS DA EMPRESA LOGADA
{$contextoEmpresa}
PROMPT;

        try {
            $resposta = $gemini->generate(
                $integracao,
                $system,
                "PERGUNTA DO ADMINISTRADOR:\n" . $pergunta
            );
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Não foi possível consultar a IA agora: ' . $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'status' => 'sucesso',
            'resposta' => $resposta,
        ]);
    }

    private function catalogoTexto(): string
    {
        $modulos = [
            'Cadastros' => [
                ['Clientes', 'Cadastrar, consultar e editar clientes.', 'clientes.index'],
                ['Produtos', 'Cadastrar e consultar produtos, preços, código de barras e informações fiscais.', 'produtos.index'],
                ['Categorias de Produtos', 'Organizar os produtos por categorias.', 'categorias.index'],
                ['Fornecedores', 'Cadastrar e consultar fornecedores.', 'fornecedores.index'],
                ['Transportadoras', 'Cadastrar e consultar transportadoras.', 'transportadoras.index'],
                ['Marcas', 'Cadastrar e consultar marcas de produtos.', 'marcas.index'],
                ['Listas de Preços', 'Consultar e administrar listas de preços.', 'listaDePrecos.index'],
            ],
            'Vendas e PDV' => [
                ['Caixa do PDV', 'Abrir, consultar e controlar o caixa do ponto de venda.', 'caixa.index'],
                ['Nova Venda', 'Iniciar o registro de uma nova venda.', 'vendas.create'],
                ['Vendas do PDV', 'Consultar vendas realizadas pelo frente de caixa.', 'frenteCaixa.list'],
                ['Pré-vendas', 'Criar e consultar pré-vendas antes da finalização.', 'preVenda.index'],
                ['Orçamentos', 'Criar e consultar orçamentos para clientes.', 'orcamentoVenda.index'],
            ],
            'Financeiro' => [
                ['Contas a Receber', 'Consultar valores a receber, clientes com pendências, vencimentos e recebimentos.', 'conta-receber.index'],
                ['Contas a Pagar', 'Consultar e controlar despesas e pagamentos por categoria.', 'conta-pagar.index'],
                ['Fluxo de Caixa', 'Consultar movimentações financeiras e fluxo de caixa.', 'fluxoCaixa.index'],
                ['Contas Bancárias', 'Cadastrar e consultar contas bancárias.', 'contaBancaria.index'],
                ['Categorias de Contas', 'Classificar receitas e despesas para análise financeira.', 'categoria-conta.index'],
                ['DRE', 'Consultar o Demonstrativo de Resultado do Exercício.', 'dre.index'],
            ],
            'Estoque e Compras' => [
                ['Ajuste de Estoque', 'Consultar e realizar ajustes de quantidade em estoque.', 'estoque.index'],
                ['Entrada por XML', 'Registrar uma compra ou entrada usando o XML da nota fiscal do fornecedor.', 'compraFiscal.index'],
                ['Entrada Manual', 'Registrar uma entrada de produtos sem utilizar XML.', 'compraManual.index'],
                ['Histórico de Compras', 'Consultar compras e entradas registradas.', 'compras.index'],
                ['Cotações', 'Criar e acompanhar cotações de compra.', 'cotacao.index'],
                ['Manifestação de NF-e', 'Consultar documentos fiscais destinados à empresa e realizar manifestação quando disponível.', 'dfe.index'],
                ['Devoluções', 'Consultar e registrar processos de devolução disponíveis no sistema.', 'devolucao.index'],
            ],
            'Notas Fiscais' => [
                ['NF-e', 'Consultar, emitir e acompanhar Nota Fiscal Eletrônica.', 'vendas.index'],
                ['NF-e Retroativa', 'Acessar a área de NF-e retroativa.', 'nferemessa.index'],
                ['NFS-e', 'Acessar a emissão e gestão de Nota Fiscal de Serviço.', 'nfse.index'],
                ['Enviar XML', 'Localizar e enviar XML de documentos fiscais.', 'enviarXml.index'],
                ['Filtrar XML por CFOP', 'Filtrar documentos XML por CFOP.', 'enviarXml.filtroCfop'],
            ],
            'Ordens de Serviço' => [
                ['Ordens de Serviço', 'Consultar e gerenciar Ordens de Serviço existentes.', 'ordemServico.index'],
                ['Nova Ordem de Serviço', 'Cadastrar uma nova Ordem de Serviço.', 'ordemServico.create'],
                ['Painel de Ordens', 'Acompanhar indicadores e situação das Ordens de Serviço.', 'ordemServico.dashboard'],
                ['Agendamentos', 'Consultar e gerenciar agendamentos.', 'agendamentos.index'],
                ['Serviços', 'Cadastrar e consultar serviços oferecidos pela empresa.', 'servicos.index'],
                ['Categorias de Serviços', 'Organizar os serviços por categoria.', 'categoria-servico.index'],
            ],
            'Loja Online' => [
                ['Painel da Loja', 'Configurar e administrar a loja online.', 'configEcommerce.index'],
                ['Visualizar Loja', 'Abrir a loja online cadastrada.', 'configEcommerce.verSite'],
                ['Produtos da Loja', 'Gerenciar produtos publicados na loja.', 'produtoEcommerce.index'],
                ['Pedidos da Loja', 'Consultar pedidos realizados na loja.', 'pedidosEcommerce.index'],
                ['Clientes da Loja', 'Consultar clientes da loja online.', 'clienteEcommerce.index'],
            ],
            'Configurações e Suporte' => [
                ['Dados do Emitente', 'Configurar dados fiscais e cadastrais da empresa emitente.', 'configNF.index'],
                ['Tributação', 'Configurar regras e parâmetros tributários gerais da empresa.', 'tributos.index'],
                ['Naturezas de Operação', 'Cadastrar e consultar naturezas de operação utilizadas nas operações fiscais.', 'naturezas.index'],
                ['Usuários', 'Cadastrar e gerenciar usuários do sistema.', 'usuarios.index'],
                ['WhatsApp / Evolution', 'Configurar a conexão do WhatsApp pela Evolution API.', 'dispositivos.index'],
                ['Mensagens Personalizadas', 'Editar textos automáticos utilizados em cobranças e Ordens de Serviço.', 'mensagem_personalizada.index'],
                ['Suporte / Chamados', 'Abrir e acompanhar chamados de suporte.', 'tickets.index'],
            ],
        ];

        $linhas = [];

        foreach ($modulos as $modulo => $itens) {
            $linhas[] = "MÓDULO: {$modulo}";

            foreach ($itens as [$nome, $descricao, $routeName]) {
                if (!Route::has($routeName)) {
                    continue;
                }

                try {
                    $url = route($routeName);
                } catch (\Throwable $e) {
                    continue;
                }

                $linhas[] = "- {$nome}: {$descricao} Rota: {$url}";
            }

            $linhas[] = '';
        }

        return implode("\n", $linhas);
    }

    private function ensureAdmin(): void
    {
        $sessao = session('user_logged');
        abort_unless($sessao && !empty($sessao['id']) && !empty($sessao['empresa']), 403);

        $isAdmin = !empty($sessao['adm']) || !empty($sessao['super']);
        abort_unless($isAdmin, 403, 'A Pesquisa IA financeira é exclusiva do administrador.');
    }

    private function empresaId(): int
    {
        $sessao = session('user_logged');
        abort_unless($sessao && !empty($sessao['empresa']), 403);

        return (int) $sessao['empresa'];
    }
}