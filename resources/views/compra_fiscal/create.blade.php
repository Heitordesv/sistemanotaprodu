@extends('default.layout',['title' => 'Compra Fiscal'])
@section('content')
<div class="page-content">
    <div class="card border-top border-0 border-4 border-primary">
        <div class="card-body p-5">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                </div>
            </div>
            <div class="card-title d-flex align-items-center">
                <h5 class="mb-0 text-primary">Compra Fiscal</h5>
            </div>
            <hr>
            <div class="pl-lg-4">
                @include('compra_fiscal.import')
            </div>
        </div>
    </div>
</div>


@endsection

