/**
 * Gerenciador de Devoluções e NF-e
 * Organizado via Module Pattern para evitar conflitos de escopo.
 */
const DevolucaoApp = {
    // Configurações globais
    config: {
        baseUrl: typeof path_url !== 'undefined' ? path_url : '/',
        empresaId: $("#empresa_id").val(),
        minMotivoChars: 15
    },

    // Inicialização de eventos e UI
    init: function() {
        this.bindEvents();
        this.calcTotal();
        this.validLineSelect();
        console.log("DevolucaoApp inicializado com sucesso.");
    },

    bindEvents: function() {
        const self = this;

        // --- Cálculos Automáticos ---
        $('body').on('blur', '.qtd, .valor_unit', function() {
            self.updateRowSubtotal($(this).closest('tr'));
        });

        $('body').on('blur', '.subtotal-item', () => self.calcTotal());

        // --- Controle de Seleção (Checkbox Único) ---
        $('.checkbox').on('change', function() {
            if (this.checked) {
                $('.checkbox').not(this).prop('checked', false);
            }
            self.validLineSelect();
        });

        // --- Gerenciamento de Linhas ---
        $(".table-itens").on('click', '.btn-delete-row', function() {
            $(this).closest('tr').fadeOut(300, function() {
                $(this).remove();
                swal("Sucesso", "Item removido!", "success");
                self.calcTotal();
            });
        });

        // --- Ações de Transmissão e Consulta ---
        $('#btn-enviar').click(() => self.handleSefazAction('transmitir', '#btn-enviar'));
        $('#btn-consultar').click(() => self.handleSefazAction('consultar', '#btn-consultar'));
        $('#btn-corrige-send').click(() => self.handleEventAction('corrigir', '#btn-corrige-send', '#inp-motivo-corrige'));
        $('#btn-cancelar-send').click(() => self.handleEventAction('cancelar', '#btn-cancelar-send', '#inp-motivo-cancela'));

        // --- Impressão e Downloads ---
        this.bindPrintEvents();

        // --- Abertura de Modais ---
        $('#btn-corrigir, #btn-cancelar').click(function() {
            const action = $(this).attr('id').split('-')[1]; // corrige ou cancelar
            self.getCheckedElement($el => {
                $('.numero_nfe').text($el.data('numero_nfe'));
                $(`#modal-${action}`).modal('show');
            });
        });
    },

    // --- Lógica de Interface e Cálculos ---
    
    updateRowSubtotal: function($row) {
        const qtd = convertMoedaToFloat($row.find('.qtd').val()) || 0;
        const vlUnit = convertMoedaToFloat($row.find('.valor_unit').val()) || 0;
        const subtotal = qtd * vlUnit;

        $row.find('.subtotal-item').val(convertFloatToMoeda(subtotal));
        this.calcTotal();
    },

    calcTotal: function() {
        let total = 0;
        $(".subtotal-item").each(function() {
            total += convertMoedaToFloat($(this).val());
        });
        
        // UI Sync com pequeno delay para fluidez
        setTimeout(() => {
            $('#soma-itens').html(`R$ ${convertFloatToMoeda(total)}`);
            $('#valor_devolucao').val(total.toFixed(2));
        }, 50);
    },

    validLineSelect: function() {
        const $selected = $('.checkbox:checked');
        const $btns = $('.btn-action');
        
        $btns.prop('disabled', true);

        if ($selected.length > 0) {
            const status = $selected.data('status');
            
            const gruposAcoes = {
                'novo':      ['#btn-enviar', '#btn-danfe-temp', '#btn-xml-temp'],
                'rejeitado': ['#btn-enviar', '#btn-danfe-temp', '#btn-xml-temp'],
                'aprovado':  ['#btn-imprimir', '#btn-imprimir-cce', '#btn-consultar', '#btn-cancelar', '#btn-corrigir', '#btn-baixar-xml'],
                'processando': ['#btn-imprimir', '#btn-consultar'],
                'cancelado': ['#btn-imprimir-cancela']
            };

            if (gruposAcoes[status]) {
                $(gruposAcoes[status].join(',')).prop('disabled', false);
            }
        }
    },

    // --- Comunicação com a API ---

    handleSefazAction: function(endpoint, btnId) {
        this.getChecked(id => {
            const $btn = $(btnId);
            const originalContent = $btn.html();

            this.setLoading($btn, true);

            $.post(`${this.config.baseUrl}api/devolucao/${endpoint}`, {
                id: id,
                empresa_id: this.config.empresaId
            })
            .done(resp => {
                if (resp.autorizado) {
                    this.exibeRetornoSefaz("NF-e Autorizada", resp, "success");
                } else if (resp.processando) {
                    swal("Processando", "Aguardando fila da SEFAZ...", "info").then(() => location.reload());
                } else {
                    this.exibeRetornoSefaz("Resposta SEFAZ", resp, "warning");
                }
            })
            .fail(err => this.exibeRetornoSefaz("Erro Crítico", err.responseJSON || "Erro de conexão", "error"))
            .always(() => this.setLoading($btn, false, originalContent));
        });
    },

    handleEventAction: function(endpoint, btnId, inputMotivo) {
        const motivo = $(inputMotivo).val();
        if (motivo.length < this.config.minMotivoChars) {
            return swal("Alerta", `O motivo deve ter pelo menos ${this.config.minMotivoChars} caracteres.`, "warning");
        }

        this.getChecked(id => {
            const $btn = $(btnId);
            this.setLoading($btn, true);

            $.post(`${this.config.baseUrl}api/devolucao/${endpoint}`, {
                id: id,
                empresa_id: this.config.empresaId,
                motivo: motivo
            })
            .done(resp => {
                const info = resp.retEvento?.infEvento || resp;
                this.exibeRetornoSefaz("Evento Registrado", info, "success");
            })
            .fail(err => this.exibeRetornoSefaz("Erro no Evento", err.responseJSON, "error"))
            .always(() => this.setLoading($btn, false));
        });
    },

    // --- Utilitários de UI ---

    exibeRetornoSefaz: function(titulo, dados, tipo) {
        const info = dados.protNFe?.infProt || dados.infProt || dados;
        const corStatus = ['100', '135'].includes(String(info.cStat)) ? '#28a745' : '#dc3545';
        
        const html = `
            <div style="text-align: left; font-size: 14px; border-top: 1px solid #eee; padding-top: 10px;">
                <p><strong>Status:</strong> <span style="color: ${corStatus}">${info.cStat || '---'}</span></p>
                <p><strong>Motivo:</strong> ${info.xMotivo || 'Sem resposta'}</p>
                ${info.chNFe ? `<p><strong>Chave:</strong> <br><small>${info.chNFe}</small></p>` : ''}
            </div>`;

        swal({
            title: titulo,
            content: { element: "div", attributes: { innerHTML: html } },
            icon: tipo,
        }).then(() => {
            if (['100', '101', '135'].includes(String(info.cStat))) location.reload();
        });
    },

    setLoading: function($btn, active, originalText = "") {
        if (active) {
            $btn.prop('disabled', true).data('original', $btn.html())
                .html('<i class="fa fa-spinner fa-spin"></i> Processando...');
        } else {
            $btn.prop('disabled', false).html(originalText || $btn.data('original'));
        }
    },

    getChecked: function(callback) {
        const id = $('.checkbox:checked').val();
        if (id) callback(id);
        else swal("Alerta", "Selecione um registro primeiro!", "warning");
    },

    getCheckedElement: function(callback) {
        const $el = $('.checkbox:checked');
        if ($el.length > 0) callback($el);
        else swal("Alerta", "Selecione um registro primeiro!", "warning");
    },

    bindPrintEvents: function() {
        const prints = {
            '#btn-imprimir': 'devolucao/imprimir/',
            '#btn-imprimir-cce': 'devolucao/imprimir-cce/',
            '#btn-imprimir-cancela': 'devolucao/imprimir-cancela/',
            '#btn-xml-temp': 'devolucao/xml-temp/',
            '#btn-danfe-temp': 'devolucao/danfe-temp/',
            '#btn-baixar-xml': 'nfe/baixar-xml/'
        };

        Object.keys(prints).forEach(selector => {
            $(selector).click(() => {
                this.getChecked(id => window.open(this.config.baseUrl + prints[selector] + id, "_blank"));
            });
        });
    }
};

// Inicialização ao carregar o DOM
$(document).ready(() => DevolucaoApp.init());