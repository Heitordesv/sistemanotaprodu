@section('content')
<div class="page-content">
    <div class="card border-top border-0 border-4 border-primary">
        <div class="card-body p-5">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('cupom.index') }}" type="button" class="btn btn-light btn-sm">
                        <i class="bx bx-arrow-back"></i> Voltar
                    </a>
                </div>
            </div>
            <div class="card-title d-flex align-items-center">
                <h5 class="mb-0 text-primary">
                    @isset($cupom) Editar Cupom de Desconto @else Cadastrar Cupom de Desconto @endisset
                </h5>
            </div>
            <hr>
            <div class="container">
                <form 
                    @isset($cupom)
                        action="{{ route('cupom.update', $cupom->id_cupom) }}" 
                        method="POST" 
                    @else 
                        action="{{ route('cupom.store') }}" 
                        method="POST" 
                    @endisset
                    >
                    @csrf
                    @isset($cupom)
                        @method('PUT')
                    @endisset

                    <div class="mb-3">
                        <label for="ativacao" class="form-label">Código de Ativação</label>
                        <input type="text" class="form-control" id="ativacao" name="ativacao" 
                               value="{{ old('ativacao', $cupom->ativacao ?? '') }}" required maxlength="20" placeholder="Ex: CUPOM10">
                    </div>

                    <div class="mb-3">
                        <label for="porcentagem" class="form-label">Desconto (%)</label>
                        <input type="number" class="form-control" id="porcentagem" name="porcentagem" 
                               value="{{ old('porcentagem', $cupom->porcentagem ?? '') }}" required min="1" max="100" placeholder="Ex: 10">
                    </div>

                    <div class="mb-3">
                        <label for="data_validade" class="form-label">Data de Validade</label>
                        <input type="date" class="form-control" id="data_validade" name="data_validade" 
                               value="{{ old('data_validade', $cupom->data_validade ?? '') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="total_vezes" class="form-label">Quantidade de Usos</label>
                        <input type="number" class="form-control" id="total_vezes" name="total_vezes" 
                               value="{{ old('total_vezes', $cupom->total_vezes ?? '') }}" required min="1" max="100000">
                    </div>

                    <div class="mb-3">
                        <label for="mostrar_site" class="form-label">Mostrar no Site?</label>
                        <select class="form-control" id="mostrar_site" name="mostrar_site">
                            <option value="1" @if((old('mostrar_site', $cupom->mostrar_site ?? 1)) == 1) selected @endif>Sim</option>
                            <option value="0" @if((old('mostrar_site', $cupom->mostrar_site ?? 0)) == 0) selected @endif>Não</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="vip" class="form-label">Cliente Vip</label>
                        <select class="form-control" id="vip" name="vip">
                            <option value="1" @if((old('vip', $cupom->vip ?? 1)) == 1) selected @endif>Sim</option>
                            <option value="0" @if((old('vip', $cupom->vip ?? 0)) == 0) selected @endif>Não</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        @isset($cupom) Atualizar Cupom @else Cadastrar Cupom @endisset
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
