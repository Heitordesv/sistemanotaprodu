@extends('default.layout', ['title' => 'Atualização IBPT'])
@section('content')
<div class="page-content"><div class="card border-top border-0 border-4 border-primary"><div class="card-body p-4">
    <div class="d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-primary">IBPT da empresa ativa</h5>
        <a href="{{ route('configNF.index') }}" class="btn btn-light btn-sm">Voltar ao emitente</a>
    </div><hr>
    <div class="alert {{ $tokenCadastrado ? 'alert-success' : 'alert-warning' }}">
        Token IBPT: <strong>{{ $tokenCadastrado ? 'configurado' : 'não configurado' }}</strong>. Esta tela consulta somente os produtos da empresa ativa.
    </div>
    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="border rounded p-3">Produtos: <strong>{{ $total }}</strong></div></div>
        <div class="col-md-4"><div class="border rounded p-3">Com NCM válido: <strong>{{ $elegiveis }}</strong></div></div>
        <div class="col-md-4"><div class="border rounded p-3">Com cache IBPT: <strong>{{ $atualizados }}</strong></div></div>
    </div>
    <button id="btn-sincronizar" class="btn btn-primary" {{ !$tokenCadastrado || !$elegiveis ? 'disabled' : '' }}><i class="bx bx-refresh"></i> Atualizar produtos desta empresa</button>
    <div class="progress mt-3 d-none" id="ibpt-progress-wrap" style="height:24px"><div id="ibpt-progress" class="progress-bar progress-bar-striped progress-bar-animated" style="width:0%">0</div></div>
    <div id="ibpt-mensagem" class="alert mt-3 d-none"></div>
</div></div></div>
@endsection
@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const button = document.getElementById('btn-sincronizar'); if (!button) return;
    const total = {{ (int) $elegiveis }}, message = document.getElementById('ibpt-mensagem'), progress = document.getElementById('ibpt-progress');
    button.addEventListener('click', async function () {
        button.disabled = true; document.getElementById('ibpt-progress-wrap').classList.remove('d-none'); message.classList.add('d-none');
        let cursor = 0, done = 0;
        try {
            while (true) {
                const response = await fetch('{{ route('configNF.ibpt.sync') }}', {method:'POST', headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'}, body:JSON.stringify({cursor})});
                const data = await response.json(); if (!response.ok) throw new Error(data.message || 'Não foi possível consultar o IBPT.');
                cursor = data.cursor; done += data.atualizados;
                const percent = total ? Math.min(100, Math.round(done * 100 / total)) : 100; progress.style.width = percent + '%'; progress.textContent = done + ' de ' + total;
                if (!data.has_more) break;
            }
            progress.classList.remove('progress-bar-animated'); message.className = 'alert alert-success mt-3'; message.textContent = done + ' produto(s) atualizado(s) para a empresa ativa.'; message.classList.remove('d-none');
        } catch (error) {
            message.className = 'alert alert-danger mt-3'; message.textContent = error.message + ' Nenhum dado de outra empresa foi alterado.'; message.classList.remove('d-none'); button.disabled = false;
        }
    });
});
</script>
@endsection
