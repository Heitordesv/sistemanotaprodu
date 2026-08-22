@extends('default.layout', ['title' => 'Planos pendentes'])
@section('content')
    <div class="page-content">
        <div class="card border-top border-0 border-4 border-primary">
            <div class="card-body p-5">
                <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                    <div class="ms-auto">
                        <a href="{{ route('planosPendentes.index') }}" type="button" class="btn btn-light btn-sm">
                            <i class="bx bx-arrow-back"></i> Voltar
                        </a>
                    </div>
                </div>
                <div class="card-title d-flex align-items-center">
                    <h5 class="mb-0 text-primary">Planos pendentes</h5>
                </div>
                <hr>
                <div class="table-responsive">
                    <table class="table mb-0 table-striped">
                        <thead>
                            <tr>
                                <th>Empresa</th>
                                <th>Plano</th>
                                <th>Expiração</th>
                                <th>Representante</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data as $item)
                                <tr>
                                    <td>{{ $item->empresa->nome }}</td>
                                    <td>{{ $item->plano->nome }}</td>
                                    <td>{{ $item->expiracao }}</td>
                                    <td>
                                        @if ($item->representante)
                                            {{ $item->representante->nome }}
                                        @else
                                            --
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">Nada encontrado</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
