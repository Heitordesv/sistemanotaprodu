<div class="modal fade" id="modal-ref-nfe" aria-modal="true" role="dialog" style="overflow:scroll;" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Referência NFe</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body row">
                <div class="row">
                    <table class="table table-dynamic table-chave">
                        <thead>
                            <tr>
                                <th>Chave</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($item) && sizeof($item->referencias) > 0)
                            @foreach ($item->referencias as $i)
                            <tr class="dynamic-form">
                                <td>
                                    <input type="tel" id="chave_nfe" class="form-control class-required" name="chave_nfe[]" value="{{$i->chave}}">
                                </td>
                                <td>
                                    <button type="button" class="btn btn-danger btn-sm btn-remove-tr">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                            @else
                            <tr class="dynamic-form">
                                <td>
                                    <input type="tel" id="chave_nfe" class="form-control class-required" name="chave_nfe[]">
                                </td>
                                <td>
                                    <button type="button" class="btn btn-danger btn-sm btn-remove-tr">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="row">
                    <div class="col-12">
                        <button type="button" class="btn btn-success btn-add-tr">
                            Adicionar chave
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button data-bs-dismiss="modal" type="button" aria-label="Close" class="btn btn-primary px-5">OK</button>
            </div>

        </div>
    </div>
</div>
