<div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-end p-6 bg-white rounded-2xl shadow-sm border border-gray-100">
<script src="https://cdn.tailwindcss.com"></script>

    {{-- Nome --}}
    <div class="md:col-span-4">
        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1 ml-1">Nome</label>
        {!! Form::text('nome', null)
            ->required()
            ->attrs([
                'class' => 'block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm text-sm py-3 px-4 border focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-200 outline-none',
                'placeholder' => 'Ex: Promoção de Verão'
            ]) 
        !!}
    </div>

    {{-- Desconto --}}
    <div class="md:col-span-2">
        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1 ml-1">Desconto (%)</label>
        {!! Form::text('desconto', null)
            ->attrs([
                'type' => 'number',
                'step' => '0.01',
                'min' => '0',
                'max' => '100',
                'class' => 'block w-full rounded-xl border-gray-200 bg-gray-50 shadow-sm text-sm py-3 px-4 border focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-200 outline-none',
                'placeholder' => '0.00'
            ]) 
        !!}
    </div>

    {{-- Ativar --}}
    <div class="md:col-span-3 pb-3">
        <label class="relative inline-flex items-center cursor-pointer group">
            <input type="checkbox" 
                   name="desconto_ativo" 
                   value="1"
                   {{ isset($item) && $item->desconto_ativo ? 'checked' : '' }}
                   class="sr-only peer">
            
            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
            
            <span class="ml-3 text-sm font-medium text-gray-700 group-hover:text-indigo-600 transition-colors">
                Ativar desconto
            </span>
        </label>
    </div>

    {{-- Botão --}}
    <div class="md:col-span-3">
        <button type="submit"
                class="w-full px-6 py-3 bg-indigo-600 hover:bg-indigo-700 active:transform active:scale-[0.98] text-white rounded-xl font-bold shadow-lg shadow-indigo-200 transition-all duration-200 flex items-center justify-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
            Salvar Alterações
        </button>
    </div>

</div>