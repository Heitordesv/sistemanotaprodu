@if(session('user_logged') && (session('user_logged')['super'] ?? false))
<div id="super-chat-v2">
    <button type="button" id="sup-launcher" class="sup-launcher">
        <i class='bx bx-message-rounded-dots'></i>
        <span>Atendimentos</span>
        <b id="sup-badge" class="sup-badge d-none">0</b>
    </button>

    <section id="sup-panel" class="sup-panel d-none">
        <header class="sup-header">
            <button type="button" id="sup-back" class="sup-icon d-none"><i class='bx bx-arrow-back'></i></button>
            <div class="sup-avatar"><i class='bx bx-support'></i></div>
            <div class="sup-head-text">
                <strong id="sup-title">Central de Suporte</strong>
                <small id="sup-subtitle">Conversas com empresas</small>
            </div>
            <a href="{{ route('ticketsSuper.index') }}" class="sup-icon" title="Abrir central completa"><i class='bx bx-expand-alt'></i></a>
            <button type="button" id="sup-close" class="sup-icon"><i class='bx bx-x'></i></button>
        </header>

        <div id="sup-list" class="sup-screen">
            <div id="sup-conversations" class="sup-conversations">
                <div class="sup-empty"><i class='bx bx-loader-alt bx-spin'></i> Carregando...</div>
            </div>
        </div>

        <div id="sup-chat" class="sup-screen d-none">
            <div id="sup-ticket-info" class="sup-ticket-info"></div>
            <div id="sup-messages" class="sup-messages"></div>

            <form id="sup-form" action="{{ route('tickets.novaMensagem') }}" method="POST" enctype="multipart/form-data" class="sup-composer">
                @csrf
                <input type="hidden" id="sup-ticket-id" name="ticket_id">
                <label for="sup-image" class="sup-attach"><i class='bx bx-paperclip'></i></label>
                <input type="file" id="sup-image" name="image" accept="image/*" class="d-none">
                <textarea id="sup-message" name="mensagem" rows="1" maxlength="5000" placeholder="Digite uma mensagem..."></textarea>
                <button type="submit" id="sup-send"><i class='bx bxs-send'></i></button>
            </form>
        </div>
    </section>
</div>

<style>
#super-chat-v2{position:fixed;right:22px;bottom:22px;z-index:10000;font-family:inherit}.sup-launcher{position:relative;height:58px;border:0;border-radius:30px;padding:0 18px;background:#00a884;color:#fff;display:flex;align-items:center;gap:8px;font-weight:700;box-shadow:0 8px 24px rgba(0,0,0,.25)}.sup-launcher i{font-size:26px}.sup-badge{position:absolute;right:-4px;top:-6px;min-width:22px;height:22px;padding:0 6px;border-radius:12px;background:#e53935;color:#fff;border:2px solid #fff;font-size:11px;display:flex;align-items:center;justify-content:center}.sup-panel{position:absolute;right:0;bottom:72px;width:410px;height:620px;max-height:calc(100vh - 110px);background:#efeae2;border:1px solid #d8dbdf;border-radius:16px;overflow:hidden;display:flex;flex-direction:column;box-shadow:0 16px 45px rgba(0,0,0,.28)}.sup-header{height:66px;flex:0 0 66px;background:#008069;color:#fff;display:flex;align-items:center;gap:8px;padding:8px 10px}.sup-avatar{width:40px;height:40px;border-radius:50%;background:#fff;color:#008069;display:flex;align-items:center;justify-content:center;font-size:22px;flex:0 0 40px}.sup-head-text{flex:1;min-width:0;display:flex;flex-direction:column}.sup-head-text strong,.sup-head-text small{white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.sup-head-text small{font-size:11px;opacity:.9}.sup-icon{width:34px;height:34px;border:0;border-radius:50%;background:transparent;color:#fff;display:flex;align-items:center;justify-content:center;font-size:21px;text-decoration:none}.sup-icon:hover{background:rgba(255,255,255,.14);color:#fff}.sup-screen{flex:1;min-height:0;display:flex;flex-direction:column}.sup-conversations{flex:1;overflow:auto;background:#fff}.sup-conv{width:100%;border:0;border-bottom:1px solid #edf0f2;background:#fff;padding:12px;display:flex;align-items:center;gap:10px;text-align:left}.sup-conv:hover{background:#f5f6f6}.sup-conv-avatar{width:44px;height:44px;flex:0 0 44px;border-radius:50%;background:#e7f7f2;color:#008069;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800}.sup-conv-main{flex:1;min-width:0}.sup-conv-top{display:flex;justify-content:space-between;gap:8px}.sup-conv-top strong{font-size:13px;color:#111b21;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.sup-conv-top small,.sup-conv-meta,.sup-conv-preview{font-size:10px;color:#667781;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.sup-dot{width:10px;height:10px;border-radius:50%;background:#25d366;flex:0 0 10px}.sup-ticket-info{padding:8px 12px;background:#f0f2f5;border-bottom:1px solid #d8dbdf;font-size:11px;color:#54656f}.sup-ticket-info strong{display:block;font-size:12px;color:#111b21}.sup-messages{flex:1;overflow:auto;padding:14px 12px;background:#efeae2}.sup-row{display:flex;margin:3px 0 8px}.sup-row.in{justify-content:flex-start}.sup-row.out{justify-content:flex-end}.sup-bubble{max-width:82%;padding:7px 8px 5px;border-radius:8px;box-shadow:0 1px 1px rgba(0,0,0,.12);font-size:13px;color:#111b21;word-break:break-word}.sup-row.in .sup-bubble{background:#fff}.sup-row.out .sup-bubble{background:#d9fdd3}.sup-author{font-size:10px;font-weight:800;color:#008069;margin-bottom:2px}.sup-time{text-align:right;font-size:9px;color:#667781;margin-top:3px}.sup-check{font-size:12px;color:#53bdeb;margin-left:3px}.sup-bubble img{display:block;width:100%;max-width:240px;max-height:240px;object-fit:cover;border-radius:6px;margin-bottom:5px}.sup-composer{display:flex;align-items:flex-end;gap:5px;padding:8px;background:#f0f2f5}.sup-composer textarea{flex:1;resize:none;border:0;outline:0;background:#fff;border-radius:9px;min-height:40px;max-height:100px;padding:10px 11px;font-size:13px}.sup-attach{width:38px;height:40px;display:flex;align-items:center;justify-content:center;font-size:21px;color:#54656f;cursor:pointer}.sup-composer button{width:40px;height:40px;border-radius:50%;border:0;background:#00a884;color:#fff;font-size:19px}.sup-empty{text-align:center;color:#667781;padding:40px 15px;font-size:12px}.sup-closed{margin:8px;padding:10px;border-radius:8px;background:#fff3cd;color:#856404;text-align:center;font-size:11px}@media(max-width:576px){#super-chat-v2{right:12px;bottom:12px}.sup-launcher{width:54px;height:54px;padding:0;justify-content:center}.sup-launcher>span{display:none}.sup-panel{position:fixed;inset:0;width:100%;height:100%;max-height:none;border-radius:0;border:0}}
</style>

<script>
document.addEventListener('DOMContentLoaded',function(){
    const listUrl=@json(route('ticketsSuper.index'));
    const launcher=document.getElementById('sup-launcher');
    const panel=document.getElementById('sup-panel');
    const closeBtn=document.getElementById('sup-close');
    const backBtn=document.getElementById('sup-back');
    const listView=document.getElementById('sup-list');
    const chatView=document.getElementById('sup-chat');
    const conversations=document.getElementById('sup-conversations');
    const messages=document.getElementById('sup-messages');
    const form=document.getElementById('sup-form');
    const ticketId=document.getElementById('sup-ticket-id');
    const textarea=document.getElementById('sup-message');
    const image=document.getElementById('sup-image');
    const send=document.getElementById('sup-send');
    const title=document.getElementById('sup-title');
    const subtitle=document.getElementById('sup-subtitle');
    const info=document.getElementById('sup-ticket-info');
    const badge=document.getElementById('sup-badge');
    let active=null;
    let lastSeen=parseInt(localStorage.getItem('nfe_super_last_client_message')||'0',10);

    function esc(v){const d=document.createElement('div');d.textContent=v==null?'':v;return d.innerHTML}
    function initials(v){return (v||'E').split(' ').filter(Boolean).slice(0,2).map(x=>x[0]).join('').toUpperCase()}
    function setBadge(n){n=parseInt(n||0,10);badge.textContent=n>9?'9+':n;badge.classList.toggle('d-none',n===0)}
    function showList(){active=null;chatView.classList.add('d-none');listView.classList.remove('d-none');backBtn.classList.add('d-none');title.textContent='Central de Suporte';subtitle.textContent='Conversas com empresas';loadList()}
    function convHtml(c){const pending=(c.ultima_mensagem_cliente&&parseInt(c.ultima_mensagem_id||0,10)>lastSeen)?'<span class="sup-dot"></span>':'';return `<button type="button" class="sup-conv" data-url="${esc(c.show_url)}"><div class="sup-conv-avatar">${esc(initials(c.empresa))}</div><div class="sup-conv-main"><div class="sup-conv-top"><strong>${esc(c.empresa)}</strong><small>${esc(c.updated_at||'')}</small></div><div class="sup-conv-meta">#${c.id} · ${esc(c.assunto)} · ${esc(c.estado_label)}</div><div class="sup-conv-preview">${esc(c.ultima_mensagem||'Sem mensagens')}</div></div>${pending}</button>`}
    function bubbleHtml(m){const side=m.suporte?'out':'in';const author=!m.suporte?'<div class="sup-author">'+esc(m.usuario||'Cliente')+'</div>':'';const text=esc(m.mensagem||'').replace(/\n/g,'<br>');const img=m.imagem?`<a href="${m.imagem}" target="_blank" rel="noopener"><img src="${m.imagem}" alt="Anexo"></a>`:'';const check=m.suporte?'<i class="bx bx-check-double sup-check"></i>':'';return `<div class="sup-row ${side}"><div class="sup-bubble">${author}${img}${text?'<div>'+text+'</div>':''}<div class="sup-time">${esc(m.data||'')}${check}</div></div></div>`}

    async function loadList(){
        try{
            const r=await fetch(listUrl,{headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}});
            const d=await r.json();
            if(!r.ok||!d.success)throw new Error(d.message||'Erro ao carregar conversas.');
            const latest=parseInt(d.latest_client_message_id||0,10);
            setBadge(latest>lastSeen?1:0);
            conversations.innerHTML=d.conversations&&d.conversations.length?d.conversations.map(convHtml).join(''):'<div class="sup-empty">Nenhuma conversa.</div>';
        }catch(e){conversations.innerHTML='<div class="sup-empty">'+esc(e.message||'Erro ao carregar conversas.')+'</div>'}
    }

    async function openChat(url){
        try{
            const r=await fetch(url+(url.includes('?')?'&':'?')+'ajax=1',{headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}});
            const d=await r.json();
            if(!r.ok||!d.success||!d.chat)throw new Error(d.message||'Não foi possível abrir a conversa.');
            active=d.chat;
            const clientMessages=active.mensagens.filter(m=>!m.suporte);
            const lastClient=clientMessages.length?parseInt(clientMessages[clientMessages.length-1].id||0,10):0;
            if(lastClient>lastSeen){lastSeen=lastClient;localStorage.setItem('nfe_super_last_client_message',String(lastSeen));}
            setBadge(0);
            ticketId.value=active.ticket.id;
            title.textContent=active.ticket.empresa;
            subtitle.textContent='#'+active.ticket.id+' · '+active.ticket.assunto;
            info.innerHTML='<strong>'+esc(active.ticket.assunto)+'</strong>'+esc(active.ticket.departamento)+' · '+esc(active.ticket.estado_label);
            messages.innerHTML=active.mensagens.length?active.mensagens.map(bubbleHtml).join(''):'<div class="sup-empty">Nenhuma mensagem.</div>';
            form.classList.toggle('d-none',!!active.ticket.finalizado);
            if(active.ticket.finalizado)messages.insertAdjacentHTML('beforeend','<div class="sup-closed">'+esc(active.ticket.mensagem_finalizar||'Atendimento finalizado.')+'</div>');
            listView.classList.add('d-none');chatView.classList.remove('d-none');backBtn.classList.remove('d-none');messages.scrollTop=messages.scrollHeight;
        }catch(e){if(window.swal)swal('Erro',e.message,'error');else alert(e.message)}
    }

    async function refreshChat(){if(!active||active.ticket.finalizado)return;await openChat(active.ticket.show_url)}

    launcher.addEventListener('click',()=>{panel.classList.toggle('d-none');if(!panel.classList.contains('d-none'))showList()});
    closeBtn.addEventListener('click',()=>panel.classList.add('d-none'));
    backBtn.addEventListener('click',showList);
    conversations.addEventListener('click',e=>{const b=e.target.closest('.sup-conv');if(b)openChat(b.dataset.url)});
    textarea.addEventListener('input',function(){this.style.height='auto';this.style.height=Math.min(this.scrollHeight,100)+'px'});
    textarea.addEventListener('keydown',e=>{if(e.key==='Enter'&&!e.shiftKey){e.preventDefault();if(textarea.value.trim()||image.files.length)form.requestSubmit()}});
    form.addEventListener('submit',async e=>{e.preventDefault();if(!textarea.value.trim()&&!image.files.length)return;send.disabled=true;const old=send.innerHTML;send.innerHTML="<i class='bx bx-loader-alt bx-spin'></i>";try{const r=await fetch(form.action,{method:'POST',body:new FormData(form),headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}});const d=await r.json();if(!r.ok||!d.success||!d.chat)throw new Error(d.message||'Erro ao enviar.');textarea.value='';textarea.style.height='auto';image.value='';active=d.chat;messages.innerHTML=active.mensagens.map(bubbleHtml).join('');messages.scrollTop=messages.scrollHeight;}catch(err){if(window.swal)swal('Erro',err.message,'error');else alert(err.message)}finally{send.disabled=false;send.innerHTML=old;textarea.focus()}});

    loadList();
    setInterval(()=>{if(panel.classList.contains('d-none'))loadList();else if(!chatView.classList.contains('d-none')&&active)refreshChat();else loadList()},5000);
});
</script>
@endif