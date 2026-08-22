@extends('default.layout',['title' => 'Usuários'])
@section('content')

<div class="page-content">
    <div class="card">
        <div class="row m-3">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('usuarios.create')}}" type="button" class="btn btn-success">
                        <i class="bx bx-plus"></i> Novo usuário
                    </a>
                </div>
            </div>
            @foreach($data as $item)
            <div class="col-md-6 col-xl-4 col-12">
                <div class="card radius-15">
                    <div class="card-body text-center">
                        <div class="p-4 border radius-15">
                            @if($item->img != "")
                            <img src="/uploads/usuarios/{{$item->img}}" width="110" height="110" class="rounded-circle shadow" alt="">
                            @else
                            <img src="/logos/user.png" width="110" height="110" class="rounded-circle shadow" alt="">
                            @endif
                            <h5 class="mb-0 mt-5">{{$item->nome}}</h5>
                            <p class="mb-3">
                                {{$item->login}}
                            </p>
                            @if(session('user_logged')['adm'])
                            <div class="list-inline contacts-social mt-3 mb-3">
                                <form action="{{ route('usuarios.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
                                    @method('delete')
                                    @csrf
                                    <a href="{{ route('usuarios.edit', $item) }}" class="list-inline-item bg-warning text-white border-0"><i class="bx bx-edit"></i></a>
                                    <a href="javascript:;" class="list-inline-item btn-delete bg-google text-white border-0"><i class="bx bx-trash"></i></a>
                                    <a href="{{ route('usuarios.historico', $item) }}" class="list-inline-item bg-primary text-white border-0"><i class="bx bx-list-check"></i></a>
                                </form>
                            </div>
                            @endif
                            <div class="d-grid">
                                @if(session('user_logged')['adm'])
                                <span>Ultimo acesso: @if($item->ultimoAcesso())
                                    {{ \Carbon\Carbon::parse($item->ultimoAcesso()->updated_at)->format('d/m/Y H:i:s') }}
                                    @else
                                    --
                                    @endif</span>
                                @endif
                                <span>Ativo: {{$item->ativo ? "Sim" : "Não"}}</span>
                                <span>ADM: {{$item->adm ? "Sim" : "Não"}}</span>
                                @if(empresaComFilial())
                                <span>Locais: {{ __get_locais($item->locais) }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        {!! $data->appends(request()->all())->links() !!}
    </div>
</div>

@endsection
