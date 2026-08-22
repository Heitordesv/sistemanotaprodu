@extends('default.layout', ['title' => 'Auditoria tributária de produtos'])

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h3 class="mb-1">Auditoria tributária com IA</h3>
                <small class="text-muted">
                    Verifique possíveis divergências de NCM, CEST, CST/CSOSN e CFOP.
                </small>
            </div>

            <a href="{{ route('produtos.index') }}"
               class="btn btn-outline-secondary">
                Voltar aos produtos
            </a>
        </div>

        <div class="card-body">
            <div class="alert alert-warning">
                <strong>Atenção:</strong>
                a análise é apenas assistiva, não altera os cadastros e não substitui
                a validação do contador. A tributação depende do regime tributário,
                UF, tipo de operação e legislação vigente.
            </div>

            <div class="alert alert-info">
                <strong>Regra aplicada nesta tela:</strong>

                <ul class="mb-0 mt-2">
                    <li>
                        Mercadoria adquirida de terceiros, efetivamente sujeita ao ICMS-ST
                        e com imposto retido anteriormente:
                        CFOP interno <strong>5405</strong> e CSOSN <strong>500</strong>.
                    </li>
                    <li>
                        Mercadoria adquirida de terceiros para revenda, sem ICMS-ST:
                        CFOP interno <strong>5102</strong> e CSOSN <strong>102</strong>.
                    </li>
                    <li>
                        A existência ou ausência do CEST será tratada apenas como
                        <strong>indício para revisão</strong>, pois não determina sozinha
                        a aplicação da substituição tributária.
                    </li>
                </ul>
            </div>

            <form method="get" class="row g-2 mb-4">
                <div class="col-md-6">
                    <input
                        name="nome"
                        value="{{ request('nome') }}"
                        class="form-control"
                        placeholder="Pesquisar produto por descrição"
                    >
                </div>

                <div class="col-auto">
                    <button class="btn btn-primary" type="submit">
                        <i class="bx bx-search"></i>
                        Pesquisar
                    </button>
                </div>
            </form>

            <form
                method="post"
                action="{{ route('produtos.auditoria-tributaria.analisar') }}"
                id="form-auditoria"
            >
                @csrf

                @error('produto_ids')
                    <div class="alert alert-danger">
                        {{ $message }}
                    </div>
                @enderror

                <div class="d-flex flex-wrap gap-2 mb-3">
                    <button
                        type="button"
                        class="btn btn-outline-danger btn-sm"
                        id="selecionar-divergentes"
                    >
                        <i class="bx bx-error-circle"></i>
                        Selecionar divergentes
                    </button>

                    <button
                        type="button"
                        class="btn btn-outline-secondary btn-sm"
                        id="limpar-selecao"
                    >
                        Limpar seleção
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40px;">
                                    <input
                                        type="checkbox"
                                        id="selecionar-todos"
                                        class="form-check-input"
                                    >
                                </th>
                                <th>Produto</th>
                                <th>NCM</th>
                                <th>CEST</th>
                                <th>CST/CSOSN</th>
                                <th>CFOP saída</th>
                                <th>CFOP entrada</th>
                                <th class="text-center" style="min-width: 170px;">Validação</th>
                                <th class="text-center" style="min-width: 120px;">Ações</th>
                            </tr>
                        </thead>

                        <tbody>
                        @forelse($data as $produto)
                            @php
                                /*
                                 * Normalização:
                                 * remove pontos, espaços, traços e outros caracteres,
                                 * deixando somente números para realizar a comparação.
                                 */
                                $cest = preg_replace(
                                    '/\D/',
                                    '',
                                    (string) ($produto->CEST ?? '')
                                );

                                $cstCsosn = preg_replace(
                                    '/\D/',
                                    '',
                                    (string) ($produto->CST_CSOSN ?? '')
                                );

                                $cfopSaidaEstadual = preg_replace(
                                    '/\D/',
                                    '',
                                    (string) ($produto->CFOP_saida_estadual ?? '')
                                );

                                $possuiCest = $cest !== '';

                                // Pares usuais para revenda interna por empresa do Simples Nacional.
                                $parComSt = $cfopSaidaEstadual === '5405' && $cstCsosn === '500';
                                $parSemSt = $cfopSaidaEstadual === '5102' && $cstCsosn === '102';

                                // Divergência objetiva: CFOP e CSOSN não formam nenhum dos pares previstos.
                                $possuiDivergencia = !$parComSt && !$parSemSt;

                                // O CEST gera somente um indício, nunca a conclusão automática sobre ST.
                                $possuiIndicioRevisao =
                                    ($possuiCest && $parSemSt) ||
                                    (!$possuiCest && $parComSt);

                                $cfopCorreto = in_array($cfopSaidaEstadual, ['5102', '5405'], true);
                                $cstCorreto =
                                    ($cfopSaidaEstadual === '5102' && $cstCsosn === '102') ||
                                    ($cfopSaidaEstadual === '5405' && $cstCsosn === '500');

                                $cfopEsperado = $cstCsosn === '500'
                                    ? '5405'
                                    : ($cstCsosn === '102' ? '5102' : null);

                                $cstEsperado = $cfopSaidaEstadual === '5405'
                                    ? '500'
                                    : ($cfopSaidaEstadual === '5102' ? '102' : null);

                                $classeLinha = $possuiDivergencia
                                    ? 'table-danger'
                                    : ($possuiIndicioRevisao ? 'table-warning' : '');
                            @endphp

                            <tr
                                class="{{ $classeLinha }}"
                                data-divergente="{{ $possuiDivergencia ? '1' : '0' }}"
                            >
                                <td>
                                    <input
                                        class="produto-check form-check-input"
                                        type="checkbox"
                                        name="produto_ids[]"
                                        value="{{ $produto->id }}"
                                    >
                                </td>

                                <td>
                                    <strong>{{ $produto->nome }}</strong>

                                    <br>

                                    <small class="text-muted">
                                        {{ optional($produto->categoria)->nome ?: 'Sem categoria' }}
                                    </small>
                                </td>

                                <td>
                                    {{ $produto->NCM ?: '—' }}
                                </td>

                                <td>
                                    @if($possuiCest)
                                        <span class="badge bg-primary">
                                            {{ $produto->CEST }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            Sem CEST
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <div class="d-flex flex-column align-items-start gap-1">
                                        <span class="{{ $cstCorreto ? 'text-success' : 'text-danger fw-bold' }}">
                                            {{ $produto->CST_CSOSN ?: 'Não informado' }}
                                        </span>

                                        @if(!$cstCorreto && $cstEsperado)
                                            <small class="text-danger">
                                                Esperado: {{ $cstEsperado }}
                                            </small>
                                        @endif
                                    </div>
                                </td>

                                <td>
                                    <div class="mb-1">
                                        <small class="text-muted">Estadual:</small>

                                        <span class="{{ $cfopCorreto ? 'text-success' : 'text-danger fw-bold' }}">
                                            {{ $produto->CFOP_saida_estadual ?: 'Não informado' }}
                                        </span>
                                    </div>

                                    @if(!$cfopCorreto && $cfopEsperado)
                                        <small class="text-danger d-block">
                                            Esperado: {{ $cfopEsperado }}
                                        </small>
                                    @endif

                                    <div class="mt-1">
                                        <small class="text-muted">Interestadual:</small>
                                        <span>
                                            {{ $produto->CFOP_saida_inter_estadual ?: '—' }}
                                        </span>
                                    </div>
                                </td>

                                <td>
                                    <div>
                                        <small class="text-muted">Estadual:</small>
                                        {{ $produto->CFOP_entrada_estadual ?: '—' }}
                                    </div>

                                    <div class="mt-1">
                                        <small class="text-muted">Interestadual:</small>
                                        {{ $produto->CFOP_entrada_inter_estadual ?: '—' }}
                                    </div>
                                </td>

                                <td class="text-center">
                                    <button
                                        type="button"
                                        class="btn btn-sm w-100 d-flex align-items-center justify-content-center gap-1
                                            {{ $possuiDivergencia
                                                ? 'btn-danger'
                                                : ($possuiIndicioRevisao ? 'btn-warning' : 'btn-success') }}"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#validacao-produto-{{ $produto->id }}"
                                        aria-expanded="false"
                                        aria-controls="validacao-produto-{{ $produto->id }}"
                                    >
                                        <i class="bx {{
                                            $possuiDivergencia
                                                ? 'bx-error-circle'
                                                : ($possuiIndicioRevisao ? 'bx-info-circle' : 'bx-check-circle')
                                        }}"></i>

                                        <span>
                                            {{ $possuiDivergencia
                                                ? 'Ver divergência'
                                                : ($possuiIndicioRevisao ? 'Ver revisão' : 'Ver validação') }}
                                        </span>

                                        <i class="bx bx-chevron-down ms-auto icone-validacao"></i>
                                    </button>

                                    <div
                                        class="collapse mt-2 text-start"
                                        id="validacao-produto-{{ $produto->id }}"
                                    >
                                    @if($possuiDivergencia)
                                        <div class="alert alert-danger py-2 px-3 mb-0">
                                            <div class="d-flex align-items-start gap-2">
                                                <i class="bx bx-error-circle fs-5 mt-1"></i>

                                                <div>
                                                    <strong>Divergência encontrada</strong>

                                                    <ul class="mb-0 mt-1 ps-3 small">
                                                        @if($cfopEsperado && $cfopSaidaEstadual !== $cfopEsperado)
                                                            <li>
                                                                Para o CSOSN {{ $cstCsosn ?: 'informado' }},
                                                                verifique o CFOP interno
                                                                <strong>{{ $cfopEsperado }}</strong>.
                                                            </li>
                                                        @endif

                                                        @if($cstEsperado && $cstCsosn !== $cstEsperado)
                                                            <li>
                                                                Para o CFOP {{ $cfopSaidaEstadual ?: 'informado' }},
                                                                verifique o CSOSN
                                                                <strong>{{ $cstEsperado }}</strong>.
                                                            </li>
                                                        @endif

                                                        @if(!$cfopEsperado && !$cstEsperado)
                                                            <li>
                                                                O cadastro não corresponde aos pares
                                                                <strong>5405/500</strong> ou
                                                                <strong>5102/102</strong>.
                                                            </li>
                                                        @endif
                                                    </ul>

                                                    <div class="small mt-2">
                                                        Confirme regime tributário, NCM, descrição,
                                                        UF e natureza da operação.
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @elseif($possuiIndicioRevisao)
                                        <div class="alert alert-warning py-2 px-3 mb-0">
                                            <div class="d-flex align-items-start gap-2">
                                                <i class="bx bx-info-circle fs-5 mt-1"></i>

                                                <div>
                                                    <strong>Revisão necessária</strong>

                                                    @if($possuiCest && $parSemSt)
                                                        <div class="small mt-1">
                                                            O produto possui CEST, mas está configurado como
                                                            operação sem ST (5102/102). O CEST é apenas um indício;
                                                            confirme se a mercadoria está sujeita ao ICMS-ST nesta UF.
                                                        </div>
                                                    @else
                                                        <div class="small mt-1">
                                                            O produto está configurado com ICMS-ST (5405/500),
                                                            mas não possui CEST. Verifique o enquadramento e o
                                                            preenchimento do CEST.
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="alert alert-success py-2 px-3 mb-0">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="bx bx-check-circle fs-5"></i>

                                                <div>
                                                    <strong>Cadastro compatível</strong>

                                                    <div class="small">
                                                        CFOP e CSOSN formam um par compatível com
                                                        {{ $parComSt ? 'revenda interna com ICMS-ST' : 'revenda interna sem ICMS-ST' }}.
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    </div>
                                </td>
                                <td class="text-center">
                                    <a
                                        href="{{ route('produtos.edit', $produto->id) }}"
                                        class="btn btn-outline-primary btn-sm"
                                        title="Editar manualmente o cadastro tributário deste produto"
                                    >
                                        <i class="bx bx-edit-alt"></i>
                                        Editar
                                    </a>

                                    <small class="text-muted d-block mt-1">
                                        Edição manual
                                    </small>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    Nenhum produto encontrado.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2 mt-3">
                    <button class="btn btn-success" type="submit">
                        <i class="bx bx-bot"></i>
                        Analisar selecionados com IA
                    </button>

                    <small class="text-muted">
                        Selecione no máximo 20 produtos por análise.
                    </small>
                </div>
            </form>

            <div class="mt-3">
                {{ $data->appends(request()->all())->links() }}
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const selecionarTodos = document.getElementById('selecionar-todos');
    const selecionarDivergentes = document.getElementById('selecionar-divergentes');
    const limparSelecao = document.getElementById('limpar-selecao');
    const formulario = document.getElementById('form-auditoria');

    document.querySelectorAll('[data-bs-toggle="collapse"][data-bs-target^="#validacao-produto-"]')
        .forEach(function (botao) {
            const alvo = document.querySelector(botao.dataset.bsTarget);

            if (!alvo) {
                return;
            }

            alvo.addEventListener('show.bs.collapse', function () {
                botao.setAttribute('aria-expanded', 'true');

                const icone = botao.querySelector('.icone-validacao');
                if (icone) {
                    icone.classList.remove('bx-chevron-down');
                    icone.classList.add('bx-chevron-up');
                }
            });

            alvo.addEventListener('hide.bs.collapse', function () {
                botao.setAttribute('aria-expanded', 'false');

                const icone = botao.querySelector('.icone-validacao');
                if (icone) {
                    icone.classList.remove('bx-chevron-up');
                    icone.classList.add('bx-chevron-down');
                }
            });
        });

    function obterCheckboxes() {
        return Array.from(document.querySelectorAll('.produto-check'));
    }

    selecionarTodos.addEventListener('change', function () {
        obterCheckboxes().forEach(function (checkbox) {
            checkbox.checked = selecionarTodos.checked;
        });
    });

    selecionarDivergentes.addEventListener('click', function () {
        obterCheckboxes().forEach(function (checkbox) {
            const linha = checkbox.closest('tr');

            checkbox.checked =
                linha && linha.dataset.divergente === '1';
        });

        selecionarTodos.checked = false;
    });

    limparSelecao.addEventListener('click', function () {
        obterCheckboxes().forEach(function (checkbox) {
            checkbox.checked = false;
        });

        selecionarTodos.checked = false;
    });

    formulario.addEventListener('submit', function (event) {
        const selecionados = obterCheckboxes().filter(function (checkbox) {
            return checkbox.checked;
        });

        if (selecionados.length === 0) {
            event.preventDefault();

            alert('Selecione pelo menos um produto para realizar a análise.');
            return;
        }

        if (selecionados.length > 20) {
            event.preventDefault();

            alert('Selecione no máximo 20 produtos por análise.');
        }
    });
});
</script>
@endsection
