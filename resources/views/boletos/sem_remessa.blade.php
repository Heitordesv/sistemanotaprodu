@extends('default.layout', ['title' => 'Boletos sem Remessa'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">

            <div class="col">
                <h6 class="mb-0 text-uppercase">Boletos sem Remessa</h6>
                <hr />
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table mb-0 table-striped">
                                <thead class="">
                                    <tr>
                                        <th></th>
                                        <th>Cliente</th>
                                        <th>Valor</th>
                                        <th>Banco</th>
                                        <th>Vencimento</th>
                                        <th>Nº boleto</th>
                                        <th>Nº documento</th>
                                        <th>Juros</th>
                                        <th>Multa</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($data as $item)
                                    <tr>
                                        <td>
                                            <input type="checkbox" value="{{ $item->id }}" name="boleto_id[]" class="check">
                                        </td>
                                        <td>{{ $item->conta->getCliente()->razao_social }}</td>
                                        <td>{{ __moeda($item->conta->valor_integral) }}</td>
                                        <td>{{ $item->banco->banco }}</td>
                                        <td>{{ __data_pt($item->data_vencimento) }}</td>
                                        <td>{{ $item->numero }}</td>
                                        <td>{{ $item->numero_documento }}</td>
                                        <td>{{ __moeda($item->juros) }}</td>
                                        <td>{{ __moeda($item->multa) }}</td>
                                        
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
                <div class="col-12">
                    <button type="button" disabled class="btn btn-success btn-gerar-remessa">Gerar Remessa</button>
                </div>
            </div>
            <form class="d-none" method="post" action="{{ route('remessa-boletos.store')}}" id="form-remessas">
                @csrf
            </form>
        </div>
    </div>
</div>
@endsection
@section('js')
<script type="text/javascript">
    $(function(){
        percorreTabela()
    })

    $(".check").click(() => {
        percorreTabela()
    })

    function percorreTabela(){
        $('.btn-gerar-remessa').attr('disabled', 1)
        $(".check").each(function () {
            if($(this).is(":checked")){
                $('.btn-gerar-remessa').removeAttr('disabled')
            }
        })
    }

    $('.btn-gerar-remessa').click(() => {

        $(".check").each(function () {
            $('#form-remessas').append($(this))
            $('#form-remessas').submit()
        })
    })
</script>
@endsection

