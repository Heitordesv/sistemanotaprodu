@php
    $isEmpresa = isset($item) && $item->cpf_cnpj && strlen(preg_replace('/\D/', '', $item->cpf_cnpj)) > 11;
    $labelRazao = $isEmpresa ? 'Razão Social' : 'Nome Completo';
    $labelFantasia = $isEmpresa ? 'Nome Fantasia / Como gostaria de ser chamado' : 'Apelido / Como gostaria de ser chamado';
@endphp
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<script src="https://cdn.tailwindcss.com"></script>
<div class="p-6 bg-white rounded-lg shadow-md space-y-6">

    {{-- CPF / CNPJ --}}
    <div class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
        <div class="md:col-span-4">
             <label for="inp-cpf_cnpj" class="mb-1 block text-sm font-medium text-gray-700">
              CPF/CNPJ <span class="font-normal text-gray-400">(opcional)</span>
          </label>
          {!! Form::text('cpf_cnpj', '')->attrs([
    'class' => 'cpf_cnpj pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg w-full focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition',
    'placeholder' => 'Informe CPF ou CNPJ (Opcional)'
]) !!}
        </div>
        <div class="md:col-span-1 flex justify-end">
            <button type="button" id="btn-consulta-cnpj" class="bg-gray-800 text-white px-4 py-2 rounded flex items-center justify-center hover:bg-gray-900 transition w-full">
                <span class="spinner-border spinner-border-sm hidden mr-2" role="status" aria-hidden="true"></span>
                <i class="bx bx-search text-lg"></i>
            </button>
        </div>
    </div>

    {{-- Nome / Razão Social e Nome Fantasia --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ $labelRazao }}</label>
            {!! Form::text('razao_social', $labelRazao)->required()->attrs([
                'class' => 'px-3 py-2 border rounded w-full focus:ring focus:ring-blue-300',
                'placeholder' => $labelRazao
            ]) !!}
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ $labelFantasia }}</label>
            {!! Form::text('nome_fantasia', $labelFantasia)->attrs([
                'class' => 'ignore px-3 py-2 border rounded w-full focus:ring focus:ring-blue-300',
                'placeholder' => $labelFantasia
            ]) !!}
        </div>
    </div>

    {{-- Documentos e Consumidor/Contribuinte --}}
    <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
        <div>
            {!! Form::text('ie_rg', 'IE/RG')->attrs(['class' => 'ignore px-3 py-2 border rounded w-full focus:ring focus:ring-blue-300']) !!}
        </div>
        <div>
            {!! Form::select('consumidor_final', 'Consumidor final', [0 => 'Não', 1 => 'Sim'])->attrs(['class' => 'form-select px-3 py-2 border rounded w-full focus:ring focus:ring-blue-300 ignore']) !!}
        </div>
        <div>
            {!! Form::select('contribuinte', 'Contribuinte', [0 => 'Não', 1 => 'Sim'])->attrs(['class' => 'form-select px-3 py-2 border rounded w-full focus:ring focus:ring-blue-300 ignore']) !!}
        </div>
        <div>
            {!! Form::tel('limite_venda', 'Limite venda')->attrs(['class' => 'moeda ignore px-3 py-2 border rounded w-full focus:ring focus:ring-blue-300']) !!}
        </div>
        <div>
            {!! Form::date('data_aniversario', 'Data de aniversário')->attrs(['class' => 'ignore px-3 py-2 border rounded w-full focus:ring focus:ring-blue-300']) !!}
        </div>
        <div>
            {!! Form::text('email', 'Email')->attrs(['class' => 'email ignore px-3 py-2 border rounded w-full focus:ring focus:ring-blue-300']) !!}
        </div>
    </div>

    {{-- Telefone e Celular --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <div>
            {!! Form::tel('celular', 'Celular')->attrs(['class' => 'fone ignore px-3 py-2 border rounded w-full focus:ring focus:ring-blue-300']) !!}
        </div>
        <div>
            {!! Form::tel('telefone', 'Telefone')->attrs(['class' => 'fone ignore px-3 py-2 border rounded w-full focus:ring focus:ring-blue-300']) !!}
        </div>
    </div>

    <hr class="my-4">

    {{-- Endereço de Faturamento --}}
    <h5 class="text-lg font-semibold text-gray-700">Endereço de Faturamento</h5>
    <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
        <div>
            {!! Form::tel('cep', 'CEP')->attrs(['class' => 'cep px-3 py-2 border rounded w-full focus:ring focus:ring-blue-300'])->required() !!}
        </div>
        <div class="md:col-span-3">
            {!! Form::text('rua', 'Rua')->attrs(['class' => 'px-3 py-2 border rounded w-full focus:ring focus:ring-blue-300'])->required() !!}
        </div>
        <div>
            {!! Form::tel('numero', 'Número')->attrs(['class' => 'px-3 py-2 border rounded w-full focus:ring focus:ring-blue-300'])->required() !!}
        </div>
        <div>
            {!! Form::text('bairro', 'Bairro')->attrs(['class' => 'px-3 py-2 border rounded w-full focus:ring focus:ring-blue-300'])->required() !!}
        </div>
        <div>
            {!! Form::text('complemento', 'Complemento')->attrs(['class' => 'ignore px-3 py-2 border rounded w-full focus:ring focus:ring-blue-300']) !!}
        </div>
        <div>
            {!! Form::select('cidade_id', 'Cidade')->required()->options(isset($item) ? [$item->cidade_id => $item->cidade->info] : [])->attrs(['class' => 'select2 px-3 py-2 border rounded w-full focus:ring focus:ring-blue-300']) !!}
        </div>
        <div>
            {!! Form::select('cod_pais', 'Pais', $paises->pluck('nome', 'codigo')->all())->attrs(['class' => 'select2 px-3 py-2 border rounded w-full focus:ring focus:ring-blue-300'])->value(isset($item) ? $item->cod_pais : '1058') !!}
        </div>
        <div>
            {!! Form::text('id_estrangeiro', 'ID estrangeiro (opcional)')->attrs(['class' => 'ignore px-3 py-2 border rounded w-full focus:ring focus:ring-blue-300']) !!}
        </div>
        <div>
            {!! Form::select('grupo_id', 'Grupo (opcional)', [null => 'Selecione'] + $grupos->pluck('nome', 'id')->all())->attrs(['class' => 'form-select ignore px-3 py-2 border rounded w-full focus:ring focus:ring-blue-300']) !!}
        </div>
        <div>
            {!! Form::select('acessor_id', 'Assessor (opcional)', [null => 'Selecione'] + $acessores->pluck('nome', 'id')->all())->attrs(['class' => 'form-select ignore px-3 py-2 border rounded w-full focus:ring focus:ring-blue-300']) !!}
        </div>
    </div>

    {{-- Upload de Imagem --}}
    <div class="col-12 mt-4">
        <div id="image-preview" class="relative w-48 h-48 border-dashed border-2 border-gray-300 rounded-lg overflow-hidden">
            <label for="image-upload" class="absolute inset-0 flex flex-col items-center justify-center cursor-pointer bg-gray-50 hover:bg-gray-100 transition">
                Selecione a imagem
            </label>
            <input type="file" name="image" id="image-upload" class="absolute w-full h-full opacity-0 cursor-pointer" accept="image/*"/>
            <img src="{{ isset($item) && $item->imagem ? '/uploads/clients/'.$item->imagem : '/imgs/no_client.png' }}" class="w-full h-full object-cover absolute top-0 left-0"/>
        </div>
    </div>

    {{-- Dados do Contador --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mt-4">
        <div>
            {!! Form::select('info_contador', 'Deseja informar dados do contador?', ['0'=>'Não','1'=>'Sim'])->attrs(['class'=>'form-select px-3 py-2 border rounded w-full focus:ring focus:ring-blue-300']) !!}
        </div>
        <div class="div-contador hidden">
            {!! Form::text('contador_nome', 'Nome')->attrs(['class' => 'ignore px-3 py-2 border rounded w-full focus:ring focus:ring-blue-300']) !!}
        </div>
        <div class="div-contador hidden">
            {!! Form::tel('contador_telefone', 'Telefone')->attrs(['class' => 'fone ignore px-3 py-2 border rounded w-full focus:ring focus:ring-blue-300']) !!}
        </div>
        <div class="div-contador hidden">
            {!! Form::text('contador_email', 'Email')->type('email')->attrs(['class' => 'ignore px-3 py-2 border rounded w-full focus:ring focus:ring-blue-300']) !!}
        </div>
    </div>

    {{-- Observações e vendedor --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mt-4">
        <div class="md:col-span-2">
            {!! Form::tel('observacao', 'Observação')->attrs(['class' => 'ignore px-3 py-2 border rounded w-full focus:ring focus:ring-blue-300']) !!}
        </div>
        <div class="md:col-span-2">
            {!! Form::select('acessor_id', 'Vendedor/Funcionário (opcional)', [null=>'Selecione'] + $funcionarios->pluck('nome','id')->all())->attrs(['class'=>'select2 ignore px-3 py-2 border rounded w-full focus:ring focus:ring-blue-300']) !!}
        </div>
    </div>

    <hr class="my-4">

    {{-- Endereço de Cobrança --}}
    <h5 class="text-lg font-semibold text-gray-700">Endereço de Cobrança (opcional)</h5>
    <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
        <div>
            {!! Form::text('rua_cobranca', 'Rua')->attrs(['class'=>'ignore px-3 py-2 border rounded w-full focus:ring focus:ring-blue-300']) !!}
        </div>
        <div>
            {!! Form::text('numero_cobranca', 'Número')->attrs(['class'=>'ignore px-3 py-2 border rounded w-full focus:ring focus:ring-blue-300']) !!}
        </div>
        <div>
            {!! Form::text('bairro_cobranca', 'Bairro')->attrs(['class'=>'ignore px-3 py-2 border rounded w-full focus:ring focus:ring-blue-300']) !!}
        </div>
        <div>
            {!! Form::text('cep_cobranca', 'CEP')->attrs(['class'=>'ignore px-3 py-2 border rounded w-full focus:ring focus:ring-blue-300']) !!}
        </div>
        <div class="md:col-span-2">
            {!! Form::select('cidade_cobranca_id', 'Cidade', [])->attrs(['class'=>'select2 ignore px-3 py-2 border rounded w-full focus:ring focus:ring-blue-300']) !!}
        </div>
    </div>

    {{-- Botão Salvar --}}
    <div class="mt-6">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded shadow">
            Salvar
        </button>
    </div>

</div>

@section('js')
<script>
    // Mostrar/ocultar dados do contador
    const selectContador = document.querySelector('select[name="info_contador"]');
    const divContador = document.querySelectorAll('.div-contador');

    selectContador.addEventListener('change', function() {
        if(this.value == '1') {
            divContador.forEach(el => el.classList.remove('hidden'));
        } else {
            divContador.forEach(el => el.classList.add('hidden'));
        }
    });
</script>

<script type="text/javascript" src="/js/client.js"></script>
<script type="text/javascript" src="/assets/js/jquery.uploadPreview.min.js"></script>
<script type="text/javascript">

    $(document).on("blur", "#inp-cep", function () {

        let cep = $(this).val().replace(/[^0-9]/g,'')

        $url = "https://viacep.com.br/ws/"+cep+"/json";
        $.get($url)
        .done((success) => {
            console.log(success)
            $('#inp-rua').val(success.logradouro)
            $('#inp-numero').val(success.numero)
            $('#inp-bairro').val(success.bairro)

            findCidade(success.ibge)
        })
        .fail((err) => {
            console.log(err)
        })

    });

    function findCidade(codigo_ibge) {

        $.get(path_url + "api/cidadePorCodigoIbge/" + codigo_ibge)
        .done((res) => {

            var newOption = new Option(
                res.nome + " (" + res.uf + ")",
                res.id,
                false,
                false
                );
            $("#inp-cidade_id")
            .html(newOption)
            .trigger("change");
        })
        .fail((err) => {
            console.log(err)
        })
    }

</script>
@endsection
