<div id="support-chat-v2">
    <button type="button" id="scv2-launcher" class="scv2-launcher">
        <i class='bx bx-message-rounded-dots'></i>
        <span>Suporte</span>
        <b id="scv2-badge" class="scv2-badge d-none">0</b>
    </button>

    <section id="scv2-panel" class="scv2-panel d-none">
        <header class="scv2-header">
            <button type="button" id="scv2-back" class="scv2-icon d-none"><i class='bx bx-arrow-back'></i></button>
            <div class="scv2-avatar"><i class='bx bx-support'></i></div>
            <div class="scv2-head-text">
                <strong id="scv2-title">Suporte NFe Notas</strong>
                <small id="scv2-subtitle">Seus atendimentos</small>
            </div>
            <button type="button" id="scv2-close" class="scv2-icon"><i class='bx bx-x'></i></button>
        </header>

        <div id="scv2-list" class="scv2-screen">
            <div class="scv2-actions">
                <button type="button" id="scv2-new" class="scv2-new"><i class='bx bx-plus'></i> Novo atendimento</button>
            </div>
            <div id="scv2-conversations" class="scv2-conversations">
                <div class="scv2-loading"><i class='bx bx-loader-alt bx-spin'></i> Carregando...</div>
            </div>
        </div>

        <div id="scv2-create" class="scv2-screen d-none">
            <form id="scv2-create-form" action="{{ route('tickets.store') }}" method="POST" enctype="multipart/form-data" class="scv2-create-form">
                @csrf
                <label>Departamento</label>
                <select name="departamento" class="form-select" required>
                    <option value="1">Suporte</option>
                    <option value="2">Conta e Vendas</option>
                </select>
                <label>Assunto</label>
                <input type="text" name="assunto" class="form-control" maxlength="100" required placeholder="Ex.: Erro ao emitir NF-e">
                <label>Mensagem</label>
                <textarea name="mensagem" class="form-control" rows="5" maxlength="5000" required placeholder="Explique o que aconteceu..."></textarea>
                <label class="scv2-file" for="scv2-create-image"><i class='bx bx-paperclip'></i> Anexar imagem</label>
                <input id="scv2-create-image" type="file" name="image" accept="image/*" class="d-none">
                <small id="scv2-create-file"></small>
                <button type="submit" class="scv2-primary" id="scv2-create-submit"><i class='bx bxs-send'></i> Iniciar atendimento</button>
            </form>
        </div>

        <div id="scv2-chat" class="scv2-screen d-none">
            <div id="scv2-ticket-info" class="scv2-ticket-info"></div>
            <div id="scv2-messages" class="scv2-messages"></div>
            <form id="scv2-message-form" action="{{ route('tickets.novaMensagem') }}" method="POST" enctype="multipart/form-data" class="scv2-composer">
                @csrf
                <input type="hidden" id="scv2-ticket-id" name="ticket_id">
                <label for="scv2-image" class="scv2-attach"><i class='bx bx-paperclip'></i></label>
                <input id="scv2-image" type="file" name="image" accept="image/*" class="d-none">
                <textarea id="scv2-message" name="mensagem" rows="1" maxlength="5000" placeholder="Digite uma mensagem..."></textarea>
                <button type="submit" id="scv2-send"><i class='bx bxs-send'></i></button>
            </form>
        </div>
    </section>
</div>

<style>
#support-chat-v2{position:fixed;right:22px;bottom:22px;z-index:9999;font-family:inherit}.scv2-launcher{position:relative;height:58px;border:0;border-radius:30px;padding:0 18px;background:#00a884;color:#fff;display:flex;align-items:center;gap:8px;font-weight:700;box-shadow:0 8px 24px rgba(0,0,0,.25)}.scv2-launcher i{font-size:26px}.scv2-badge{position:absolute;right:-4px;top:-6px;min-width:22px;height:22px;border-radius:12px;padding:0 6px;background:#e53935;color:#fff;border:2px solid #fff;font-size:11px;display:flex;align-items:center;justify-content:center}.scv2-panel{position:absolute;right:0;bottom:72px;width:410px;height:620px;max-height:calc(100vh - 110px);background:#efeae2;border:1px solid #d8dbdf;border-radius:16px;overflow:hidden;display:flex;flex-direction:column;box-shadow:0 16px 45px rgba(0,0,0,.28)}.scv2-header{height:66px;flex:0 0 66px;background:#008069;color:#fff;display:flex;align-items:center;gap:8px;padding:8px 10px}.scv2-avatar{width:40px;height:40px;border-radius:50%;background:#fff;color:#008069;display:flex;align-items:center;justify-content:center;font-size:22px}.scv2-head-text{flex:1;min-width:0;display:flex;flex-direction:column}.scv2-head-text strong,.scv2-head-text small{white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.scv2-head-text small{font-size:11px;opacity:.9}.scv2-icon{width:34px;height:34px;border:0;border-radius:50%;background:transparent;color:#fff;display:flex;align-items:center;justify-content:center;font-size:21px}.scv2-screen{flex:1;min-height:0;display:flex;flex-direction:column}.scv2-actions{padding:10px;background:#f0f2f5;border-bottom:1px solid #d8dbdf}.scv2-new,.scv2-primary{width:100%;border:0;border-radius:10px;background:#00a884;color:#fff;font-weight:700;padding:11px}.scv2-conversations{flex:1;overflow:auto;background:#fff}.scv2-loading,.scv2-empty{padding:35px 15px;text-align:center;color:#667781}.scv2-conv{width:100%;border:0;border-bottom:1px solid #edf0f2;background:#fff;padding:12px;display:flex;align-items:center;gap:10px;text-align:left}.scv2-conv:hover{background:#f5f6f6}.scv2-conv-icon{width:44px;height:44px;flex:0 0 44px;border-radius:50%;background:#e7f7f2;color:#008069;display:flex;align-items:center;justify-content:center;font-size:21px}.scv2-conv-main{flex:1;min-width:0}.scv2-conv-top{display:flex;justify-content:space-between;gap:8px}.scv2-conv-top strong{font-size:13px;color:#111b21;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.scv2-conv-top small,.scv2-conv-meta,.scv2-conv-preview{font-size:10px;color:#667781;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.scv2-unread{min-width:20px;height:20px;border-radius:10px;background:#25d366;color:#fff;font-size:10px;font-weight:800;display:flex;align-items:center;justify-content:center}.scv2-create-form{padding:16px;background:#fff;overflow:auto;flex:1}.scv2-create-form label{font-size:12px;font-weight:700;margin:9px 0 4px;color:#3b4a54}.scv2-create-form textarea{resize:none}.scv2-file{display:inline-flex!important;align-items:center;gap:4px;color:#008069!important;cursor:pointer}.scv2-primary{margin-top:14px}.scv2-ticket-info{padding:8px 12px;background:#f0f2f5;border-bottom:1px solid #d8dbdf;font-size:11px;color:#54656f}.scv2-ticket-info strong{display:block;font-size:12px;color:#111b21}.scv2-messages{flex:1;overflow:auto;padding:14px 12px;background:#efeae2}.scv2-row{display:flex;margin:3px 0 8px}.scv2-row.in{justify-content:flex-start}.scv2-row.out{justify-content:flex-end}.scv2-bubble{max-width:82%;padding:7px 8px 5px;border-radius:8px;box-shadow:0 1px 1px rgba(0,0,0,.12);font-size:13px;color:#111b21;word-break:break-word}.scv2-row.in .scv2-bubble{background:#fff}.scv2-row.out .scv2-bubble{background:#d9fdd3}.scv2-author{font-size:10px;font-weight:800;color:#008069;margin-bottom:2px}.scv2-time{text-align:right;font-size:9px;color:#667781;margin-top:3px}.scv2-bubble img{display:block;width:100%;max-width:240px;max-height:240px;object-fit:cover;border-radius:6px;margin-bottom:5px}.scv2-composer{display:flex;align-items:flex-end;gap:5px;padding:8px;background:#f0f2f5}.scv2-composer textarea{flex:1;resize:none;border:0;outline:0;background:#fff;border-radius:9px;min-height:40px;max-height:100px;padding:10px 11px;font-size:13px}.scv2-attach{width:38px;height:40px;display:flex;align-items:center;justify-content:center;font-size:21px;color:#54656f;cursor:pointer}.scv2-composer button{width:40px;height:40px;border-radius:50%;border:0;background:#00a884;color:#fff;font-size:19px}.scv2-closed{margin:8px;padding:10px;border-radius:8px;background:#fff3cd;color:#856404;text-align:center;font-size:11px}@media(max-width:576px){#support-chat-v2{right:12px;bottom:12px}.scv2-launcher{width:54px;height:54px;padding:0;justify-content:center}.scv2-launcher span{display:none}.scv2-panel{position:fixed;inset:0;width:100%;height:100%;max-height:none;border-radius:0;border:0}}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const listUrl = @json(route('tickets.index'));
    const messageUrl = @json(route('tickets.novaMensagem'));
    const storageKey = 'nfe_support_seen_messages';
    const panel=document.getElementById('scv2-panel'),launcher=document.getElementById('scv2-launcher'),closeBtn=document.getElementById('scv2-close'),backBtn=document.getElementById('scv2-back'),list=document.getElementById('scv2-list'),create=document.getElementById('scv2-create'),chat=document.getElementById('scv2-chat'),conversations=document.getElementById('scv2-conversations'),badge=document.getElementById('scv2-badge'),newBtn=document.getElementById('scv2-new'),title=document.getElementById('scv2-title'),subtitle=document.getElementById('scv2-subtitle'),createForm=document.getElementById('scv2-create-form'),createSubmit=document.getElementById('scv2-create-submit'),createImage=document.getElementById('scv2-create-image'),createFile=document.getElementById('scv2-create-file'),ticketInfo=document.getElementById('scv2-ticket-info'),messages=document.getElementById('scv2-messages'),messageForm=document.getElementById('scv2-message-form'),ticketId=document.getElementById('scv2-ticket-id'),messageInput=document.getElementById('scv2-message'),imageInput=document.getElementById('scv2-image'),sendBtn=document.getElementById('scv2-send');
    let active=null;

    function esc(v){const d=document.createElement('div');d.textContent=v==null?'':v;return d.innerHTML}
    function screen(el){[list,create,chat].forEach(x=>x.classList.add('d-none'));el.classList.remove('d-none')}
    function headerDefault(){backBtn.classList.add('d-none');title.textContent='Suporte NFe Notas';subtitle.textContent='Seus atendimentos'}
    function seen(){try{return JSON.parse(localStorage.getItem(storageKey)||'{}')}catch(e){return{}}}
    function saveSeen(v){localStorage.setItem(storageKey,JSON.stringify(v))}
    function errorText(d,f){if(d&&d.errors){const first=Object.values(d.errors)[0];if(first&&first[0])return first[0]}return(d&&d.message)||f}
    function unreadFor(c){const s=seen();return c.ultima_mensagem_suporte&&Number(c.ultima_mensagem_id||0)>Number(s[c.id]||0)}
    function updateBadge(items){const n=(items||[]).filter(unreadFor).length;badge.textContent=n>9?'9+':n;badge.classList.toggle('d-none',n===0)}
    function convHtml(c){const unread=unreadFor(c)?'<span class="scv2-unread">1</span>':'';return `<button type="button" class="scv2-conv" data-url="${esc(c.show_url)}"><div class="scv2-conv-icon"><i class='bx bx-message-rounded-dots'></i></div><div class="scv2-conv-main"><div class="scv2-conv-top"><strong>#${c.id} · ${esc(c.assunto)}</strong><small>${esc(c.updated_at||'')}</small></div><div class="scv2-conv-meta">${esc(c.departamento)} · ${esc(c.estado_label)}</div><div class="scv2-conv-preview">${esc(c.ultima_mensagem||'')}</div></div>${unread}</button>`}
    function bubbleHtml(m){const side=m.suporte?'in':'out',author=m.suporte?'<div class="scv2-author">Suporte NFe Notas</div>':'',text=esc(m.mensagem||'').replace(/\n/g,'<br>'),image=m.imagem?`<a href="${m.imagem}" target="_blank" rel="noopener"><img src="${m.imagem}" alt="Anexo"></a>`:'';return `<div class="scv2-row ${side}"><div class="scv2-bubble">${author}${image}<div>${text}</div><div class="scv2-time">${esc(m.data||'')}</div></div></div>`}

    async function loadList(){try{const r=await fetch(listUrl,{headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}}),d=await r.json();if(!r.ok||!d.success)throw new Error(errorText(d,'Erro ao carregar atendimentos.'));updateBadge(d.conversations);conversations.innerHTML=d.conversations.length?d.conversations.map(convHtml).join(''):'<div class="scv2-empty">Nenhum atendimento ainda.</div>'}catch(e){conversations.innerHTML='<div class="scv2-empty">'+esc(e.message)+'</div>'}}
    function markSeen(c){if(!c||!c.ticket||!c.mensagens)return;const support=c.mensagens.filter(m=>m.suporte);if(!support.length)return;const s=seen();s[c.ticket.id]=support[support.length-1].id;saveSeen(s)}
    function render(c){active=c;markSeen(c);ticketId.value=c.ticket.id;backBtn.classList.remove('d-none');title.textContent='Atendimento #'+c.ticket.id;subtitle.textContent=c.ticket.assunto;ticketInfo.innerHTML='<strong>'+esc(c.ticket.assunto)+'</strong>'+esc(c.ticket.departamento)+' · '+esc(c.ticket.estado_label);messages.innerHTML=c.mensagens.length?c.mensagens.map(bubbleHtml).join(''):'<div class="scv2-empty">Nenhuma mensagem.</div>';messageForm.classList.toggle('d-none',c.ticket.finalizado);if(c.ticket.finalizado)messages.insertAdjacentHTML('beforeend','<div class="scv2-closed">'+esc(c.ticket.mensagem_finalizar||'Atendimento finalizado.')+'</div>');screen(chat);messages.scrollTop=messages.scrollHeight;loadList()}
    async function openChat(url){try{const sep=url.includes('?')?'&':'?',r=await fetch(url+sep+'ajax=1',{headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}}),d=await r.json();if(!r.ok||!d.success)throw new Error(errorText(d,'Não foi possível abrir a conversa.'));render(d.chat)}catch(e){if(window.swal)swal('Erro',e.message,'error');else alert(e.message)}}

    launcher.addEventListener('click',()=>{panel.classList.toggle('d-none');if(!panel.classList.contains('d-none')){headerDefault();screen(list);loadList()}});
    closeBtn.addEventListener('click',()=>panel.classList.add('d-none'));
    backBtn.addEventListener('click',()=>{active=null;headerDefault();screen(list);loadList()});
    newBtn.addEventListener('click',()=>{backBtn.classList.remove('d-none');title.textContent='Novo atendimento';subtitle.textContent='Fale com nosso suporte';screen(create)});
    conversations.addEventListener('click',e=>{const b=e.target.closest('.scv2-conv');if(b)openChat(b.dataset.url)});
    createImage.addEventListener('change',function(){createFile.textContent=this.files[0]?this.files[0].name:''});

    createForm.addEventListener('submit',async e=>{e.preventDefault();createSubmit.disabled=true;const old=createSubmit.innerHTML;createSubmit.innerHTML="<i class='bx bx-loader-alt bx-spin'></i> Enviando...";try{const r=await fetch(createForm.action,{method:'POST',body:new FormData(createForm),headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}}),d=await r.json();if(!r.ok||!d.success)throw new Error(errorText(d,'Não foi possível iniciar o atendimento.'));createForm.reset();createFile.textContent='';render(d.chat)}catch(err){if(window.swal)swal('Erro',err.message,'error');else alert(err.message)}finally{createSubmit.disabled=false;createSubmit.innerHTML=old}});

    messageInput.addEventListener('input',function(){this.style.height='auto';this.style.height=Math.min(this.scrollHeight,100)+'px'});
    messageInput.addEventListener('keydown',e=>{if(e.key==='Enter'&&!e.shiftKey){e.preventDefault();if(messageInput.value.trim()||imageInput.files.length)messageForm.requestSubmit()}});
    messageForm.addEventListener('submit',async e=>{e.preventDefault();if(!active||(!messageInput.value.trim()&&!imageInput.files.length))return;sendBtn.disabled=true;const old=sendBtn.innerHTML;sendBtn.innerHTML="<i class='bx bx-loader-alt bx-spin'></i>";try{const fd=new FormData(messageForm);fd.set('ticket_id',active.ticket.id);const r=await fetch(messageUrl,{method:'POST',body:fd,headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}}),d=await r.json();if(!r.ok||!d.success)throw new Error(errorText(d,'Não foi possível enviar a mensagem.'));messageInput.value='';messageInput.style.height='auto';imageInput.value='';render(d.chat)}catch(err){if(window.swal)swal('Erro',err.message,'error');else alert(err.message)}finally{sendBtn.disabled=false;sendBtn.innerHTML=old;messageInput.focus()}});

    loadList();setInterval(()=>{if(active&&!chat.classList.contains('d-none'))openChat(active.ticket.show_url);else loadList()},5000);
});
</script>