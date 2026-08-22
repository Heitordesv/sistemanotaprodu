@extends('default.layout',['title' => 'Central de E-mails'])

@section('content')
<div class="page-content">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h4 class="mb-0">Central de E-mails</h4>
                <span class="badge bg-dark">Administrador</span>
            </div>
            <p class="text-muted mb-0 mt-1">Visualize a caixa de entrada administrativa e envie comunicados para empresas.</p>
        </div>

        @if(($tab ?? 'entrada') === 'entrada')
            <a href="{{ route('emails.index', ['tab' => 'entrada']) }}" class="btn btn-light">
                <i class='bx bx-refresh'></i> Atualizar
            </a>
        @endif
    </div>

    <div class="email-tabs mb-4">
        <a href="{{ route('emails.index', ['tab' => 'entrada']) }}"
           class="email-tab {{ ($tab ?? 'entrada') === 'entrada' ? 'active' : '' }}">
            <i class='bx bx-inbox'></i> Caixa de entrada
        </a>

        <a href="{{ route('emails.index', ['tab' => 'enviar']) }}"
           class="email-tab {{ ($tab ?? 'entrada') === 'enviar' ? 'active' : '' }}">
            <i class='bx bx-send'></i> Enviar e-mail
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if(($tab ?? 'entrada') === 'entrada')
        @if(!empty($erroCaixa))
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center p-5">
                    <div class="mail-big-icon mx-auto mb-3">
                        <i class='bx bx-envelope-open'></i>
                    </div>

                    <h5>Não foi possível abrir a caixa de entrada</h5>
                    <p class="text-muted">{{ $erroCaixa }}</p>

                    <div class="alert alert-light border text-start mb-0">
                        <strong>Configuração da caixa administrativa:</strong><br>
                        <code>MAIL_INBOX_HOST=mail.nfenotas.com.br</code><br>
                        <code>MAIL_INBOX_PORT=993</code><br>
                        <code>MAIL_INBOX_USERNAME=suporte@nfenotas.com.br</code><br>
                        <code>MAIL_INBOX_PASSWORD=********</code><br>
                        <code>MAIL_INBOX_ENCRYPTION=ssl</code><br>
                        <code>MAIL_INBOX_VALIDATE_CERT=true</code><br>
                        <code>MAIL_INBOX_FOLDER=INBOX</code>
                        <hr>
                        <small class="text-muted">
                            A conta da caixa de entrada é independente do SMTP usado para enviar e-mails.
                            O servidor também precisa estar com a extensão PHP IMAP habilitada.
                        </small>
                    </div>
                </div>
            </div>
        @elseif(!empty($mensagemSelecionada))
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="p-3 p-lg-4 border-bottom d-flex gap-3 align-items-start">
                        <a href="{{ route('emails.index', ['tab' => 'entrada', 'q' => $q ?? '']) }}"
                           class="btn btn-light btn-sm">
                            <i class='bx bx-arrow-back'></i>
                        </a>

                        <div class="flex-grow-1 min-w-0">
                            <h5 class="mb-2 text-break">{{ $mensagemSelecionada['subject'] }}</h5>

                            <div class="small">
                                <strong>{{ $mensagemSelecionada['from_name'] ?: $mensagemSelecionada['from_email'] }}</strong>
                                @if($mensagemSelecionada['from_email'])
                                    <span class="text-muted">&lt;{{ $mensagemSelecionada['from_email'] }}&gt;</span>
                                @endif
                            </div>

                            @if(!empty($mensagemSelecionada['to']))
                                <div class="small text-muted">Para: {{ $mensagemSelecionada['to'] }}</div>
                            @endif

                            @if(!empty($mensagemSelecionada['cc']))
                                <div class="small text-muted">Cc: {{ $mensagemSelecionada['cc'] }}</div>
                            @endif
                        </div>

                        @if(!empty($mensagemSelecionada['date']))
                            <small class="text-muted text-nowrap">
                                {{ \Carbon\Carbon::createFromTimestamp($mensagemSelecionada['date'])->format('d/m/Y H:i') }}
                            </small>
                        @endif
                    </div>

                    @if(!empty($mensagemSelecionada['attachments']))
                        <div class="px-4 py-3 bg-light border-bottom">
                            <strong class="small">
                                <i class='bx bx-paperclip'></i> Anexos
                            </strong>

                            <div class="d-flex flex-wrap gap-2 mt-2">
                                @foreach($mensagemSelecionada['attachments'] as $attachment)
                                    <span class="attachment-chip">
                                        <i class='bx bx-file'></i>
                                        {{ $attachment['name'] ?: 'Anexo' }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="mail-body p-4">
                        {!! nl2br(e($mensagemSelecionada['body'] ?: 'Este e-mail não possui conteúdo de texto para exibição.')) !!}
                    </div>
                </div>
            </div>
        @else
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <small class="text-muted">Mensagens exibidas</small>
                            <h4 class="mb-0">{{ count($emails ?? []) }}</h4>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <small class="text-muted">Não lidos exibidos</small>
                            <h4 class="mb-0">{{ $naoLidos ?? 0 }}</h4>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <small class="text-muted">Conta</small>
                            <h6 class="mb-0">{{ config('mail.imap.username') ?: 'Não configurada' }}</h6>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <form method="GET"
                          action="{{ route('emails.index') }}"
                          class="p-3 border-bottom d-flex gap-2 flex-column flex-md-row">
                        <input type="hidden" name="tab" value="entrada">

                        <div class="input-group flex-grow-1">
                            <span class="input-group-text bg-white">
                                <i class='bx bx-search'></i>
                            </span>
                            <input type="search"
                                   class="form-control"
                                   name="q"
                                   value="{{ $q ?? '' }}"
                                   placeholder="Buscar por remetente, e-mail ou assunto">
                        </div>

                        <button class="btn btn-primary">Pesquisar</button>

                        @if(!empty($q))
                            <a href="{{ route('emails.index', ['tab' => 'entrada']) }}" class="btn btn-light">
                                Limpar
                            </a>
                        @endif
                    </form>

                    <div class="mail-list">
                        @forelse($emails ?? [] as $email)
                            <a href="{{ route('emails.index', ['tab' => 'entrada', 'uid' => $email['uid'], 'q' => $q ?? '']) }}"
                               class="mail-row {{ !($email['seen'] ?? false) ? 'unread' : '' }}">
                                <div class="mail-avatar">
                                    {{ mb_strtoupper(mb_substr($email['from_name'] ?: $email['from_email'] ?: '?', 0, 1)) }}
                                </div>

                                <div class="flex-grow-1 min-w-0">
                                    <div class="d-flex justify-content-between gap-2">
                                        <strong class="text-truncate">
                                            {{ $email['from_name'] ?: $email['from_email'] ?: 'Remetente desconhecido' }}
                                        </strong>

                                        <small class="text-muted text-nowrap">
                                            @if(!empty($email['date']))
                                                {{ \Carbon\Carbon::createFromTimestamp($email['date'])->isToday()
                                                    ? \Carbon\Carbon::createFromTimestamp($email['date'])->format('H:i')
                                                    : \Carbon\Carbon::createFromTimestamp($email['date'])->format('d/m/Y') }}
                                            @endif
                                        </small>
                                    </div>

                                    <div class="mail-subject text-truncate">{{ $email['subject'] }}</div>
                                    <div class="small text-muted text-truncate">{{ $email['from_email'] }}</div>
                                </div>

                                @if(!($email['seen'] ?? false))
                                    <span class="unread-dot"></span>
                                @endif

                                <i class='bx bx-chevron-right text-muted fs-5'></i>
                            </a>
                        @empty
                            <div class="text-center py-5">
                                <div class="mail-big-icon mx-auto mb-3">
                                    <i class='bx bx-inbox'></i>
                                </div>
                                <h5>Nenhum e-mail encontrado</h5>
                                <p class="text-muted mb-0">
                                    {{ !empty($q) ? 'Tente outra pesquisa.' : 'A caixa de entrada está vazia.' }}
                                </p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif
    @else
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h5 class="mb-1">Enviar e-mail para empresas</h5>
                <p class="text-muted mb-4">Selecione os destinatários e escreva o comunicado.</p>

                <form method="POST" action="{{ route('emails.enviar') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Assunto</label>
                        <input type="text"
                               name="assunto"
                               value="{{ old('assunto') }}"
                               class="form-control"
                               maxlength="255"
                               required>
                        @error('assunto')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Mensagem</label>
                        <textarea id="mensagem"
                                  name="mensagem"
                                  rows="7"
                                  class="form-control"
                                  required>{{ old('mensagem') }}</textarea>
                        @error('mensagem')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0">Empresas</label>
                            <button type="button" id="selecionarTodos" class="btn btn-sm btn-outline-primary">
                                Selecionar todas
                            </button>
                        </div>

                        <div class="company-list border rounded">
                            @foreach($empresas ?? [] as $empresa)
                                <label class="company-row" for="empresa{{ $empresa->id }}">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           name="empresas[]"
                                           value="{{ $empresa->id }}"
                                           id="empresa{{ $empresa->id }}"
                                           {{ in_array($empresa->id, old('empresas', [])) ? 'checked' : '' }}>

                                    <span class="min-w-0">
                                        <strong class="d-block text-truncate">{{ $empresa->razao_social }}</strong>
                                        <small class="text-muted">{{ $empresa->email }}</small>
                                    </span>
                                </label>
                            @endforeach
                        </div>

                        @error('empresas')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="text-end">
                        <button class="btn btn-success px-4">
                            <i class='bx bx-send'></i> Enviar e-mails
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection

@section('css')
<style>
.email-tabs{display:inline-flex;padding:5px;border:1px solid #e5e7eb;border-radius:12px;background:#fff;gap:4px}.email-tab{padding:9px 14px;border-radius:9px;text-decoration:none;color:#667085;font-weight:600}.email-tab.active{background:#eef4ff;color:#2563eb}.mail-row{display:flex;align-items:center;gap:12px;padding:14px 16px;border-bottom:1px solid #eef1f4;text-decoration:none;color:#344054}.mail-row:hover{background:#f8faff;color:#344054}.mail-row.unread{background:#fbfdff}.mail-row.unread strong,.mail-row.unread .mail-subject{font-weight:700;color:#101828}.mail-avatar{width:42px;height:42px;flex:0 0 42px;border-radius:50%;background:#eef2f6;display:flex;align-items:center;justify-content:center;font-weight:700}.unread-dot{width:8px;height:8px;border-radius:50%;background:#2563eb;flex:0 0 8px}.mail-subject{font-size:13px}.mail-body{min-height:280px;line-height:1.7;overflow-wrap:anywhere}.mail-big-icon{width:62px;height:62px;border-radius:20px;background:#eef4ff;color:#2563eb;display:flex;align-items:center;justify-content:center;font-size:30px}.attachment-chip{display:inline-flex;align-items:center;gap:5px;padding:7px 10px;background:#fff;border:1px solid #dfe3e8;border-radius:8px;font-size:11px}.company-list{max-height:330px;overflow:auto}.company-row{display:flex;align-items:center;gap:10px;padding:11px 13px;border-bottom:1px solid #eef1f4;cursor:pointer}.company-row:last-child{border-bottom:0}.company-row:hover{background:#f8faff}.min-w-0{min-width:0}@media(max-width:576px){.email-tabs{display:flex;width:100%}.email-tab{flex:1;text-align:center;padding:9px 5px}.mail-row{padding:12px 10px}.mail-avatar{width:36px;height:36px;flex-basis:36px}}
</style>

@if(($tab ?? 'entrada') === 'enviar')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/dist/ui/trumbowyg.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/dist/plugins/emoji/ui/trumbowyg.emoji.min.css">
@endif
@endsection

@section('js')
@if(($tab ?? 'entrada') === 'enviar')
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/dist/trumbowyg.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/dist/plugins/emoji/trumbowyg.emoji.min.js"></script>
<script>
$(function(){
    if ($('#mensagem').length && $.fn.trumbowyg) {
        $('#mensagem').trumbowyg({
            btns:[['formatting'],['bold','italic','underline'],['link'],['emoji'],['unorderedList','orderedList'],['removeformat']],
            autogrow:true
        });
    }

    $('#selecionarTodos').on('click', function(){
        const boxes = document.querySelectorAll('input[name="empresas[]"]');
        const all = Array.from(boxes).every(box => box.checked);
        boxes.forEach(box => box.checked = !all);
    });
});
</script>
@endif
@endsection