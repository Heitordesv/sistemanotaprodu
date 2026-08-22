var MODELOS = []

$('body').on('change', '.modelo', function () {
    etiqueta()
});


$(function () {
    MODELOS = JSON.parse($('#padroes').val())
})

function etiqueta() {
    $('#obs').html('')
    let id = $('#modelo').val()
    let p = MODELOS.filter((x) => {
        return x.id == id
    })
    p = p[0]
    console.log(p)
    $('#inp-largura').val(p.largura)
    $('#inp-altura').val(p.altura)
    $('#inp-etiquestas_por_linha').val(p.etiquestas_por_linha)
    $('#inp-distancia_etiquetas_lateral').val(p.distancia_etiquetas_lateral)
    $('#inp-distancia_etiquetas_topo').val(p.distancia_etiquetas_topo)
    $('#inp-quantidade_etiquetas').val(p.quantidade_etiquetas)
    $('#inp-tamanho_fonte').val(p.tamanho_fonte)
    $('#inp-tamanho_codigo_barras').val(p.tamanho_codigo_barras)
    $('#inp-nome_produto').val(p.nome_produto).change()
    $('#inp-nome_empresa').val(p.nome_empresa).change()
    $('#inp-codigo_produto').val(p.codigo_produto).change()
    $('#inp-valor_produto').val(p.valor_produto).change()
    $('#inp-codigo_barras_numerico').val(p.codigo_barras_numerico).change()
}
