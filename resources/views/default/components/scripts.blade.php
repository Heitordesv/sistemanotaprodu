<script type="text/javascript">
    var casas_decimais = 2;
    casas_decimais = {{$casasDecimais}}
    let prot = window.location.protocol;
    let host = window.location.host;
    const path_url = prot + "//" + host + "/";
    const hash = '{{session('user_logged')['hash_empresa']}}';
    window.produtoWebEndpoints = {
        pesquisa: @json(route('produtos.consulta.pesquisa')),
        find: @json(url('/produtos/consulta/find')),
        findByBarcode: @json(route('produtos.consulta.findByBarcode')),
        findByBarcodeReference: @json(route('produtos.consulta.findByBarcodeReference')),
        findProdRemessa: @json(route('produtos.consulta.findProdRemessa'))
    };
    // Compatibilidade com o seletor legado das telas administrativas.
    // O PDV possui layout e endpoints proprios e nao usa esta configuracao.
    window.pdvProdutoEndpoints = window.produtoWebEndpoints;

</script>

<script src="/assets/js/bootstrap.bundle.min.js"></script>
<!--plugins-->
<script src="/assets/js/jquery.min.js"></script>
<script type="text/javascript">
    const ajaxHeaders = {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    };

    // Mantido para os endpoints externos legados. As ações fiscais abertas
    // pelas telas web resolvem a empresa diretamente da sessão autenticada.
    if (typeof hash !== 'undefined' && hash) {
        ajaxHeaders['X-Empresa-Hash'] = hash;
    }

    $.ajaxSetup({ headers: ajaxHeaders });
</script>
<script src="/assets/js/simplebar.min.js"></script>
<script src="/assets/js/metisMenu.min.js"></script>
<script src="/assets/js/perfect-scrollbar.js"></script>
<script src="/assets/vectormap/jquery-jvectormap-2.0.2.min.js"></script>
<script src="/assets/vectormap/jquery-jvectormap-world-mill-en.js"></script>
<script src="/assets/js/toastr.min.js"></script>

<script src="/assets/js/highcharts.js"></script>
<script src="/assets/js/exporting.js"></script>
<script src="/assets/js/variable-pie.js"></script>
<!-- <script src="/assets/js/export-data.js"></script> -->
<script src="/assets/js/accessibility.js"></script>
<script src="/assets/js/apexcharts.min.js"></script>
<!-- <script src="/assets/js/index2.js"></script> -->
<script src='https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js'></script>
<script type="text/javascript" src="/js/jquery.mask.min.js"></script>
<script src="/assets/js/select2.min.js"></script>
<script src="/assets/js/app.js"></script>
<script src="/js/main.js"></script>
<script src="/js/theme.js"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/35.1.0/classic/ckeditor.js"></script>
@if(!session('user_contador'))
<script type="text/javascript" src="/js/noticacao.js?v=3"></script>
@endif

@yield('js')

<script type="text/javascript">
    toastr.options = {
        "progressBar": true, 
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "10000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    }

    @if(session()->has('flash_sucesso'))
    toastr.success('{{ session()->get('flash_sucesso') }}');
    @endif

    @if(session()->has('flash_erro'))
    toastr.error('{{ session()->get('flash_erro') }}');
    @endif

    @if(session()->has('flash_warning'))
    toastr.warning('{{ session()->get('flash_warning') }}');
    @endif

</script>

@if(session('user_contador'))
@include('modals._empresa_contador', ['not_submit' => true])
@endif

@if($audio == 1)
@if(session()->has('flash_sucesso'))
<script type="text/javascript">
    var audio = new Audio('/audio/success.mp3');
    audio.addEventListener('canplaythrough', function() {
        audio.play();
    });
</script>
@endif

@if(session()->has('flash_erro'))
<script type="text/javascript">
    var audio = new Audio('/audio/error.mp3');
    audio.addEventListener('canplaythrough', function() {
        audio.play();
    });
</script>
@endif

@if(session()->has('flash_warning'))
<script type="text/javascript">
    var audio = new Audio('/audio/error.mp3');
    audio.addEventListener('canplaythrough', function() {
        audio.play();
    });
</script>
@endif
@endif
