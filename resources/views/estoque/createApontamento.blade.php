@extends('default.layout', ['title' => 'Novo apontamento de estoque'])

@section('content')
<div class="page-content">
    @if(session('error') || $errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Não foi possível atualizar o estoque.</strong>
            @if(session('error'))
                <div>{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif

    <div class="card border-top border-0 border-4 border-primary">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div>
                    <h5 class="mb-1">Novo apontamento de estoque</h5>
                    <p class="text-muted mb-0">Localize o produto pelo nome ou use o leitor de código de barras.</p>
                </div>
                <a class="btn btn-outline-primary" href="{{ route('estoque.listaApontamento') }}">
                    <i class="bx bx-list-ul"></i> Histórico
                </a>
            </div>

            {!! Form::open()->post()->route('estoque.store')->attrs(['id' => 'form-apontamento']) !!}
            <div class="row">
                <div class="col-12 mt-3">
                    <label class="form-label d-block">Como deseja localizar o produto?</label>
                    <div class="btn-group" role="group" aria-label="Modo de pesquisa">
                        <input type="radio" class="btn-check" name="modo_busca" id="modo_codigo"
                               value="codigo" {{ old('modo_busca', 'codigo') === 'codigo' ? 'checked' : '' }}>
                        <label class="btn btn-outline-primary" for="modo_codigo">
                            <i class="bx bx-barcode-reader"></i> Código de barras
                        </label>

                        <input type="radio" class="btn-check" name="modo_busca" id="modo_nome"
                               value="nome" {{ old('modo_busca') === 'nome' ? 'checked' : '' }}>
                        <label class="btn btn-outline-primary" for="modo_nome">
                            <i class="bx bx-search"></i> Pesquisar por nome
                        </label>
                    </div>
                </div>

                <div class="col-lg-6 mt-3 d-none" id="campo-produto-nome">
                    {!! Form::select('produto_id', 'Pesquisar produto pelo nome')
                        ->attrs(['id' => 'produto_id']) !!}
                    <small class="text-muted">Comece a digitar o nome e selecione o produto correto.</small>
                </div>

                <div class="col-lg-6 mt-3" id="campo-codigo-barras">
                    <label for="inp-codBarras" class="form-label">Código de barras</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bx bx-barcode-reader"></i></span>
                        <input
                            id="inp-codBarras"
                            type="text"
                            name="codBarras"
                            value="{{ old('codBarras') }}"
                            class="form-control"
                            placeholder="Digite ou escaneie o código"
                            autocomplete="off">
                    </div>
                    <small class="text-muted">O apontamento será enviado ao pressionar Enter no leitor.</small>
                </div>

                <div class="col-md-3 mt-3">
                    {!! Form::select(
                        'tipo',
                        'Movimentação',
                        [0 => 'Saída / redução', 1 => 'Entrada / incremento'],
                        old('tipo', session('ultimo_tipo_apontamento', 0))
                    ) !!}
                </div>

                <div class="col-md-3 mt-3">
                    {!! Form::tel('quantidade', 'Quantidade')
                        ->value(old('quantidade', '1'))
                        ->attrs(['inputmode' => 'decimal', 'placeholder' => '1,000']) !!}
                </div>

                <div class="col-md-6 mt-3">
                    {!! Form::text('observacao', 'Observação')
                        ->value(old('observacao'))
                        ->attrs(['maxlength' => 200]) !!}
                </div>

                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-primary px-5" id="btn-salvar">
                        <i class="bx bx-save"></i> Atualizar estoque
                    </button>
                    <a href="{{ route('estoque.index') }}" class="btn btn-light">Voltar</a>
                </div>
            </div>
            {!! Form::close() !!}
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div>
                    <h6 class="mb-1">Posição atual do estoque</h6>
                    <span class="text-muted">Total de registros: {{ $data->total() }}</span>
                </div>
                <div class="text-end">
                    <div><strong>Custo:</strong> {{ __moeda($somaEstoque['compra']) }}</div>
                    <div><strong>Venda:</strong> {{ __moeda($somaEstoque['venda']) }}</div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            @if(empresaComFilial()) <th>Local</th> @endif
                            <th>Categoria</th>
                            <th class="text-end">Quantidade</th>
                            <th class="text-end">Custo</th>
                            <th class="text-end">Venda</th>
                            <th>Movimentação</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $item)
                            <tr>
                                <td>
                                    {{ optional($item->produto)->nome ?? 'Produto removido' }}
                                    @if(optional($item->produto)->grade)
                                        ({{ optional($item->produto)->str_grade }})
                                    @endif
                                </td>
                                @if(empresaComFilial())
                                    <td>{{ $item->filial ? $item->filial->descricao : 'Matriz' }}</td>
                                @endif
                                <td>{{ optional(optional($item->produto)->categoria)->nome ?? '-' }}</td>
                                <td class="text-end">{{ __estoque($item->quantidade) }}</td>
                                <td class="text-end">{{ __moeda($item->valor_compra) }}</td>
                                <td class="text-end">{{ __moeda(optional($item->produto)->valor_venda ?? 0) }}</td>
                                <td>
                                    @if($item->produto)
                                        <a href="{{ route('produtos.movimentacao', $item->produto->id) }}"
                                           class="btn btn-primary btn-sm" title="Ver movimentações">
                                            <i class="bx bx-list-ul"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ empresaComFilial() ? 7 : 6 }}" class="text-center text-muted py-4">
                                    Nenhum estoque cadastrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $data->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
$(document).ready(function () {
    const barcode = document.getElementById('inp-codBarras');
    const form = document.getElementById('form-apontamento');
    const button = document.getElementById('btn-salvar');

    const product = $('#produto_id');
    const codeMode = document.getElementById('modo_codigo');
    const nameMode = document.getElementById('modo_nome');
    const codeField = document.getElementById('campo-codigo-barras');
    const nameField = document.getElementById('campo-produto-nome');

    function updateSearchMode() {
        const byName = nameMode.checked;
        nameField.classList.toggle('d-none', !byName);
        codeField.classList.toggle('d-none', byName);

        if (byName) {
            barcode.value = '';
            setTimeout(function () {
                if (product.data('select2')) {
                    product.select2('open');
                } else {
                    product.trigger('focus');
                }
            }, 100);
        } else {
            product.val(null).trigger('change');
            barcode.focus();
            barcode.select();
        }
    }

    codeMode.addEventListener('change', updateSearchMode);
    nameMode.addEventListener('change', updateSearchMode);
    updateSearchMode();

    form.addEventListener('submit', function () {
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Atualizando...';
    });

    barcode.addEventListener('keydown', function (event) {
        if (event.key === 'Enter' && barcode.value.trim() !== '') {
            event.preventDefault();
            form.requestSubmit();
        }
    });
});
</script>
@endsection
