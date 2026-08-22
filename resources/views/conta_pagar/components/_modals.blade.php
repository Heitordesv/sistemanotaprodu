{{-- Modal Global para Upload de Comprovante --}}
<div class="modal fade" id="comprovanteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="formComprovante" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Anexar Comprovante</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-dark fw-bold">Arquivo (PDF, JPG, PNG)</label>
                        <input type="file" name="comprovante" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-dark fw-bold">Observação</label>
                        <textarea name="observacao" class="form-control" rows="2" placeholder="Opcional..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Salvar Arquivo</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Modal Global para Visualização de Comprovante --}}
<div class="modal fade" id="modalVisualizarComprovante" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">Visualizar Comprovante</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center" id="conteudoComprovante">
                </div>
            <div class="modal-footer">
                <a href="" id="btnDownloadComp" target="_blank" class="btn btn-primary" download>Baixar Arquivo</a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

{{-- Modais Dinâmicos de Detalhes --}}
@foreach($data as $item)
    <div class="modal fade" id="modalPagamento-{{ $item->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">Detalhes da Conta #{{ $item->id }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="p-3 border rounded bg-light">
                        <p class="mb-1"><b>Valor Integral:</b> R$ {{ __moeda($item->valor_integral) }}</p>
                        <p class="mb-1"><b>Valor Pago:</b> R$ {{ __moeda($item->valor_pago) }}</p>
                        <hr>
                        @forelse($item->detalhesPagamento as $detalhe)
                            <div class="mb-2">
                                <span class="badge bg-primary">{{ ucfirst($detalhe->tipo_pagamento) }}</span>
                                @if($detalhe->boleto_codigo) <div class="small mt-1"><b>Cód:</b> {{ $detalhe->boleto_codigo }}</div> @endif
                            </div>
                        @empty
                            <p class="text-muted text-center mb-0">Nenhum detalhe extra.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Modal Comprovantes --}}
<div class="modal fade" id="modalComprovantes-{{ $item->id }}" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">

            {{-- Header --}}
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bx bx-images me-2"></i>
                    Comprovantes - Conta #{{ $item->id }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            {{-- Body --}}
            <div class="modal-body bg-light">
                <div class="row g-3">

                    @forelse($item->comprovantes as $comprovante)
                        @php 
                            $ext = strtolower(pathinfo($comprovante->arquivo, PATHINFO_EXTENSION));
                            $isPdf = $ext === 'pdf';
                            $url = Storage::url($comprovante->arquivo);
                        @endphp

                        <div class="col-md-3 col-sm-6">
                            <div class="card comprovante-card h-100 border-0 shadow-sm">

                                {{-- Badge --}}
                                <span class="badge bg-{{ $isPdf ? 'danger' : 'primary' }} position-absolute top-0 end-0 m-2">
                                    {{ $isPdf ? 'PDF' : 'IMG' }}
                                </span>

                                {{-- Preview --}}
                                <div class="preview-container"
                                     onclick="abrirPreview('{{ $url }}', {{ $isPdf ? 'true' : 'false' }})">

                                    @if($isPdf)
                                        <div class="d-flex align-items-center justify-content-center h-100 bg-white">
                                            <i class="bx bxs-file-pdf text-danger" style="font-size: 4rem;"></i>
                                        </div>
                                    @else
                                        <img src="{{ $url }}" class="preview-img">
                                    @endif

                                </div>

                                {{-- Info --}}
                                <div class="card-body p-2 text-center">
                                    <small class="text-muted d-block">
                                        <i class="bx bx-calendar"></i>
                                        {{ __data_pt($comprovante->data_upload) }}
                                    </small>

                                    @if($comprovante->observacao)
                                        <small class="text-secondary d-block text-truncate">
                                            "{{ $comprovante->observacao }}"
                                        </small>
                                    @endif
                                </div>

                                {{-- Actions --}}
                                <div class="card-footer bg-white border-0 d-flex justify-content-between px-2 pb-2">
                                    <a href="{{ $url }}" target="_blank" class="btn btn-sm btn-primary">
                                        <i class="bx bx-show"></i>
                                    </a>
                                    <a href="{{ $url }}" download class="btn btn-sm btn-success">
                                        <i class="bx bx-download"></i>
                                    </a>
                                </div>

                            </div>
                        </div>

                    @empty
                        <div class="col-12 text-center py-5">
                            <i class="bx bx-folder-open text-secondary" style="font-size: 4rem;"></i>
                            <p class="text-muted mt-2">Nenhum comprovante encontrado</p>
                        </div>
                    @endforelse

                </div>
            </div>

            {{-- Footer --}}
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">
                    Fechar
                </button>
            </div>

        </div>
    </div>
</div>

@endforeach