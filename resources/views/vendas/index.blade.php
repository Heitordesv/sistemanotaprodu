@extends('default.layout',['title' => 'Vendas'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                </div>
            </div>
            <div class="col">
                @if(isset($config))
                <input type="hidden" id="pass" value="{{ $config->senha_remover }}">
                @endif
                <h6 class="mb-0 text-uppercase">Pesquisar vendas</h6>
                {!!Form::open()->fill(request()->all())
                ->get()
                !!}
                <div class="row mt-3">
                    <div class="col-md-2">
                        {!!Form::select('tipo', 'Tipo de pesquisa',
                        [0 => 'Razão Social',
                        1 => 'Nome Fantasia',
                        ])
                        ->attrs(['class' => 'select2'])
                        !!}
                    </div>
                    <div class="col-md-4">
                        {!!Form::select('cliente_id', 'Cliente')
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!!Form::select('pesquisa_data', 'Pesquisa por data',
                        ['created_at' => 'Data Registro',
                        'data_entrega' => 'Data Entrega'])
                        ->attrs(['class' => 'select2'])
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!!Form::date('start_date', 'Data inicial')
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!!Form::date('end_date', 'Data final')
                        !!}
                    </div>
                    <div class="col-md-2 mt-3">
                        {!!Form::select('estado_emissao', 'Estado',
                        ['' => 'Todas',
                        'rejeitado' => 'Rejeitadas',
                        'cancelado' => 'Canceladas',
                        'aprovado' => 'Aprovadas',
                        ])
                        ->attrs(['class' => 'select2'])
                        !!}
                    </div>
                    <div class="col-md-2 mt-3">
                        {!!Form::tel('numero_nfe', 'Número NFe')->attrs(['class' => ''])
                        !!}
                    </div>
                    <div class="col-md-2 mt-3">
                        {!!Form::date('data_emissao', 'Data emissão')->attrs(['class' => ''])
                        !!}
                    </div>

                    @if(empresaComFilial())
                    {!! __view_locais_select_filtro("Local", isset($filial_id) ? $filial_id : '') !!}
                    @endif

                    <div class="col-md-5 text-left mt-3">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('vendas.index') }}"><i class="bx bx-eraser"></i> Limpar</a>
                    </div>
                </div>
                {!!Form::close()!!}
                <hr />
                <h6>Lista de Vendas</h6>
                {{-- <p>Registros: {{ $data->total() }}</p> --}}
                <div class="row">
                    <div class="col-12">
                        <a href="{{ route('vendas.create')}}" type="button" class="btn btn-success">
                            <i class="bx bx-plus"></i> Nova venda
                        </a>
                        @if($config->arquivo != null)
                        <button class="btn btn-dark float-right btn-consulta-status">
                            Consultar Status Sefaz
                        </button>
                        @endif
                    </div>
                </div>
                @if($contigencia != null)
                <h3 class="text-danger mt-3">Contigência ativada</h3>
                <span class="text-danger">Tipo: {{$contigencia->tipo}}</span><br>
                <span class="text-danger">Data de ínicio: {{ __data_pt($contigencia->created_at) }}</span>
                @endif
                <div class="card mt-3">
                    <div class="card-body">
                        <div class="table-responsive tbl-400">
                            <table class="table mb-0 table-striped">
                                <thead class="">
                                    <tr>
                                        <th>#</th>
                                        <th>Ações</th>
                                        <th>Cliente</th>
                                        <th>Data de Registro</th>
                                        <th>Tipo de Pagamento</th>
                                        @if(empresaComFilial())
                                        <th>Local</th>
                                        @endif
                                        <th>Estado</th>
                                        <th>NFe</th>
                                        <th>Usuário</th>
                                        <th>Valor Integral</th>
                                        <th>Desconto</th>
                                        <th>Acréscimo</th>
                                        <th>Ecommerce</th>
                                        <th>Valor Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($data as $item)
                                    <tr>
                                        <td>
                                            <input type="checkbox" value="{{$item->id}}" data-email="{{$item->cliente->email}}" class="checkbox" name="" data-status="{{$item->estado_emissao}}" data-numero_nfe="{{$item->numero_nfe}}">
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Ações</button>
                                                <ul class="dropdown-menu" style="z-index: 999">
                                                    <form action="{{ route('vendas.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
                                                        @method('delete')
                                                        @csrf
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('vendas.edit', $item->id) }}">Editar</a>
                                                        </li>
                                                        @if($item->estado_emissao == 'novo' || $item->estado_emissao == 'rejeitado')
                                                        <li>
                                                            <button class="dropdown-item btn-delete">Apagar</button>
                                                        </li>
                                                        @endif
                                                        <li>
                                                            <a target="_blank" class="dropdown-item" href="{{ route('vendas.xml-temp', $item->id) }}">XML temporário</a>
                                                        </li>

                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('nfe.state-fiscal', $item->id) }}">Alterar estado fiscal</a>
                                                        </li>

                                                        <li>
                                                            <a target="_blank" class="dropdown-item" href="{{ route('vendas.print', $item->id) }}">Imprimir</a>
                                                        </li>

                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('vendas.show', $item->id) }}">Ver</a>
                                                        </li>

                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('vendas.clone', $item->id) }}">Duplicar</a>
                                                        </li>
                                                    </form>
                                                </ul>
                                            </div>
                                        </td>
                                        
                                        <td>{{ $item->cliente->razao_social }}</td>
                                        <td>{{ __data_pt($item->created_at, 1) }}</td>
                                        <td>{{ $item->getTipoPagamento() }}</td>
        
                                        @if(empresaComFilial())
                                        <td>
                                            <span class="codigo" style="width: 150px;">
                                                {{ $item->filial_id ? $item->filial->descricao : 'Matriz' }}
                                            </span>
                                        </td>
                                        @endif
                                        <td>
                                            {!! $item->estadoEmissao() !!}
                                            @if($item->contigencia)
                                            <br>
                                            <span class="text-danger">contigência</span>
                                            @endif

                                        </td>
                                        <td>{{ $item->numero_nfe > 0 ? $item->numero_nfe : '--' }}</td>
                                        <td>{{ $item->usuario->nome }}</td>
                                        <td>{{ __moeda($item->valor_total) }}</td>
                                        <td>{{ __moeda($item->desconto) }}</td>
                                        <td>{{ __moeda($item->acrescimo) }}</td>
                                        <td>
                                            @if($item->pedido_nuvemshop_id > 0)
                                            NUVEMSHOP
                                            <a href="{{ route('nuvemshop-pedidos.show', [$item->pedidoNuvemShop->pedido_id]) }}" class="btn btn-link">ver pedido</a>
                                            @else
                                            NÃO
                                            @endif
                                        </td>
                                        <td>{{ __moeda($item->valor_total - $item->desconto + $item->acrescimo) }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="14" class="text-center">Nada encontrado</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="row g-3 mt-2">
                            <div class="col-md-2">
                                <button id="btn-enviar" type="button" class="btn btn-info btn-action spinner-white spinner-right px-2" style="width: 100%" href="">Transmitir</button>
                            </div>
                            <div class="col-md-2">
                                <button id="btn-imprimir" class="btn btn-secondary btn-action spinner-white spinner-right" style="width: 100%">Imprimir</button>
                            </div>
                            <div class="col-md-2">
                                <button id="btn-consultar" type="button" class="btn btn-primary btn-action spinner-white spinner-right" style="width: 100%">Consultar</button>
                            </div>
                            <div class="col-md-2">
                                <button id="btn-cancelar" type="button" class="btn btn-danger btn-action spinner-white spinner-right" style="width: 100%">Cancelar</button>
                            </div>
                            <div class="col-md-2">
                                <button id="btn-corrigir" type="button" class="btn btn-warning spinner-white btn-action spinner-right" style="width: 100%" href="">CC-e</button>
                            </div>
                            <div class="col-md-2">
                                <button id="btn-inutilizar" type="button" class="btn btn-outline-secondary spinner-white spinner-right" style="width: 100%">Inutilizar NFe</button>
                            </div>
                            <div class="col-md-2">
                                <button type="button" data-href="{{ route('vendas.danfe-temp') }}" class="btn btn-dark spinner-white btn-action spinner-right px-1" style="width: 100%" id="btn-danfe-temp">Danfe Temporária</button>
                            </div>
                            <div class="col-md-2">
                                <button id="btn-enviar-email" type="button" data-bs-toggle="modal" data-bs-target="#modal-email" class="btn btn-primary spinner-white btn-action spinner-right" style="width: 100%">Enviar E-mail</button>
                            </div>

                            <div class="col-md-2">
                                <button id="btn-imprimir-cce" type="button" class="btn btn-warning spinner-white btn-action spinner-right" style="width: 100%">Imprimir CC-e</button>
                            </div>
                            <div class="col-md-2">
                                <button id="btn-imprimir-cancela" type="button" class="btn btn-danger spinner-white btn-action spinner-right" style="width: 100%">Imprimir Cancela</button>
                            </div>
                            <div class="col-md-2">
                                <button id="btn-baixar-xml" type="button" class="btn btn-success btn-action spinner-white spinner-right px-2" style="width: 100%">Baixar XML</button>
                            </div>
                             <div class="col-md-2 mt-2">
                            <button style="width: 100%" data-bs-toggle="modal" data-bs-target="#modal-whatsApp" class="btn btn-info px-3">WhatsApp</button>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
            
            @include('modals._whatsApp', ['not_submit' => true])

            @isset($data->appends)
            {!! $data->appends(request()->all())->links() !!}
            @endisset
        </div>
    </div>
</div>
<div class="modal fade" id="modal-corrigir" tabindex="-1">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">
					Corrigir NFe <strong class="text-warning numero_nfe"></strong>
				</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
			</div>

			<div class="modal-body">
				{!! Form::text('motivo-corrige', 'Descrição da correção')
					->attrs(['id' => 'inp-motivo-corrige']) !!}
			</div>

			<div class="modal-footer">
				<button id="btn-corrige-send" class="btn btn-warning px-5">
					Corrigir
				</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="modal-cancelar" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    Cancelar NFe <strong class="text-danger numero_nfe"></strong>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                {!! Form::text('motivo-cancela', 'Justificativa')
                    ->attrs(['id' => 'inp-motivo-cancela']) !!}
            </div>

            <div class="modal-footer">
                <button id="btn-cancelar-send" type="button" class="btn btn-danger px-5">
                    Cancelar
                </button>
            </div>

        </div>
    </div>
</div>


<div class="modal fade" id="modal-inutilizar" aria-modal="true" role="dialog" style="overflow:scroll;" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">INUTILIZAÇÃO DE NÚMERO(s) DE NFe</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-3">
                        {!! Form::tel('numero_inicial', 'Nº inicial') !!}
                    </div>
                    <div class="col-md-3">
                        {!! Form::tel('numero_final', 'Nº final') !!}
                    </div>
                    <div class="col-md-12 mt-3">
                        {!! Form::text('motivo-inutiliza', 'Justificativa') !!}
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button id="btn-inutiliza-send" type="button" class="btn btn-primary px-5">Inutilizar</button>
            </div>
        </div>
    </div>
</div>

@include('modals._emailRemessa', ['not_submit' => true])


@endsection

@section('js')

<script type="text/javascript" src="{{ asset('js/nf.js') }}?v={{ filemtime(public_path('js/nf.js')) }}"></script>
<script type="text/javascript" src="{{ asset('js/vendas.js') }}?v={{ filemtime(public_path('js/vendas.js')) }}"></script>

@endsection
