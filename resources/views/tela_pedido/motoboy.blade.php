
@section('content')
<div class="page-content">
    <div class="card border-top border-0 border-4 border-primary">
        <div class="card-body p-5">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('telasPedido.index')}}" type="button" class="btn btn-light btn-sm">
                        <i class="bx bx-arrow-back"></i> Voltar
                    </a>
                </div>
            </div>
            <div class="card-title d-flex align-items-center">
                <h5 class="mb-0 text-primary">Cadastrar Motoboy
</h5>
            </div>
            <hr>
<div class="container">
<form action="{{ route('tela_pedido.motoboy.store') }}" method="POST">
    @csrf
    <label for="empresa_id">Empresa ID:</label>
    <input type="text" name="empresa_id" required>

    <label for="deliveryman_name">Nome do Motoboy:</label>
    <input type="text" name="deliveryman_name" required>

    <label for="deliveryman_phone_number">Telefone:</label>
    <input type="text" name="deliveryman_phone_number" required>

    <button type="submit">Cadastrar Motoboy</button>
</form>
       


        <button type="submit" class="btn btn-primary">Cadastrar Motoboy</button>
    </form>
</div>
 </div>
    </div>
</div>
@endsection

