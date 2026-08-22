@extends('default.layout', ['title' => 'Pedidos'])

@section('content')

<div class="page-content">
    <div class="card">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                 
                </div>
            </div>
            <hr>
       <div class="container">
    <h2 class="my-4">Lista de Cupons Cadastrados</h2>

    <!-- Verifica se há algum sucesso ou erro ao exibir mensagens -->
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @elseif(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <!-- Tabela de Cupons -->
    <table class="table table-striped">
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Ativação</th>
                <th scope="col">Porcentagem</th>
                <th scope="col">Total de Vezes</th>
                <th scope="col">Mostrar no Site</th>
                <th scope="col">Data de Validade</th>
                <th scope="col">VIP</th>
                <th scope="col">Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cupons as $cupom)
                <tr>
                    <td>{{ $cupom->id }}</td>
                    <td>{{ $cupom->ativacao }}</td>
                    <td>{{ $cupom->porcentagem }}%</td>
                    <td>{{ $cupom->total_vezes }}</td>
                    <td>{{ $cupom->mostrar_site ? 'Sim' : 'Não' }}</td>
                    <td>{{ \Carbon\Carbon::parse($cupom->data_validade)->format('d/m/Y') }}</td>
                    <td>{{ $cupom->vip ? 'Sim' : 'Não' }}</td>
                    <td>
                        <!-- Ações: Editar e Excluir -->
                        <a href="{{ route('cupom.edit', $cupom->id_cupom) }}" class="btn btn-warning btn-sm">Editar</a>
                        <form action="{{ route('cupom.destroy', $cupom->id_cupom) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Tem certeza que deseja excluir este cupom?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Excluir</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">Nenhum cupom encontrado</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    <!-- Link para criação de novo cupom -->
    <a href="{{ route('cupom.create') }}" class="btn btn-primary mt-3">Criar Novo Cupom</a>
</div>
</div>

@endsection
