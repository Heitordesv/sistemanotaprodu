@extends('default.layout',['title' => 'Galeria da Loja'])
@section('content')
<div class="page-content">
    <div class="card border-top border-0 border-4 border-primary">
        <div class="card-body p-5">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('nuvemshop-produtos.index')}}" type="button" class="btn btn-light btn-sm">
                        <i class="bx bx-arrow-back"></i> Voltar
                    </a>
                </div>
            </div>
            <div class="card-title d-flex align-items-center">
                <h5 class="mb-0 text-primary">Galeria do Produto: <strong>{{ $prodBd->nome }}</strong>
                </h5>
            </div>
            <hr>
            {!!Form::open()
            ->put()
            ->route('nuvemshop-produtos.storeImagem', [$prodBd->id])
            ->multipart()!!}
            <div class="m-5">

                <h6>Imagem</h6>
                @if (!isset($not_submit))
                <div id="image-preview" class="col-md-4">
                    <label for="image-upload" id="image-label">Selecione a imagem</label>
                    <input type="file" name="image" id="image-upload" class="_image-upload" accept="image/*" />

                    <img src="/imgs/no_image.png" class="img-default">

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
                    @if(sizeof($produto->images) > 0)

                    <div class="row">
                        @foreach($produto->images as $v => $g)
                        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6">
                            <!--begin::Card-->
                            <div class="card card-custom gutter mt-2">
                                <img style="width: auto; height: 200px;" src="{{$g->src}}" alt="image">
                                <form action="{{ route('nuvemshop-produtos.destroy_image', $g->id) }}" method="post" id="form-{{$g->id}}">
                                    <input type="hiddden" name="produto_id" value="{{$produto->id}}">
                                    @method('delete')
                                    @csrf
                                    <button type="button" class="btn btn-danger mt-2 w-100 btn-delete">
                                        <i class="bx bx-trash"></i>
                                        Remover
                                    </button>
                                    <p class="text-info m-2">Imagem {{$v+1}}</p>
                                </form>
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