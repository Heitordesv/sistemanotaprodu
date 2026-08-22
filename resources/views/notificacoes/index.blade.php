{{-- 0. PEDIDOS DO SITE --}}
@foreach($dadosPedidos as $pedido)
    <a href="{{ route('pedidosEcommerce.show', $pedido['id']) }}" 
       class="dropdown-item alert-item border-bottom bg-light-success">
        <div class="d-flex align-items-center">
            <div class="notify bg-success text-white">
                <i class="bx bx-cart"></i>
            </div>
            <div class="flex-grow-1 ms-2">
                <h6 class="msg-name text-success fw-bold">
                    Novo Pedido #{{ $pedido['id'] }}
                </h6>
                <p class="msg-info mb-0">
                    {{ Str::limit($pedido['cliente'], 25) }}
                </p>
                <small class="text-dark">
                    R$ {{ number_format($pedido['valor'], 2, ',', '.') }} | {{ $pedido['data'] }}
                </small>
            </div>
        </div>
    </a>
@endforeach

{{-- 1. ALERTA CRÍTICO: PLANO DA EMPRESA --}}
@if($alertaPlano)
    @php $isVencido = $alertaPlano['status'] == 'vencido'; @endphp
    <a class="dropdown-item alert-item border-bottom {{ $isVencido ? 'bg-light-danger' : 'bg-light-warning' }}" 
       href="{{ $alertaPlano['link'] }}" target="_blank">
        <div class="d-flex align-items-center">
            <div class="notify {{ $isVencido ? 'bg-danger' : 'bg-warning' }} text-white">
                <i class="bx bx-credit-card-front"></i>
            </div>
            <div class="flex-grow-1 ms-2">
                <h6 class="msg-name text-dark">PLANO DE ASSINATURA</h6>
                <p class="msg-info mb-0">
                    @if($isVencido)
                        <span class="text-danger fw-bold pulse-animation">SISTEMA EXPIRADO!</span>
                    @else
                        <span class="text-dark">Vence em {{ $alertaPlano['dias'] }} dias ({{ $alertaPlano['data'] }})</span>
                    @endif
                </p>
                <div class="d-flex justify-content-between align-items-center mt-1">
                    <small class="text-dark">Valor: <strong>R$ {{ __moeda($alertaPlano['valor']) }}</strong></small>
                    <span class="badge {{ $isVencido ? 'bg-danger' : 'bg-dark' }} text-white">PAGAR AGORA</span>
                </div>
            </div>
        </div>
    </a>
@endif


{{-- 2. ANIVERSARIANTES --}}
@foreach($aniversariantes as $ani)
    <div class="dropdown-item alert-item border-bottom p-2 bg-light-info">
        <div class="d-flex align-items-center mb-2">
            <div class="notify bg-info text-white"><i class="bx bx-cake"></i></div>
            <div class="flex-grow-1 ms-2">
                <h6 class="msg-name">Aniversariante do Dia!</h6>
                <p class="msg-info mb-0">🎁 {{ Str::limit($ani->razao_social ?? $ani->nome_fantasia, 25) }}</p>
                <small class="text-muted">{{ $ani->celular ?? $ani->telefone ?? 'Sem contato' }}</small>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('clientes.edit', $ani->id) }}" class="btn btn-sm btn-light border w-50">Ver</a>
            <a href="https://api.whatsapp.com/send?phone=55{{ preg_replace('/[^0-9]/', '', $ani->celular ?? $ani->telefone) }}&text=Parabéns! 🎂" 
               target="_blank" class="btn btn-sm btn-success w-50">
               <i class='bx bxl-whatsapp'></i> Zap
            </a>
        </div>
    </div>
@endforeach


{{-- 3. CONTAS A RECEBER --}}
@foreach($dadosReceber as $cr)
    @php $atrasado = !empty($cr['atrasado']); @endphp
    <div class="dropdown-item alert-item border-bottom p-2 {{ $atrasado ? 'bg-light-danger' : '' }}">
        <div class="d-flex align-items-center mb-2">
            <div class="notify {{ $atrasado ? 'bg-danger' : 'bg-light-success' }} {{ $atrasado ? 'text-white' : 'text-success' }}">
                <i class="bx bx-user-check"></i>
            </div>
            <div class="flex-grow-1 ms-2">
                <h6 class="msg-name {{ $atrasado ? 'text-danger fw-bold' : '' }}">
                    Receber de: <span>{{ Str::limit($cr['nome'], 20) }}</span>
                </h6>
                <p class="msg-info mb-0">
                    R$ {{ __moeda($cr['valor']) }} | <small class="fw-bold">Venc: {{ $cr['vencimento'] }}</small>
                </p>
                @if($cr['celular'])
                    <small class="text-muted"><i class='bx bx-phone'></i> {{ $cr['celular'] }}</small>
                @endif
            </div>
        </div>
        <form action="{{ route('conta-receber.enviarWhatsApp') }}" method="POST" class="form-whatsapp">
            @csrf
            <input type="hidden" name="id" value="{{ $cr['id'] }}">
            <button type="submit" class="btn btn-sm {{ $atrasado ? 'btn-danger' : 'btn-outline-success' }} w-100 py-1">
                <i class='bx bxl-whatsapp'></i> Enviar Cobrança {{ $atrasado ? 'Urgente' : '' }}
            </button>
        </form>
    </div>
@endforeach


{{-- 4. CONTAS A PAGAR --}}
@foreach($dadosPagar as $cp)
    @php $atrasadoP = !empty($cp['atrasado']); @endphp
    <a class="dropdown-item alert-item border-bottom {{ $atrasadoP ? 'bg-light-danger border-start border-danger border-4' : '' }}" 
       href="{{ route('conta-pagar.pay', $cp['id']) }}">
        <div class="d-flex align-items-center">
            <div class="notify {{ $atrasadoP ? 'bg-danger text-white' : 'bg-light-danger text-danger' }}">
                <i class="bx bx-wallet"></i>
            </div>
            <div class="flex-grow-1 ms-2">
                <h6 class="msg-name {{ $atrasadoP ? 'text-danger fw-bold' : '' }}">
                    Pagar para: {{ Str::limit($cp['nome'], 20) }}
                </h6>
                <p class="msg-info mb-0">
                    R$ {{ __moeda($cp['valor']) }} | <small class="fw-bold">Venc: {{ $cp['vencimento'] }}</small>
                </p>
                @if($atrasadoP)
                    <small class="text-danger fw-bold text-uppercase" style="font-size: 10px;">
                        Conta Atrasada!
                    </small>
                @endif
            </div>
        </div>
    </a>
@endforeach


{{-- 5. VALIDADE DE PRODUTOS --}}
@foreach($alertasVencimento as $v)
    <a class="dropdown-item alert-item border-bottom {{ $v['status'] == 'vencido' ? 'bg-light-danger' : '' }}" 
       href="{{ route('produtos.edit', $v['id']) }}">
        <div class="d-flex align-items-center">
            <div class="notify {{ $v['status'] == 'vencido' ? 'bg-danger text-white' : 'bg-light-warning text-warning' }}">
                <i class="bx bx-calendar-exclamation"></i>
            </div>
            <div class="flex-grow-1 ms-2">
                <h6 class="msg-name {{ $v['status'] == 'vencido' ? 'text-danger fw-bold' : '' }}">
                    {{ Str::limit($v['nome'], 25) }}
                </h6>
                <small class="{{ $v['status'] == 'vencido' ? 'text-danger fw-bold' : '' }}">
                    {{ $v['status'] == 'vencido' ? 'JÁ VENCIDO EM: ' : 'Vence em: ' }}{{ $v['vencimento'] }}
                </small>
            </div>
        </div>
    </a>
@endforeach


{{-- 6. ESTOQUE MÍNIMO --}}
@foreach($produtosComAlertaEstoque as $p)
    <a class="dropdown-item alert-item border-bottom" href="{{ route('produtos.edit', $p['id']) }}">
        <div class="d-flex align-items-center">
            <div class="notify bg-light-info text-info">
                <i class="bx bx-package"></i>
            </div>
            <div class="flex-grow-1 ms-2">
                <h6 class="msg-name">{{ Str::limit($p['nome'], 25) }}</h6>
                <small class="text-info fw-bold">Estoque Baixo: {{ $p['estoque'] }} unid.</small>
            </div>
        </div>
    </a>
@endforeach


{{-- 🔊 SOM DE NOVO PEDIDO --}}
<audio id="somPedido" src="/audio.mp3"></audio>
<script>
@if(count($dadosPedidos))
    document.getElementById('somPedido').play();
@endif
</script>


<script>
    $(document).ajaxError(function(event, jqxhr) {
        if (jqxhr.status === 419) {
            window.location.reload();
        }
    });

    document.querySelectorAll('.form-whatsapp').forEach(form => {
        form.addEventListener('submit', function(e) {
            const token = this.querySelector('input[name="_token"]').value;
            if (!token) {
                e.preventDefault();
                window.location.reload();
            }
        });
    });

    setInterval(function() {
        fetch(window.location.href, { method: 'HEAD' });
    }, 1000 * 60 * 30);
</script>