
document.addEventListener('DOMContentLoaded', function () {
    var calendarEl = document.getElementById('calendar');
    getAgendamentos((agendamentos) => {
        var calendar = new FullCalendar.Calendar(calendarEl, {
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
            },
            locale: 'pt-br',
            initialView: 'dayGridMonth',
            navLinks: true,
            selectable: true,
            nowIndicator: true,
            dayMaxEvents: true,
            editable: true,
            selectable: true,
            businessHours: true,
            dayMaxEvents: true,
            events: agendamentos
        });
        calendar.render();

    });
})


var SELECIONADOS = []

$(function () {
    setTimeout(() => {
        $('.btn_cat').first().trigger('click')
    }, 100)
})

$('.sub_cat').addClass('d-none')

function selectCat(id) {
    $('.btn_cat').removeClass('btn-primary')
    $('.btn_cat_' + id).addClass('btn-primary')
    $('.sub_cat').addClass('d-none')
    $('.sub_cat_' + id).removeClass('d-none')
    //$('.cat_' + id).addClass('btn-primary')
}

function selectServico(id, valor, tempo_servico) {
    let js = {
        id: id,
        valor: valor,
        tempo_servico: tempo_servico
    }
    console.log(tempo_servico)
    let temp = SELECIONADOS.find((x) => {
        return x.id == id
    })
    if (!temp) {
        SELECIONADOS.push(js)
    } else {
        SELECIONADOS = SELECIONADOS.filter((x) => {
            return x.id != id

        })
    }
    setTimeout(() => {
        percorreArray()
    }, 20);
}

function percorreArray() {
    $('.input_hidden').html('')
    $('.sub_cat').removeClass('btn-success')
    let total = 0
    let tempo_servico = 0
    SELECIONADOS.map((x) => {
        total += parseFloat(x.valor)
        tempo_servico += parseFloat(x.tempo_servico)
        $('.servico_' + x.id).addClass('btn-success')
        let inp = '<input type="hidden" name="servico_id[]" value="' + x.id + '">'
        $('.input_hidden').append(inp)
        console.log(inp)
    })
    $('.total').text(convertFloatToMoeda(total))
    $('#total').val(total)
    $('.tempo_servico').text(tempo_servico + " min")
}

$('.modal .select2').each(function () {
    let id = $(this).prop('id')
    if (id == 'inp-uf') {
        $(this).select2({
            dropdownParent: $(this).parent(),
            theme: 'bootstrap4',
        });
    }

    if (id == 'inp-cidade_id') {
        $(this).select2({
            minimumInputLength: 2,
            language: "pt-BR",
            placeholder: "Digite para buscar a cidade",
            width: "100%",
            theme: 'bootstrap4',
            dropdownParent: $(this).parent(),
            ajax: {
                cache: true,
                url: path_url + 'api/buscaCidades',
                dataType: "json",
                data: function (params) {
                    console.clear()
                    var query = {
                        pesquisa: params.term,
                    };
                    return query;
                },
                processResults: function (response) {
                    console.log("response", response)
                    var results = [];
                    $.each(response, function (i, v) {
                        var o = {};
                        o.id = v.id;

                        o.text = v.nome + "(" + v.uf + ")";
                        o.value = v.id;
                        results.push(o);
                    });
                    return {
                        results: results
                    };
                }
            }
        });
    }
})

$('#btn-store-cliente').click(() => {
    let valid = validaCamposModal()
    if (valid.length > 0) {
        let msg = ""
        valid.map((x) => {
            msg += x + "\n"
        })
        swal("Ops, erro no formulário", msg, "error")
    } else {
        console.log("salvando...")
        let data = {}
        $(".modal input, .modal select").each(function () {
            let indice = $(this).attr('id')
            indice = indice.substring(4, indice.length)
            data[indice] = $(this).val()
        });
        data['empresa_id'] = $('#empresa_id').val()
        console.log(data)
        $.post(path_url + 'api/cliente/store', data)
            .done((success) => {
                console.log("success", success)
                swal("Sucesso", "Cliente cadastrado!", "success")
                    .then(() => {
                        var newOption = new Option(success.razao_social, success.id, false, false);
                        $('#inp-cliente_id').append(newOption).trigger('change');
                        $('#modal-cliente').modal('hide')
                    })
            }).fail((err) => {
                console.log(err)
                swal("Ops", "Algo deu errado ao salvar cliente!", "error")
            })
    }
})



function getAgendamentos(eventos) {
    let empresa_id = $('#empresa_id').val()
    $.get(path_url + 'api/agendamentos/all', {
            empresa_id: empresa_id
        })
        .done((success) => {

            console.log(success)
            eventos(success)
        })
        .fail((err) => {
            console.log(err)
            eventos([])
        })
}
