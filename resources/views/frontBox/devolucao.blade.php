@extends('default.layout', ['title' => 'Devolução'])
@section('content')
@php
    $usuarioSessao = session('user_logged');
    $empresaDevolucaoId = (int) (
        request()->empresa_id ??
        ($usuarioSessao['empresa'] ?? 0)
    );

    if (!isset($administradores)) {
        $administradores = \App\Models\Usuario::query()
            ->where('empresa_id', $empresaDevolucaoId)
            ->where('adm', 1)
            ->where('ativo', 1)
            ->orderBy('nome')
            ->get(['id', 'nome']);
    }

    if (!isset($historicoDevolucoes)) {
        $historicoDevolucoes = \Illuminate\Support\Facades\Schema::hasTable(
            'autorizacoes_devolucao_caixa'
        )
            ? \App\Models\AutorizacaoDevolucao::where(
                'empresa_id',
                $empresaDevolucaoId
            )->orderBy('id', 'desc')->limit(100)->get()
            : collect();
    }
@endphp
<div class="page-content">
    <div class="card">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="col">
                    <h6 class="mb-0 text-uppercase">Devolução</h6>
                    {!! Form::open()->fill(request()->all())->get() !!}
                    <div class="row mt-3">
                        <div class="col-md-4">
                            {!! Form::text('nome', 'Nome') !!}
                        </div>
                        <div class="col-md-2">
                            {!! Form::tel('nfce', 'NFCe') !!}
                        </div>
                        <div class="col-md-2">
                            {!! Form::tel('valor', 'Valor')->attrs(['class' => 'moeda']) !!}
                        </div>
                        <div class="col-md-2">
                            {!! Form::date('start_date', 'Data') !!}
                        </div>
                        <div class="col-md-3 text-left">
                            <br>
                            <button class="btn btn-primary" type="submit">
                                <i class="bx bx-search"></i> Pesquisar
                            </button>
                            <a id="clear-filter" class="btn btn-danger" href="{{ route('frenteCaixa.devolucao') }}">
                                Limpar
                            </a>
                        </div>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
            <hr>
            <div class="row row-cols-auto g-3" style="margin-left: 60px">
                <div class="col">
                    <a class="btn btn-info px-3 radius-10" href="{{ route('frenteCaixa.index') }}">
                        Frente de Caixa
                    </a>
                </div>
                <div class="col">
                    <a class="btn btn-danger px-3 radius-10" data-bs-toggle="modal" data-bs-target="#modal-inutilizar_nfce">
                        Inutilizar
                    </a>
                </div>
            </div>
            <hr>
            <div class="alert alert-secondary">
                <strong>Regra de segurança:</strong>
                administradores podem devolver diretamente. Outros usuários precisam informar a senha de um administrador ativo desta empresa.
            </div>
            <div class="table-responsive tbl-400">
                <table class="table mb-0 table-striped">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Data</th>
                            <th>Tipo de Pagamento</th>
                            <th>Estado</th>
                            <th>Nº NFCe</th>
                            <th>Usuário</th>
                            <th>Valor</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data as $item)
                        <tr>
                            <td>{{ $item->cliente->razao_social ?? 'Consumidor Final' }}</td>
                            <td>{{ __data_pt($item->created_at, 0) }}</td>
                            <td>{{ $item->getTipoPagamento($item->tipo_pagamento) }}</td>
                            <td>{!! $item->estadoEmissao() !!}</td>
                            <td>{{ $item->numero_nfce ?? '0' }}</td>
                            <td>{{ $item->usuario->nome }}</td>
                            <td>{{ __moeda($item->valor_total) }}</td>
                            <td>
                                <form
                                    action="{{ route('frenteCaixa.destroy', $item->id) }}"
                                    method="post"
                                    id="form-{{ $item->id }}"
                                >
                                    @method('delete')
                                    @csrf
                                    <input type="hidden" name="admin_id" class="devolucao-admin-id">
                                    <input type="hidden" name="admin_senha" class="devolucao-admin-senha">

                                    @if ($item->estado_emissao == 'aprovado')
                                    <button
                                        type="button"
                                        onclick="modalCancelar({{ $item->id }}, {{ $item->numero_nfce ?? 0 }})"
                                        class="btn btn-danger btn-sm"
                                        title="Cancelar NFCe e devolver estoque"
                                    >
                                        <i class="bx bx-error"></i>
                                    </button>
                                    @elseif ($item->estado_emissao == 'cancelado')
                                    <span class="badge bg-danger">Venda devolvida</span>
                                    @elseif (!$item->impedeDelete)
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-danger btn-devolver-venda"
                                        data-form="form-{{ $item->id }}"
                                        data-venda="{{ $item->id }}"
                                        title="Cancelar venda e devolver estoque"
                                    >
                                        <i class="bx bx-undo"></i>
                                    </button>
                                    @endif
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">Nada encontrado</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-body p-4">
            <h6 class="mb-3 text-uppercase">Histórico de autorizações de devolução</h6>
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Venda</th>
                            <th>Tipo</th>
                            <th>Usuário logado</th>
                            <th>Autorizado por</th>
                            <th>Valor original</th>
                            <th>Motivo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($historicoDevolucoes as $historico)
                        <tr>
                            <td>{{ __data_pt($historico->created_at, 0) }}</td>
                            <td>
                                #{{ $historico->venda_caixa_id }}
                                @if ($historico->numero_nfce)
                                / NFCe {{ $historico->numero_nfce }}
                                @endif
                            </td>
                            <td>
                                {{ $historico->tipo == 'cancelamento_fiscal' ? 'Fiscal' : 'Não fiscal' }}
                            </td>
                            <td>
                                {{ $historico->usuario_solicitante_nome }}
                                <small class="text-muted">#{{ $historico->usuario_solicitante_id }}</small>
                            </td>
                            <td>
                                {{ $historico->usuario_autorizador_nome }}
                                <small class="text-muted">#{{ $historico->usuario_autorizador_id }}</small>
                            </td>
                            <td>{{ __moeda($historico->valor_venda) }}</td>
                            <td>{{ $historico->motivo ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">Nenhuma devolução autorizada registrada.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-autorizar-devolucao" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Autorizar devolução da venda <span id="devolucao-venda-numero"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    Solicite a um administrador da empresa que selecione o nome e informe a própria senha.
                </div>
                <label for="devolucao_admin_id" class="form-label">Administrador autorizador</label>
                <select id="devolucao_admin_id" class="form-select mb-3">
                    <option value="">Selecione o administrador</option>
                    @forelse ($administradores as $administrador)
                    <option value="{{ $administrador->id }}">{{ $administrador->nome }}</option>
                    @empty
                    <option value="" disabled>Nenhum administrador ativo encontrado</option>
                    @endforelse
                </select>

                <label for="devolucao_admin_senha" class="form-label">Senha do administrador</label>
                <input
                    type="password"
                    id="devolucao_admin_senha"
                    class="form-control"
                    autocomplete="new-password"
                    maxlength="100"
                >
                <input type="hidden" id="devolucao_form_id">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Voltar</button>
                <button type="button" id="btn-confirmar-autorizacao-devolucao" class="btn btn-danger">
                    Autorizar e devolver
                </button>
            </div>
        </div>
    </div>
</div>

@include('modals.frontBox._inutilizar_nfce', ['not_submit' => true])
@include('modals.frontBox._cancelar_nfce', ['not_submit' => true])

@endsection
@section('js')
<script>
    window.usuarioDevolucaoEhAdministrador = @json((bool) is_adm());

    function mensagemErroDevolucao(xhr) {
        var resposta = xhr.responseJSON;

        if (resposta && resposta.errors) {
            var campos = Object.keys(resposta.errors);
            if (campos.length && resposta.errors[campos[0]].length) {
                return resposta.errors[campos[0]][0];
            }
        }

        if (resposta && resposta.message) {
            return resposta.message;
        }

        if (typeof resposta === 'string') {
            return resposta;
        }

        return 'Não foi possível concluir a devolução.';
    }

    function enviarDevolucaoNaoFiscal(formId, administradorId, senha) {
        var form = $('#' + formId);
        form.find('.devolucao-admin-id').val(administradorId || '');
        form.find('.devolucao-admin-senha').val(senha || '');

        var botao = $('.btn-devolver-venda[data-form="' + formId + '"]');
        botao.prop('disabled', true);

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            headers: {
                'Accept': 'application/json'
            }
        })
        .done(function (resposta) {
            $('#modal-autorizar-devolucao').modal('hide');
            swal('Devolução concluída', resposta.message, 'success').then(function () {
                location.reload();
            });
        })
        .fail(function (xhr) {
            swal('Não foi possível devolver', mensagemErroDevolucao(xhr), 'error');
        })
        .always(function () {
            botao.prop('disabled', false);
            form.find('.devolucao-admin-senha').val('');
            $('#devolucao_admin_senha').val('');
        });
    }

    $('.btn-devolver-venda').on('click', function () {
        var formId = $(this).data('form');
        var vendaId = $(this).data('venda');

        if (window.usuarioDevolucaoEhAdministrador) {
            swal({
                title: 'Confirmar devolução?',
                text: 'A venda #' + vendaId + ' será mantida, marcada como cancelada e o estoque será devolvido. A operação ficará registrada em seu nome.',
                icon: 'warning',
                buttons: ['Voltar', 'Confirmar'],
                dangerMode: true
            }).then(function (confirmado) {
                if (confirmado) {
                    enviarDevolucaoNaoFiscal(formId, '', '');
                }
            });
            return;
        }

        $('#devolucao_form_id').val(formId);
        $('#devolucao-venda-numero').text('#' + vendaId);
        $('#devolucao_admin_id').val('');
        $('#devolucao_admin_senha').val('');
        $('#modal-autorizar-devolucao').modal('show');
    });

    $('#btn-confirmar-autorizacao-devolucao').on('click', function () {
        var formId = $('#devolucao_form_id').val();
        var administradorId = $('#devolucao_admin_id').val();
        var senha = $('#devolucao_admin_senha').val();

        if (!administradorId || !senha) {
            swal('Autorização necessária', 'Selecione o administrador e informe a senha.', 'warning');
            return;
        }

        enviarDevolucaoNaoFiscal(formId, administradorId, senha);
    });
</script>
<script type="text/javascript" src="{{ asset('js/nfce.js') }}?v={{ filemtime(public_path('js/nfce.js')) }}"></script>
@endsection
