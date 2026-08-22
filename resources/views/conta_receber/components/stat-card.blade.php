@props(['label', 'value' => 0, 'color' => 'primary', 'subtext' => null, 'isCurrency' => true])

<div class="col-md-3">
    <div class="card p-3 shadow-sm border-start border-{{ $color }} border-4 h-100">
        <small class="text-muted fw-bold text-uppercase" style="font-size: 0.7rem;">{{ $label }}</small>
        <h4 class="text-{{ $color }} fw-bold mb-0">
            {{ $isCurrency ? 'R$ ' . number_format($value, 2, ',', '.') : $value }}
        </h4>
        @if($subtext)
            <small class="text-muted">{{ $subtext }}</small>
        @endif
    </div>
</div>