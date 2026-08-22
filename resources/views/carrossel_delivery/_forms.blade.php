<div class="row m-2">
    {{-- <input type="hidden" name="config_id" value="{{ $item->id }}"> --}}
    @if (!isset($not_submit))
    <div id="image-preview" class="col-4">
        <label for="image-upload" id="image-label">Selecione a imagem</label>
        <input type="file" name="image" id="image-upload" class="_image-upload" accept="image/*" />
        @isset($item)
        @if ($item->imagem)
        <img src="/uploads/lojaDelivery/{{ $item->imagem }}" class="img-default">
        @else
        <img src="/imgs/no_image.png" class="img-default">
        @endif
        @else
        <img src="/imgs/no_image.png" class="img-default">
        @endif
    </div>
    @endif
    <div class="col-4">
        <button style="margin-top: 80px; margin-left: 20px" type="submit" class="btn btn-info px-5">Salvar</button>
    </div>
</div>
<hr>
<h5 class="m-3" style="color: red">As imagens serão exibidas respeitando a ordem abaixo!</h5>
<div class="table-responsive mt-3">
    <table class="table mb-0">
        <thead>
            <tr>
                <th>Imagem</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $item)
            <tr>
                <td>
                    <span>
                        <img style="width: 220px; height: 200px;" src="/uploads/carrossel_delivery/{{ $item->path }}">
                    </span>
                </td>
                <td>
                    <span>
                        <div>
                            <label class="m-3">
                                <input onclick="alterarStatus({{$item->id}})" @if($item->status) checked @endif value="true" name="status" type="checkbox">
                            </label>
                        </div>
                    </span>
                </td>
                <td>
                    <span class="">
                        <a class="btn btn-danger btn-sm mt-3" onclick='swal("Atenção!", "Deseja remover esta imagem?", "warning").then((sim) => {if(sim){ location.href="/carrosselDelivery/delete/{{ $item->id }}" }else{return false} })' href="#!">
                            <i class="bx bx-trash"></i>
                        </a>

                        @if($item->status)
                        @if(!$loop->first)
                        <a class="btn btn-success btn-sm mt-3" href="/carrosselDelivery/up/{{$item->id}}">
                            <i class="bx bx-upvote"></i>
                        </a>
                        @endif
                        @if(!$loop->last)
                        <a class="btn btn-dark btn-sm mt-3" href="/carrosselDelivery/down/{{$item->id}}">
                            <i class="bx bx-downvote"></i>
                        </a>
                        @endif
                        @endif
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@section('js')

<script type="text/javascript">
    function alterarStatus(id) {
        $.get(path_url + 'carrosselDelivery/alteraStatus/' + id)
            .done((success) => {
                console.log(success)
            })
            .fail((err) => {
                console.log(err)
            })
    }
</script>
<script type="text/javascript" src="/assets/js/jquery.uploadPreview.min.js"></script>
@endsection
