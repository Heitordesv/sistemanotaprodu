@extends('default.layout', ['title' => 'CATEGORIAS DE COMPLEMENTOS'])

@section('content')



<div class="row justify-content-center">
   
    <div class="col-md-11">
        <br>
        <br>
        <br>
        <br>
        <div class="container">
            <h2>Mensagens Personalizadas</h2>
        
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
        
            <a href="{{ route('mensagem_personalizada.create') }}" class="btn btn-primary mb-3">Nova Mensagem</a>
        
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Mensagem</th>
                        <th>Status</th>
                        <th>Tipo</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <tbody>
                        @forelse($mensagens as $mensagem)
                            <tr>
                                <td>{{ $mensagem->id }}</td>
                                <td>{{ $mensagem->mensagem }}</td>
                                <td>{{ $mensagem->status }}</td>
                                <td>{{ $mensagem->tipo }}</td>
                                <td>
                                    <a href="{{ route('mensagem_personalizada.edit', $mensagem->id) }}" class="btn btn-sm btn-warning">Editar</a>
                                    <form action="{{ route('mensagem_personalizada.destroy', $mensagem->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button onclick="return confirm('Deseja realmente excluir?')" class="btn btn-sm btn-danger">Excluir</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5">Nenhuma mensagem cadastrada ainda.</td></tr>
                        @endforelse
                    </tbody>
                                </table>
        </div>
@endsection
