@extends('default.layout', ['title' => 'Contas a Receber'])

@section('content')
    @include('conta_receber.components.css')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

<div class="page-content p-4">

    @include('conta_receber.components.header')
    @include('conta_receber.components.filtros')

    @php
        $isSuper = isSuper(session('user_logged')['super']);
    @endphp

    @include('conta_receber.components.tabela', ['isSuper' => $isSuper])

</div>

@include('conta_receber.components.modais')



@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    @include('conta_receber.components.scripts')
@endsection