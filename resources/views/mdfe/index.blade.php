@extends('default.layout',['title' => 'Lista de MDFe'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('mdfe.create')}}" type="button" class="btn btn-success">
                        <i class="bx bx-plus"></i> Nova MDFe
                    </a>
                    <a href="{{ route('mdfe.nao-encerrados') }}" type="button" class="btn btn-danger">
                        <i class="bx bx-list-ol"></i>Ver documentos não encerrados
                    </a>
                    <button class="btn btn-dark" id="btn-importar_nfe" data-bs-toggle="modal" data-bs-target="#modal-importar_nfe">Importar NFe(s) emitida(s)</button>
                </div>
            </div>
            <hr>
            <div class="col">
                <h6 class="mb-0 text-uppercase">LISTA DE MDFe</h6>
                <p class="mt-2">Registros: </p>
                {!!Form::open()->fill(request()->all())
                ->get()
                !!}
                <div class="row mt-2">
                    <div class="col-md-3 mt-3">
                        {!!Form::date('data_inicla', 'Data inicial')
                        !!}
                    </div>
                    <div class="col-md-3 mt-3">
                        {!!Form::date('data_final', 'Data final')
                        !!}
                    </div>
                    <div class="col-md-2 mt-3">
                        {!!Form::select('estado', 'Estado',
                        [
                        'novo' => 'Disponiveis',
                        'rejeitado' => 'Rejeitadas',
                        'cancelado' => 'Canceladas',
                        'aprovado' => 'Aprovadas',
                        '' => 'Todas'
                        ])->attrs(['class' => 'form-select'])
                        !!}
                    </div>
                    @if(empresaComFilial())
                    {!! __view_locais_select_filtro("Local", isset($filial_id) ? $filial_id : '') !!}
                    @endif
                    <div class="col-md-4 mt-3 text-left">
                        <br>
                        <button class="btn btn-primary" type="submit"><i class="bx bx-search"></i>Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('mdfe.index') }}"><i class="bx bx-eraser"></i> Limpar</a>
                    </div>
                </div>
                {!!Form::close()!!}
                <hr />
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive tbl-400">
                            <table class="table mb-0 table-striped">
                                <thead class="">
                                    <tr>
                                        <th>#</th>
                                        <th>Ações</th>
                                        <th>Data início da viagem</th>
                                        <th>Data de criação</th>
                                        <th>Cnpj contratante</th>
                                        <th>Estado fiscal</th>
                                        @if(empresaComFilial())
                                        <th>Local</th>
                                        @endif
                                        <th>Chave</th>
                                        <th>Número</th>
                                        <th>Veículo tração</th>
                                        <th>Quantidade carga</th>
                                        <th>Valor carga</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($data as $item)
                                    <tr>
                                        <td>
                                            <input type="checkbox" value="{{$item->id}}" class="checkbox" name="" data-status="{{$item->estado_emissao}}" data-numero_mdfe="{{$item->mdfe_numero}}">
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Ações</button>
                                                <ul class="dropdown-menu" style="z-index: 999">
                                                    <form action="{{ route('mdfe.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
                                                        @method('delete')
                                                        @csrf
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('mdfe.edit', $item->id) }}">Editar</a>
                                                        </li>
                                                        @if($item->estado_emissao == 'novo' || $item->estado_emissao == 'rejeitado')
                                                        <li>
                                                            <button class="dropdown-item btn-delete">Remover</button>
                                                        </li>
                                                        @endif

                                                        @if($item->estado_emissao != 'aprovado')
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('mdfe.xml-temp', $item->id) }}">XML temporário</a>
                                                        </li>
                                                        @endif

                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('mdfe.estadoFiscal', $item->id ) }}">Alterar estado fiscal</a>
                                                        </li>
                                                    </form>
                                                </ul>
                                            </div>
                                        </td>
                                        <td>{{ __data_pt($item->data_inicio_viagem, 0) }}</td>
                                        <td>{{ __data_pt($item->created_at, 0) }}</td>
                                        <td>{{ $item->cnpj_contratante }}</td>
                                        <td>{!! $item->estadoEmissao($item->estado_emissao) !!}</td>
                                        @if(empresaComFilial())
                                        <td>
                                            <span>
                                                {{ $item->filial_id ? $item->filial->descricao : 'Matriz' }}
                                            </span>
                                        </td>
                                        @endif
                                        <td>{{ $item->chave }}</td>
                                        <td>{{ $item->mdfe_numero > 0 ? $item->mdfe_numero : '--' }}</td>
                                        <td>{{ $item->veiculoTracao->marca }} - {{ $item->veiculoTracao->placa }} </td>
                                        <td>{{ $item->quantidade_carga }}</td>
                                        <td>{{ __moeda( $item->valor_carga) }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="12" class="text-center">Nada encontrado</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="row g-3 mt-2">
                            <div class="col-md-2">
                                <button type="button" disabled id="btn-enviar" class="btn btn-info spinner-white spinner-right px-2" style="width: 100%" href="">Transmitir</button>
                            </div>
                            <div class="col-md-2">
                                <button type="button" id="btn-imprimir" disabled class="btn btn-outline-secondary spinner-white spinner-right" style="width: 100%" href="">Imprimir</button>
                            </div>
                            <div class="col-md-2">
                                <button type="button" id="btn-consultar" disabled class="btn btn-primary spinner-white spinner-right" style="width: 100%" href="">Consultar</button>
                            </div>
                            <div class="col-md-2">
                                <button type="button" id="btn-cancelar" disabled class="btn btn-danger spinner-white spinner-right" style="width: 100%" href="">Cancelar</button>
                            </div>
                            {{-- e-mail --}}
                            <div class="col-md-2">
                                <button id="btn-enviar-email" type="button" data-bs-toggle="modal" data-bs-target="#modal-emailMdfe" class="btn btn-primary spinner-white btn-action spinner-right" style="width: 100%">Enviar E-mail</button>
                            </div>
                            <div class="col-md-2">
                                <button type="button" id="btn-xml-temp" class="btn btn-outline-secondary spinner-white spinner-right" disabled style="width: 100%" href="">XML Temporário</button>
                            </div>
                            <div class="col-md-2">
                                <button id="btn-baixar-xml" type="button" class="btn btn-success btn-action spinner-white spinner-right px-2" style="width: 100%">Baixar XML</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {!! $data->appends(request()->all())->links() !!}
        </div>
    </div>
</div>

<div class="modal fade" id="modal-cancelar" aria-modal="true" role="dialog" style="overflow:scroll;" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancelar MDFe <strong class="text-danger numero_mdfe"></strong></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="col-md-12">
                    {!! Form::text('motivo-cancela', 'Justificativa') !!}
                </div>
            </div>
            <div class="modal-footer">
                <button id="btn-cancelar-send" type="button" class="btn btn-danger px-5">Cancelar</button>
            </div>
        </div>
    </div>
</div>
@section('js')
<script type="text/javascript">
    $('#btn-importar_nfe').click(() => {
        filtro()
    })

    function filtro() {
        let empresa_id = $('#empresa_id').val()
        let start_date = $('#inp-start_date').val()
        let end_date = $('#inp-end_date').val()

        $.get(path_url + "api/mdfe/vendasAprovadas", {
                empresa_id: empresa_id
                , start_date: start_date
                , end_date: end_date
            })
            .done((e) => {
                console.log(e)
                $('.tbl-vendas tbody').html(e)

            })
            .fail((e) => {
                console.log(e)
            })
    }

    $('body').on('click', '.btn-filtro', function() {
        filtro()
    })

    $('body').on('click', '#btn-importar', function() {
        console.clear()
        let id = []
        $('table .checkbox:checked').each(function(e, v) {
            id.push(v.value)
            console.log(id)
            location.href = path_url + "mdfe/createByVendas/" + id
        })
    })

</script>
<script src="/js/mdfe.js"></script>
@endsection

@include('modals._importar_nfe')
@include('modals._emailMdfe', ['not_submit' => true])

@endsection
