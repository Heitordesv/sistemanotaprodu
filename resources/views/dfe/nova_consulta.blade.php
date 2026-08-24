@extends('default.layout', ['title' => 'Consulta de Documentos'])
@section('content')
<div class="page-content">
    <div class="card border-top border-0 border-4 border-primary">
        <div class="card-body p-5">
            <div class="col-sm-12 col-lg-12 col-md-12 col-xl-12">
                <h4>Nova Consulta</h4>

                
                <p id="aguarde" class="text-info d-none">
                    <a id="btn-enviar" class="btn btn-success spinner-white spinner spinner-right">
                        Consultando novos documentos, aguarde...
                    </a>
                </p>
                <p id="sem-resultado" style="display: none" class="center-align text-danger">Nenhum novo resultado...</p>
                <div class="col-xl-12" id="table" style="display: none">
                    <a href="{{ route('dfe.index') }}" class="btn btn-info">
                        <i class="la la-undo"></i>
                        Voltar para os documentos
                    </a>
                    <div class="table-responsive tbl-400">
                        <table class="table mb-0 table-striped">
                            <thead>
                                <tr class="datatable-row" style="left: 0px;">
                                    <th>NOME</th>
                                    <th>CNPJ</th>
                                    <th>VALOR</th>
                                    <th>CHAVE</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('js')
<script>
window.dfeEndpoints = {
    novosDocumentos: @json(route('dfe.getDocumentosNovos'))
};
</script>
<script type="text/javascript" src="{{ asset('js/dfe.js') }}?v={{ filemtime(public_path('js/dfe.js')) }}"></script>

@endsection
@endsection
