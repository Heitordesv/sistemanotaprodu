@extends('default.layout',['title' => 'Galeria da Loja'])
@section('content')
<div class="page-content">
    <div class="card border-top border-0 border-4 border-primary">
        <div class="card-body p-5">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('configDelivery.index')}}" type="button" class="btn btn-light btn-sm">
                        <i class="bx bx-arrow-back"></i> Voltar
                    </a>
                </div>
            </div>
            <div class="card-title d-flex align-items-center">
                <h5 class="mb-0 text-primary">Galeria da Loja: <strong>{{ $item->empresa->razao_social }}</strong>
                </h5>
            </div>
            <hr>
            {!!Form::open()
            ->post()
            ->route('configDelivery.storeImagem')
            ->multipart()!!}
            <div class="m-5">
				<input type="hidden" name="config_id" value="{{ $item->id }}">
                <h6>Imagem</h6>
                @if (!isset($not_submit))
                <div id="image-preview" class="col-md-4">
                    <label for="image-upload" id="image-label">Selecione a imagem</label>
                    <input type="file" name="image" id="image-upload" class="_image-upload" accept="image/*" />
                    @isset($item)
                    @if ($item->imagem)
                    <img src="/uploads/lojaDelivery/{{ $item->imagem }}" class="img-default">
                    @else
                    <img src="/imgs/no_image.png" class="img-default">
                    @endif
                    @else
                    <img src="/imgs/no_image.png" class="img-default">
                    @endif
                </div>
                @endif
            </div>
            <div>
                <button type="submit" class="btn btn-info px-5">Salvar</button>
            </div>
            {!!Form::close()!!}

            <hr>
            <div class="row">
                <div class="card">
                    @if(sizeof($item->galeria) > 0)
                    <div class="row">
                        @foreach($item->galeria as $v => $g)
                        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
                            <!--begin::Card-->
                            <div class="card card-custom gutter mt-2">
                                @if(public_path('/').file_exists($g->imagem))
                                <img style="width: auto; height: 200px;" class="m-2" src="/uploads/lojaDelivery/{{$g->imagem}}">
                                @else
                                <img style="width: auto; height: 200px;" class="m-2" src="/imgs/no_image.png" alt="image">
                                @endif
                                <a onclick='swal("Atenção!", "Deseja remover este registro?", "warning").then((sim) => {if(sim){ location.href="/configDelivery/deleteImagem/{{$g->id}}" }else{return false} })' href="#!" class="btn btn-danger m-2">
                                    <i class="la la-trash"></i>
                                    Remover</a>
                                <p class="text-info m-2">Imagem {{$v+1}}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <h4 class="text-danger m-3">Nenhum imagem cadastrada</h4>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script type="text/javascript" src="/assets/js/jquery.uploadPreview.min.js"></script>
    
@endsection