$(function() {
    $('[data-bs-toggle="popover"]').popover();
});

var LOJAID = null

$("body").on("change", "#inp-empresa_id", function () {
    liberaProdutos()
})

function liberaProdutos() {
    LOJAID = $('#inp-empresa_id').val()
    console.log(LOJAID)
    if (LOJAID != '') {
        $('.d-produto').removeClass('d-none')
    } else {
        $('.d-produto').addClass('d-none')
    }
}