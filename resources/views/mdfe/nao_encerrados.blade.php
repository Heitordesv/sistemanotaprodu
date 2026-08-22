@extends('default.layout',['title' => 'Documentos não encerrados'])
@section('content')
<div class="page-content">
    <div class="card border-top border-0 border-4 border-primary">
        <div class="card-body p-5">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('mdfe.index')}}" type="button" class="btn btn-light btn-sm">
                        <i class="bx bx-arrow-back"></i> Voltar
                    </a>
                </div>
            </div>
            <div class="card-title d-flex align-items-center">
                <h5 class="mb-0 text-">Documentos não encerrados</h5>
            </div>

            <div class="table-responsive tbl-400">
                <table class="table mb-0 table-striped">
                    <thead>
                        <tr>

                            <th>Chave</th>
                            <th>Protocolo</th>
                            <th>Número</th>
                            <th>Data</th>
                            <th>Ação</th>
                        </tr>
                    </thead>

                    <tbody>
                        @if(count($data) == 0)
                        <tr>
                            <td colspan="5" class="center-align"><h5 class="red-text">Nada Encontrado</h5></td>
                        </tr>
                        @endif
                        @foreach($data as $m)

                        <tr class="datatable-row">

                            <td class="datatable-cell">
                                <span class="codigo" style="width: 250px;" id="chave">
                                    {{$m['chave']}}
                                </span>
                            </td>

                            <td class="datatable-cell">
                                <span class="codigo" style="width: 150px;" id="protocolo">
                                    {{$m['protocolo']}}
                                </span>
                            </td>
                            <td class="datatable-cell">
                                <span class="codigo" style="width: 100px;">
                                    {{$m['numero'] > 0 ? $m['numero'] : '--'}}
                                </span>
                            </td>
                            <td class="datatable-cell">
                                <span class="codigo" style="width: 100px;">
                                    {{$m['data'] != '' ? __data_pt($m['data']) : '--'}}
                                </span>
                            </td>

                            <td class="datatable-cell">
                                <form action="{{ route('mdfe.encerrar') }}" method="get" id="form">
                                    <input type="hidden" value="{{$m['chave']}}" name="chave">
                                    <input type="hidden" value="{{$m['protocolo']}}" name="protocolo">
                                    <button class="btn btn-sm btn-danger btn-confirm">Encerrar</button>
                                </form>
                                
                            </td>

                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
        </div>
    </div>
</div>
@endsection
