@extends('default.layout', ['title' => 'Configuração de Caixa'])

@section('content')

{!! Form::open()->fill($item)
->post()
->route('frenteCaixa.storeConfig') !!}
@include('frontBox._configuracao')
{!! Form::close() !!}

@endsection