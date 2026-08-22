<div class="row">
    <h4 class="mt-3">Dados Iniciais</h4>
    <h6 class="mt-3">Última NFSe: </h6>
    <div class="row mt-4">
        <div class="col-md-6 row">
            <button type="button" class="btn btn-tomador btn-outline-primary link-active px-6" onclick="selectDiv('tomador')">Tomador</button>
        </div>
        <div class="col-md-6 row m-auto">
            <button type="button" class="btn btn-servico btn-outline-primary" onclick="selectDiv('servico')">Serviço</button>
        </div>
    </div>
    <div class="div-tomador row mt-4">
        <div class="row mt-4">
            <div class="col-md-8">
                {!! Form::select('cliente_id', 'Cliente')->attrs(['class' => 'select2'])->required()
                ->options(isset($item) ? [$item->cliente_id => $item->razao_social] : [])
                !!}
            </div>
            <br>
            <div class="col-md-4 mt-3">
                {!! Form::tel('documento', 'CPF/CNPJ')->attrs(['class' => 'cpf_cnpj'])->required() !!}
            </div>
            <div class="col-md-5 mt-3">
                {!! Form::text('razao_social', 'Razão Social')->attrs(['class' => ''])->required() !!}
            </div>
            <div class="col-md-3 mt-3">
                {!! Form::tel('im', 'Inscrição Municipal (I.M)')->attrs(['class' => 'ie_rg']) !!}
            </div>
            <div class="col-md-4 mt-3">
                {!! Form::text('rua', 'Rua')->attrs(['class' => ''])->required() !!}
            </div>
            <div class="col-md-2 mt-3">
                {!! Form::tel('numero', 'Número')->attrs(['class' => ''])->required() !!}
            </div>
            <div class="col-md-3 mt-3">
                {!! Form::text('bairro', 'Bairro')->attrs(['class' => ''])->required() !!}
            </div>
            <div class="col-md-3 mt-3">
                {!! Form::text('complemento', 'Complemento')->attrs(['class' => '']) !!}
            </div>
            <div class="col-md-4 mt-3">
                {!! Form::select('cidade_id', 'Cidade')->attrs(['class' => 'select2'])->required()
                ->options(isset($item) ? [$item->cidade_id => $item->cidade->info] : [])
                !!}
            </div>
            <div class="col-md-2 mt-3">
                {!! Form::tel('cep', 'CEP')->attrs(['class' => 'cep']) !!}
            </div>
            <div class="col-md-4 mt-3">
                {!! Form::text('email', 'E-mail')->attrs(['class' => '']) !!}
            </div>
            <div class="col-md-2 mt-3">
                {!! Form::tel('telefone', 'Telefone')->attrs(['class' => 'fone']) !!}
            </div>
        </div>
    </div>
    <div class="div-servico d-none row mt-4">
        <div class="row mt-4">
            <div class="col-md-6">
                {!! Form::select('servico_id', 'Serviço', ['' => 'Selecione'] + $servicos->pluck('nome', 'id')->all())->attrs(['class' => 'select2'])->required()
                ->value(isset($item) ? $item->servico->servico_id : null)
                !!}
            </div>
            <div class="col-md-6">
                {!! Form::text('natureza_operacao', 'Natureza Operação')->attrs(['class' => ''])->required() !!}
            </div>
            <div class="col-md-12 mt-3">
                {!! Form::text('discriminacao', 'Discriminação')->attrs(['class' => ''])->required()
                ->value(isset($item) ? $item->servico->discriminacao : '')
                !!}
            </div>
            <div class="col-md-2 mt-3">
                {!! Form::text('valor_servico', 'Valor Serviço')->attrs(['class' => 'moeda'])->required()
                ->value(isset($item) ? __moeda($item->valor_total) : '')
                !!}
            </div>
            <div class="col-md-2 mt-3">
                {!! Form::tel('codigo_cnae', 'Cód CNAE')->attrs(['class' => '']) !!}
            </div>
            <div class="col-md-3 mt-3">
                {!! Form::tel('codigo_servico', 'Cód Serviço')->attrs(['class' => ''])->required()
                ->value(isset($item) ? $item->servico->codigo_servico : '')
                !!}
            </div>
            <div class="col-md-3 mt-3">
                {!! Form::tel('codigo_tributacao_municipio', 'Cód tributação do município')->attrs(['class' => ''])
                ->value(isset($item) ? $item->servico->codigo_tributacao_municipio : $config->codigo_tributacao_municipio)
                !!}
            </div>
            <div class="col-md-2 mt-3">
                {!! Form::select('exigibilidade_iss', 'Exigibilidade ISS', App\Models\Nfse::exigibilidades())->attrs(['class' => 'form-select'])->required()
                ->value(isset($item) ? $item->servico->exigibilidade_iss : null)
                !!}
            </div>
            <div class="col-md-2 mt-3">
                {!! Form::select('iss_retido', 'ISS retido', [0 => 'Não', 1 => 'Sim'])->attrs(['class' => 'form-select'])->required()
                ->value(isset($item) ? $item->servico->iss_retido : null)
                !!}
            </div>
            <div class="col-md-4 mt-3">
                {!! Form::select('responsavel_retencao_iss', 'Resp pela retenção', [1 => 'Tomador', 2 => 'Intermediário'])->attrs(['class' => 'form-select'])
                ->value(isset($item) ? $item->servico->responsavel_retencao_iss : null)
                !!}
            </div>
            <div class="col-md-3 mt-3">
                {!! Form::date('data_competencia', 'Data da competência')->attrs(['class' => ''])
                ->value(isset($item) ? $item->servico->data_competencia : '')
                !!}
            </div>
            <div class="col-md-3 mt-3">
                {!! Form::select('estado_local_prestacao_servico', 'Estado do local de prestação', App\Models\Cidade::estados())->attrs(['class' => 'form-select'])->required()
                ->value(isset($item) ? $item->servico->estado_local_prestacao_servico : '')
                !!}
            </div>
            <div class="col-md-4 mt-3">
                {!! Form::text('cidade_local_prestacao_servico', 'Cidade do local de prestação')->attrs(['class' => ''])
                ->value(isset($item) ? $item->servico->cidade_local_prestacao_servico : '')
                !!}
            </div>
            <hr class="mt-4">
            <div class="col-md-3 mt-3">
                {!! Form::text('valor_deducoes', 'Valor deduções')->attrs(['class' => 'moeda'])
                ->value(isset($item) ? $item->servico->valor_deducoes : '')
                !!}
            </div>
            <div class="col-md-3 mt-3">
                {!! Form::text('desconto_incondicional', 'Desconto incondicional')->attrs(['class' => 'moeda'])
                ->value(isset($item) ? $item->servico->desconto_incondicional : '')
                !!}
            </div>
            <div class="col-md-3 mt-3">
                {!! Form::text('desconto_condicional', 'Desconto condicional')->attrs(['class' => 'moeda'])
                ->value(isset($item) ? $item->servico->desconto_condicional : '')
                !!}
            </div>
            <div class="col-md-3 mt-3">
                {!! Form::text('outras_retencoes', 'Outras retenções')->attrs(['class' => 'moeda'])
                ->value(isset($item) ? $item->servico->outras_retencoes : '')
                !!}
            </div>
            <div class="col-md-3 mt-3">
                {!! Form::text('aliquota_iss', 'Alíquota ISS')->attrs(['class' => 'moeda'])
                ->value(isset($item) ? $item->servico->aliquota_iss : '')
                !!}
            </div>
            <div class="col-md-3 mt-3">
                {!! Form::text('aliquota_pis', 'Alíquota PIS')->attrs(['class' => 'moeda'])
                ->value(isset($item) ? $item->servico->aliquota_pis : '')
                !!}
            </div>
            <div class="col-md-3 mt-3">
                {!! Form::text('aliquota_cofins', 'Alíquota COFINS')->attrs(['class' => 'moeda'])
                ->value(isset($item) ? $item->servico->aliquota_cofins : '')
                !!}
            </div>
            <div class="col-md-3 mt-3">
                {!! Form::text('aliquota_inss', 'Alíquota INSS')->attrs(['class' => 'moeda'])
                ->value(isset($item) ? $item->servico->aliquota_inss : '')
                !!}
            </div>
            <div class="col-md-3 mt-3">
                {!! Form::text('aliquota_ir', 'Alíquota IR')->attrs(['class' => 'moeda'])
                ->value(isset($item) ? $item->servico->aliquota_ir : '')
                !!}
            </div>
            <div class="col-md-3 mt-3">
                {!! Form::text('aliquota_csll', 'Alíquota CSLL')->attrs(['class' => 'moeda'])
                ->value(isset($item) ? $item->servico->aliquota_csll : '')
                !!}
            </div>
        </div>
    </div>
    <hr class="mt-5">
    <div class="mt-3">
        <button type="submit" class="btn btn-info px-5">Salvar</button>
    </div>
</div>

@section('js')
<script>
    function selectDiv(ref) {
        $('button').removeClass('link-active')
        if (ref == 'servico') {
            $('.div-servico').removeClass('d-none')
            $('.div-tomador').addClass('d-none')
            $('.btn-servico').addClass('link-active')

        } else {
            $('.div-servico').addClass('d-none')
            $('.div-tomador').removeClass('d-none')
            $('.btn-tomador').addClass('link-active')
        }
    }

    $('#inp-cliente_id').change(() => {
        let cliente = $('#inp-cliente_id').val()
        if (cliente) {
            buscaCliente(cliente)
        }
    })

    function buscaCliente(id) {
        $.get(path_url + 'api/cliente/find/' + id)
        .done((res) => {
            console.log(res)
            $('#inp-documento').val(res.cpf_cnpj)
            $('#inp-razao_social').val(res.razao_social)
            $('#inp-cep').val(res.cep)
            $('#inp-rua').val(res.rua)
            $('#inp-numero').val(res.numero)
            $('#inp-bairro').val(res.bairro)
            $('#inp-complemento').val(res.complemento)
            $('#inp-email').val(res.email)
            $('#inp-telefone').val(res.telefone)
            var newOption = new Option(
                res.cidade.nome + " (" + res.cidade.uf + ")"
                , res.cidade.id
                , false
                , false
                );
            $("#inp-cidade_id")
            .html(newOption)
            .trigger("change");
        })
        .fail((err) => {
            console.log(err)
        })
    }

    $('#inp-servico_id').change(() => {
        let servico = $('#inp-servico_id').val()
        if (servico) {
            buscaServico(servico)
        }
    })

    function buscaServico(id) {
        $.get(path_url + 'api/servicos/find/' + id)
        .done((res) => {
            console.log(res)
            $('#inp-discriminacao').val(res.nome)
            $('#inp-valor_servico').val(res.valor.replace(".", ","))
            $('#inp-codigo_servico').val(res.codigo_servico)
            $('#inp-natureza_operacao').val(res.natureza)
            $('#inp-aliquota_iss').val(res.aliquota_iss.replace(".", ","))
            $('#inp-aliquota_pis').val(res.aliquota_pis.replace(".", ","))
            $('#inp-aliquota_cofins').val(res.aliquota_cofins.replace(".", ","))
            $('#inp-aliquota_inss').val(res.aliquota_inss.replace(".", ","))
        })
        .fail((err) => {
            console.log(err)
        })
    }

</script>
@endsection
