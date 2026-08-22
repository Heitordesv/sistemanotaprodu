@extends('default.layout',['title' => 'Nova Empresa'])
@section('content')
<div class="page-content">
    <div class="card border-top border-0 border-4 border-primary">
        <div class="card-body p-5">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('empresas.index')}}" type="button" class="btn btn-light btn-sm">
                        <i class="bx bx-arrow-back"></i> Voltar
                    </a>
                </div>
            </div>
            <div class="card-title d-flex align-items-center">
                <h5 class="mb-0 text-primary">Setar Plano</h5>
            </div>
            <hr>
            {!!Form::open()
            ->post()
            ->route('empresas.setarPlanoPost')
            ->multipart()!!}
            <div class="pl-lg-4">
                <input type="hidden" name="empresa" value="{{$empresa->id}}">
                <h5>Empresa: {{$empresa->nome}}</h5>
                <input type="hidden" value="{{json_encode($planos)}}" id="planos">
                <div class="row m-5">
                    <div class="col-md-4">
                        {!!Form::select('plano', 'Plano', ['' => 'Selecione'] + $planos->pluck('nome', 'id')->all())->attrs(['class' => 'form-select'])->required()
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!!Form::tel('valor', 'Valor')->attrs(['class' => 'moeda'])->required()
                        !!}
                    </div>
                    <div class="col-md-3">
                        {!!Form::date('date_expiracao', 'Data de Expiração')->required()
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!!Form::select('indeterminado', 'Indeterminado', [0 => 'Não', 1 => 'Sim'])
                        ->attrs(['class' => 'form-select'])->required()
                        !!}
                    </div>
                    <div class="col-md-11 mt-5">
                        {!!Form::text('mensagem', 'Mensagem de Alerta (Opcional)')
                        !!}
                    </div>
                    <div class="col-12 mt-5">
                        <button type="submit" class="btn btn-primary px-5">Salvar</button>
                    </div>
                </div>
            </div>
            {!!Form::close()!!}
        </div>
    </div>
</div>


@section('js')
<script type="text/javascript">
    var planos = [];
    $(function() {
        planos = JSON.parse($('#planos').val());
    });

    $('#inp-indeterminado').change(() => {
        let i = $('#inp-indeterminado').val()
        
        if (i == 1) {
            $('#inp-date_expiracao').val('')
        } else {
            setPlano()
        }
    })

    $('#inp-plano').change(() => {
        setPlano()
    })

    function setPlano(){
        let plano = $('#inp-plano').val();
        planos.map((p) => {
            if (p.id == plano) {
                let intervalo = p.intervalo_dias;
                var outraData = new Date();
                outraData.setDate(outraData.getDate() + intervalo);

                let mes = outraData.getMonth() + 1;
             
                let d = outraData.getFullYear() + '-' + (mes < 10 ? "0" + mes : mes) + '-' +(outraData.getDate() < 10 ? "0" + outraData.getDate() : outraData.getDate())
               
                $('#inp-date_expiracao').val(d)
                $('#inp-valor').val(p.valor.replace(".", ","))
            }
        })
    }

</script>
@endsection

@endsection
