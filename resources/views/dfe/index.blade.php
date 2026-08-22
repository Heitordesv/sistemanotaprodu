@extends('default.layout',['title' => 'Manifesto'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
            </div>
            <div class="mt-3">
                <h5>Manifesto</h5>
            </div>
            <div class="col mt-3">
                {!!Form::open()->fill(request()->all())
                ->get()
                !!}
                <div class="row mt-2">
                    <div class="col-md-3">
                        {!!Form::date('start_date', 'Data inicial')
                        !!}
                    </div>
                    <div class="col-md-3    ">
                        {!!Form::date('end_date', 'Data final')
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!!Form::select('tipo', 'Tipo',
                        [
                        '' => 'Todos',
                        1 => 'Ciência',
                        2 => 'Confirmada',
                        3 => 'Desconhecido',
                        4 => 'Op. não Realizada'])
                        ->attrs(['class' => 'form-select'])
                        !!}
                    </div>
                    <div class="col-md-3 text-left ">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisa</button>
                        <a class="btn btn-danger" href="{{ route('dfe.index') }}"><i class="bx bx-eraser"></i> Limpar</a>
                    </div>
                </div>
                {!!Form::close()!!}

                <div class="mt-4">
                    <a href="{{ route('dfe.novaConsulta') }}" ype="button" class="btn btn-primary"><i class="bx bx-refresh"></i> Nova consulta de documentos</a>
                </div>
                <p class="mt-3">Total de registros: {{$data->total()}}</p>
                <hr />
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table mb-0 table-striped">
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>Documento</th>
                                        <th>Valor</th>
                                        <th>Data emissão</th>
                                        <th>Num. protocolo</th>
                                        <th>Chave</th>
                                        <th>Estado</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($data as $item)
                                    <tr>
                                        <td>{{ $item->nome }}</td>
                                        <td>{{ $item->documento }}</td>
                                        <td>{{ __moeda($item->valor) }}</td>
                                        <td>{{ __data_pt($item->data_emissao) }}</td>
                                        <td>{{ $item->num_prot }}</td>
                                        <td>{{ $item->chave }}</td>
                                        <td>{{ $item->estado() }}</td>
                                        <td>
                                            @if($item->tipo == 1 || $item->tipo == 2)
                                            <a href="{{ route('dfe.download', [$item->id]) }}" class="btn btn-success">Completa</a>
                                            <a class="btn btn-info" onclick="setChave('{{$item->chave}}')" data-toggle="modal" data-target="#modal-evento">Editar</a>

                                            <a target="_blank" href="{{ route('dfe.danfe', [$item->id]) }}" class="btn btn-primary">Imprimir</a>
                                          
                                            <a href="/dfe/downloadXml/{{$item->chave}}" type="submit" class="btn btn-warning">
                                                Baixar XML
                                            </a>


                                            @elseif($item->tipo == 1 || $item->tipo == 2)
                                            <a class="btn btn-danger">Desconhecida</a>
                                            @elseif($item->tipo == 4)
                                            <a class="btn btn-warning">Não realizada</a>
                                            @endif
                                            @if($item->tipo != 2)
                                            <a class="btn btn-info" onclick="setChave('{{$item->chave}}')" data-toggle="modal" data-target="#modal-evento">Manifestar</a>
                                            @endif

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
                {!! $data->appends(request()->all())->links() !!}
<div class="mt-3 text-right">
    <strong>Total:</strong> 
    {{ __moeda($data->sum('valor')) }}
</div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-evento" aria-modal="true" role="dialog" style="overflow:scroll;" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form class="modal-content" method="post" action="{{ route('dfe.manifestar') }}">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Manifestação NFe</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" name="chave" id="chave">
                <div class="col-md-6">
                    {!! Form::select('tipo', 'Tipo', [1 => "Ciencia", 2 => "Confirmação", 3 => "Desconhecimento", 4 => "Operação não realizada"])
                    ->attrs(['class' => 'form-select']) !!}
                </div>

                <div class="col-md-12 just d-none mt-3">
                    {!! Form::text('justificativa', 'Justificativa') !!}
                </div>
            </div>

            <div class="modal-footer">
                <button id="btn-corrige-send" type="submit" class="btn btn-info px-5">Manifestar</button>
            </div>

        </form>
    </div>
</div>


@section('js')
<script type="text/javascript">
    function setChave(chave) {
        $('#chave').val(chave)
        $('#modal-evento').modal('show')
    }

    $(document).on("change", "#inp-tipo", function() {
        if ($(this).val() > 2) {
            $('.just').removeClass('d-none')
        } else {
            $('.just').addClass('d-none')
        }
    })

</script>
@endsection
@endsection
