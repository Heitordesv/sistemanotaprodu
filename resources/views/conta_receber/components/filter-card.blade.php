<div class="card p-3 shadow-sm mb-4">
    <form method="GET" class="row g-2 align-items-end">

        <div class="col-md-2">
            <label>Dia</label>
            <select name="dia" class="form-control">
                <option value="">Todos</option>
                @for($i=1;$i<=31;$i++)
                    <option value="{{ $i }}" {{ request('dia') == $i ? 'selected' : '' }}>
                        {{ $i }}
                    </option>
                @endfor
            </select>
        </div>

        <div class="col-md-2">
            <label>Mês</label>
            <select name="mes" class="form-control">
                @for($i=1;$i<=12;$i++)
                    <option value="{{ $i }}" {{ ($mes ?? now()->month) == $i ? 'selected' : '' }}>
                        {{ DateTime::createFromFormat('!m',$i)->format('M') }}
                    </option>
                @endfor
            </select>
        </div>

        <div class="col-md-2">
            <label>Ano</label>
            <input type="number" name="ano" class="form-control" value="{{ $ano ?? now()->year }}">
        </div>

        <div class="col-md-3">
            <label>Grupo</label>
            <select name="grupo_id" class="form-control">
                <option value="">Todos</option>
                @foreach($grupos ?? [] as $g)
                    <option value="{{ $g->id }}" {{ request('grupo_id') == $g->id ? 'selected' : '' }}>
                        {{ $g->nome }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <button class="btn btn-primary w-100">
                Filtrar
            </button>
        </div>

    </form>
</div>