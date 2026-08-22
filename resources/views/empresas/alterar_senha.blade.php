@extends('default.layout',['title' => 'Nova Empresa'])
@section('content')
<div class="page-content">
    <div class="card border-top border-0 border-4 border-primary">
        <div class="ms-auto m-3">
            <a href="{{ route('empresas.index')}}" type="button" class="btn btn-light btn-sm">
                <i class="bx bx-arrow-back"></i> Voltar
            </a>
        </div>
        <div class="card-body p-5">
            <hr>
            {!!Form::open()->fill($empresa)
            ->put()
            ->route('empresas.alterarSenhaPost', [$empresa->id])
            !!}

            <input type="hidden" name="id" value="{{$empresa->id}}">

            <div class="row g-3">
                <div>
                    <h5>Alteração de Senha do Usuário: <strong>{{ $empresa->nome }}</strong></h5>
                </div>
                <div class="col-sm-4 col-lg-4 col-md-4 col-12">
                    @foreach ($empresa->usuarios as $key => $u)
                    <div class="card">
                        <h6 class="m-3">Usuários: {{$u->nome}}</h6>
                        <div class="card-body" style="height: 150px;">
                            <h4>Login: <strong class="text-info">{{$u->login}}</strong></h4>
                            <h4>ADM:
                                @if($u->adm)
                                <span class="label label-xl label-inline label-light-success">SIM</span>
                                @else
                                <span class="label label-xl label-inline label-light-danger">NÃO</span>
                                @endif
                            </h4>
                            <h4>Ativo:
                                @if($u->ativo)
                                <span class="label label-xl label-inline label-light-success">SIM</span>
                                @else
                                <span class="label label-xl label-inline label-light-danger">NÃO</span>
                                @endif
                            </h4>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="col-4">
                {!!Form::text('senha', 'Nova Senha')->attrs(['class' => '']) !!}
            </div>
            <div class="col-12 mt-3">
                <button type="submit" class="btn btn-primary px-5">Salvar</button>
            </div>
            {!!Form::close()!!}
        </div>
    </div>
</div>
@endsection
