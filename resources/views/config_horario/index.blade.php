@extends('default.layout', ['title' => 'Atualizar Horários'])

@section('content')
<div class="page-content">
    <div class="card">
        <div class="card-body p-4">

            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto"></div>
            </div>


            <!-- Formulário de atualização de horários -->
            <form action="{{ route('config_horario.update', ['id_empresa' => $empresa->id_empresa]) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="indent_title_in mb-3">
                    <i class="fa fa-clock-o" aria-hidden="true"></i>
                    <h3>Horários de funcionamento</h3>
                    <p>Defina o seu horário de atendimento para que seus clientes saibam quando seus serviços estarão disponíveis.</p>
                </div>

         

                    <div class="panel-body">

                        <!-- Segunda-feira -->
                        <div class="wrapper_indent mb-4">
                            <label class="form-check-label d-block mb-2">
                                <strong>SEGUNDA-FEIRA</strong>
                            </label>

                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="config_segunda" name="config_segunda" value="true"
                                    @if(old('config_segunda', $empresa->config_segunda) == 'true' || $empresa->config_segunda === true) checked @endif>
                                <label class="form-check-label" for="config_segunda">
                                    <strong style="color:#85c99d;">PERÍODO DA MANHÃ</strong>
                                </label>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="segunda_manha_de">de:</label>
                                        <input required type="time" name="segunda_manha_de" id="segunda_manha_de"
                                            value="{{ old('segunda_manha_de', $empresa->segunda_manha_de ?? '00:00') }}"
                                            class="form-control"/>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="segunda_manha_ate">até:</label>
                                        <input required type="time" name="segunda_manha_ate" id="segunda_manha_ate"
                                            value="{{ old('segunda_manha_ate', $empresa->segunda_manha_ate ?? '00:00') }}"
                                            class="form-control"/>
                                    </div>
                                </div>
                            </div>

                            <div class="form-check form-switch mt-3 mb-2">
                                <input class="form-check-input" type="checkbox" id="config_segundaa" name="config_segundaa" value="true"
                                    @if(old('config_segundaa', $empresa->config_segundaa) == 'true' || $empresa->config_segundaa === true) checked @endif>
                                <label class="form-check-label" for="config_segundaa">
                                    <strong style="color:#85c99d;">PERÍODO DA TARDE</strong>
                                </label>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="segunda_tarde_de">de:</label>
                                        <input required type="time" name="segunda_tarde_de" id="segunda_tarde_de"
                                            value="{{ old('segunda_tarde_de', $empresa->segunda_tarde_de ?? '00:00') }}"
                                            class="form-control"/>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="segunda_tarde_ate">até:</label>
                                        <input required type="time" name="segunda_tarde_ate" id="segunda_tarde_ate"
                                            value="{{ old('segunda_tarde_ate', $empresa->segunda_tarde_ate ?? '00:00') }}"
                                            class="form-control"/>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Repetir para outros dias da semana (Terça-feira, Quarta-feira, etc.) -->
                        <div class="wrapper_indent mb-4">
                            <label class="form-check-label d-block mb-2">
                                <strong>TERÇA-FEIRA</strong>
                            </label>

                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="config_terca" name="config_terca" value="true"
                                    @if(old('config_terca', $empresa->config_terca) == 'true' || $empresa->config_terca === true) checked @endif>
                                <label class="form-check-label" for="config_terca">
                                    <strong style="color:#85c99d;">PERÍODO DA MANHÃ</strong>
                                </label>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="terca_manha_de">de:</label>
                                        <input required type="time" name="terca_manha_de" id="terca_manha_de"
                                            value="{{ old('terca_manha_de', $empresa->terca_manha_de ?? '00:00') }}"
                                            class="form-control"/>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="terca_manha_ate">até:</label>
                                        <input required type="time" name="terca_manha_ate" id="terca_manha_ate"
                                            value="{{ old('terca_manha_ate', $empresa->terca_manha_ate ?? '00:00') }}"
                                            class="form-control"/>
                                    </div>
                                </div>
                            </div>

                            <div class="form-check form-switch mt-3 mb-2">
                                <input class="form-check-input" type="checkbox" id="config_tercaa" name="config_tercaa" value="true"
                                    @if(old('config_tercaa', $empresa->config_tercaa) == 'true' || $empresa->config_tercaa === true) checked @endif>
                                <label class="form-check-label" for="config_tercaa">
                                    <strong style="color:#85c99d;">PERÍODO DA TARDE</strong>
                                </label>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="terca_tarde_de">de:</label>
                                        <input required type="time" name="terca_tarde_de" id="terca_tarde_de"
                                            value="{{ old('terca_tarde_de', $empresa->terca_tarde_de ?? '00:00') }}"
                                            class="form-control"/>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="terca_tarde_ate">até:</label>
                                        <input required type="time" name="terca_tarde_ate" id="terca_tarde_ate"
                                            value="{{ old('terca_tarde_ate', $empresa->terca_tarde_ate ?? '00:00') }}"
                                            class="form-control"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Quarta-feira -->
<div class="wrapper_indent mb-4">
    <label class="form-check-label d-block mb-2">
        <strong>QUARTA-FEIRA</strong>
    </label>

    <div class="form-check form-switch mb-2">
        <input class="form-check-input" type="checkbox" id="config_quarta" name="config_quarta" value="true"
            @if(old('config_quarta', $empresa->config_quarta) == 'true' || $empresa->config_quarta === true) checked @endif>
        <label class="form-check-label" for="config_quarta">
            <strong style="color:#85c99d;">PERÍODO DA MANHÃ</strong>
        </label>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label for="quarta_manha_de">de:</label>
                <input required type="time" name="quarta_manha_de" id="quarta_manha_de"
                    value="{{ old('quarta_manha_de', $empresa->quarta_manha_de ?? '00:00') }}"
                    class="form-control"/>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="quarta_manha_ate">até:</label>
                <input required type="time" name="quarta_manha_ate" id="quarta_manha_ate"
                    value="{{ old('quarta_manha_ate', $empresa->quarta_manha_ate ?? '00:00') }}"
                    class="form-control"/>
            </div>
        </div>
    </div>

    <div class="form-check form-switch mt-3 mb-2">
        <input class="form-check-input" type="checkbox" id="config_quartaa" name="config_quartaa" value="true"
            @if(old('config_quartaa', $empresa->config_quartaa) == 'true' || $empresa->config_quartaa === true) checked @endif>
        <label class="form-check-label" for="config_quartaa">
            <strong style="color:#85c99d;">PERÍODO DA TARDE</strong>
        </label>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label for="quarta_tarde_de">de:</label>
                <input required type="time" name="quarta_tarde_de" id="quarta_tarde_de"
                    value="{{ old('quarta_tarde_de', $empresa->quarta_tarde_de ?? '00:00') }}"
                    class="form-control"/>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="quarta_tarde_ate">até:</label>
                <input required type="time" name="quarta_tarde_ate" id="quarta_tarde_ate"
                    value="{{ old('quarta_tarde_ate', $empresa->quarta_tarde_ate ?? '00:00') }}"
                    class="form-control"/>
            </div>
        </div>
    </div>
</div>

<!-- Quinta-feira -->
<div class="wrapper_indent mb-4">
    <label class="form-check-label d-block mb-2">
        <strong>QUINTA-FEIRA</strong>
    </label>

    <div class="form-check form-switch mb-2">
        <input class="form-check-input" type="checkbox" id="config_quinta" name="config_quinta" value="true"
            @if(old('config_quinta', $empresa->config_quinta) == 'true' || $empresa->config_quinta === true) checked @endif>
        <label class="form-check-label" for="config_quinta">
            <strong style="color:#85c99d;">PERÍODO DA MANHÃ</strong>
        </label>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label for="quinta_manha_de">de:</label>
                <input required type="time" name="quinta_manha_de" id="quinta_manha_de"
                    value="{{ old('quinta_manha_de', $empresa->quinta_manha_de ?? '00:00') }}"
                    class="form-control"/>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="quinta_manha_ate">até:</label>
                <input required type="time" name="quinta_manha_ate" id="quinta_manha_ate"
                    value="{{ old('quinta_manha_ate', $empresa->quinta_manha_ate ?? '00:00') }}"
                    class="form-control"/>
            </div>
        </div>
    </div>

    <div class="form-check form-switch mt-3 mb-2">
        <input class="form-check-input" type="checkbox" id="config_quintaa" name="config_quintaa" value="true"
            @if(old('config_quintaa', $empresa->config_quintaa) == 'true' || $empresa->config_quintaa === true) checked @endif>
        <label class="form-check-label" for="config_quintaa">
            <strong style="color:#85c99d;">PERÍODO DA TARDE</strong>
        </label>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label for="quinta_tarde_de">de:</label>
                <input required type="time" name="quinta_tarde_de" id="quinta_tarde_de"
                    value="{{ old('quinta_tarde_de', $empresa->quinta_tarde_de ?? '00:00') }}"
                    class="form-control"/>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="quinta_tarde_ate">até:</label>
                <input required type="time" name="quinta_tarde_ate" id="quinta_tarde_ate"
                    value="{{ old('quinta_tarde_ate', $empresa->quinta_tarde_ate ?? '00:00') }}"
                    class="form-control"/>
            </div>
        </div>
    </div>
</div>


<!-- Sexta-feira -->
<div class="wrapper_indent mb-4">
    <label class="form-check-label d-block mb-2">
        <strong>SEXTA-FEIRA</strong>
    </label>

    <div class="form-check form-switch mb-2">
        <input class="form-check-input" type="checkbox" id="config_sexta" name="config_sexta" value="true"
            @if(old('config_sexta', $empresa->config_sexta) == 'true' || $empresa->config_sexta === true) checked @endif>
        <label class="form-check-label" for="config_sexta">
            <strong style="color:#85c99d;">PERÍODO DA MANHÃ</strong>
        </label>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label for="sexta_manha_de">de:</label>
                <input required type="time" name="sexta_manha_de" id="sexta_manha_de"
                    value="{{ old('sexta_manha_de', $empresa->sexta_manha_de ?? '00:00') }}"
                    class="form-control"/>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="sexta_manha_ate">até:</label>
                <input required type="time" name="sexta_manha_ate" id="sexta_manha_ate"
                    value="{{ old('sexta_manha_ate', $empresa->sexta_manha_ate ?? '00:00') }}"
                    class="form-control"/>
            </div>
        </div>
    </div>

    <div class="form-check form-switch mt-3 mb-2">
        <input class="form-check-input" type="checkbox" id="config_sextaa" name="config_sextaa" value="true"
            @if(old('config_sextaa', $empresa->config_sextaa) == 'true' || $empresa->config_sextaa === true) checked @endif>
        <label class="form-check-label" for="config_sextaa">
            <strong style="color:#85c99d;">PERÍODO DA TARDE</strong>
        </label>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label for="sexta_tarde_de">de:</label>
                <input required type="time" name="sexta_tarde_de" id="sexta_tarde_de"
                    value="{{ old('sexta_tarde_de', $empresa->sexta_tarde_de ?? '00:00') }}"
                    class="form-control"/>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="sexta_tarde_ate">até:</label>
                <input required type="time" name="sexta_tarde_ate" id="sexta_tarde_ate"
                    value="{{ old('sexta_tarde_ate', $empresa->sexta_tarde_ate ?? '00:00') }}"
                    class="form-control"/>
            </div>
        </div>
    </div>
</div>

<!-- Sábado -->
<div class="wrapper_indent mb-4">
    <label class="form-check-label d-block mb-2">
        <strong>SÁBADO</strong>
    </label>

    <div class="form-check form-switch mb-2">
        <input class="form-check-input" type="checkbox" id="config_sabado" name="config_sabado" value="true"
            @if(old('config_sabado', $empresa->config_sabado) == 'true' || $empresa->config_sabado === true) checked @endif>
        <label class="form-check-label" for="config_sabado">
            <strong style="color:#85c99d;">PERÍODO DA MANHÃ</strong>
        </label>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label for="sabado_manha_de">de:</label>
                <input required type="time" name="sabado_manha_de" id="sabado_manha_de"
                    value="{{ old('sabado_manha_de', $empresa->sabado_manha_de ?? '00:00') }}"
                    class="form-control"/>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="sabado_manha_ate">até:</label>
                <input required type="time" name="sabado_manha_ate" id="sabado_manha_ate"
                    value="{{ old('sabado_manha_ate', $empresa->sabado_manha_ate ?? '00:00') }}"
                    class="form-control"/>
            </div>
        </div>
    </div>

    <div class="form-check form-switch mt-3 mb-2">
        <input class="form-check-input" type="checkbox" id="config_sabadoo" name="config_sabadoo" value="true"
            @if(old('config_sabadoa', $empresa->config_sabadoo) == 'true' || $empresa->config_sabadoo === true) checked @endif>
        <label class="form-check-label" for="config_sabadoa">
            <strong style="color:#85c99d;">PERÍODO DA TARDE</strong>
        </label>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label for="sabado_tarde_de">de:</label>
                <input required type="time" name="sabado_tarde_de" id="sabado_tarde_de"
                    value="{{ old('sabado_tarde_de', $empresa->sabado_tarde_de ?? '00:00') }}"
                    class="form-control"/>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="sabado_tarde_ate">até:</label>
                <input required type="time" name="sabado_tarde_ate" id="sabado_tarde_ate"
                    value="{{ old('sabado_tarde_ate', $empresa->sabado_tarde_ate ?? '00:00') }}"
                    class="form-control"/>
            </div>
        </div>
    </div>
</div> 
<!-- Domingo -->
<div class="wrapper_indent mb-4">
    <label class="form-check-label d-block mb-2">
        <strong>DOMINGO</strong>
    </label>

    <div class="form-check form-switch mb-2">
        <input class="form-check-input" type="checkbox" id="config_domingo" name="config_domingo" value="true"
            @if(old('config_domingo', $empresa->config_domingo) == 'true' || $empresa->config_domingo === true) checked @endif>
        <label class="form-check-label" for="config_domingo">
            <strong style="color:#85c99d;">PERÍODO DA MANHÃ</strong>
        </label>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label for="domingo_manha_de">de:</label>
                <input required type="time" name="domingo_manha_de" id="domingo_manha_de"
                    value="{{ old('domingo_manha_de', $empresa->domingo_manha_de ?? '00:00') }}"
                    class="form-control"/>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="domingo_manha_ate">até:</label>
                <input required type="time" name="domingo_manha_ate" id="domingo_manha_ate"
                    value="{{ old('domingo_manha_ate', $empresa->domingo_manha_ate ?? '00:00') }}"
                    class="form-control"/>
            </div>
        </div>
    </div>

    <div class="form-check form-switch mt-3 mb-2">
        <input class="form-check-input" type="checkbox" id="config_domingoo" name="config_domingoo" value="true"
            @if(old('config_domingoa', $empresa->config_domingoo) == 'true' || $empresa->config_domingoo === true) checked @endif>
        <label class="form-check-label" for="config_domingoa">
            <strong style="color:#85c99d;">PERÍODO DA TARDE</strong>
        </label>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label for="domingo_tarde_de">de:</label>
                <input required type="time" name="domingo_tarde_de" id="domingo_tarde_de"
                    value="{{ old('domingo_tarde_de', $empresa->domingo_tarde_de ?? '00:00') }}"
                    class="form-control"/>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="domingo_tarde_ate">até:</label>
                <input required type="time" name="domingo_tarde_ate" id="domingo_tarde_ate"
                    value="{{ old('domingo_tarde_ate', $empresa->domingo_tarde_ate ?? '00:00') }}"
                    class="form-control"/>
            </div>
        </div>
    </div>
</div>

                    </div>
                </div>

                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-save me-2"></i>Atualizar Horários
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
