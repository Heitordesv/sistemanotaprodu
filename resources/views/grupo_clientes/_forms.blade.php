<script src="https://cdn.tailwindcss.com"></script>
<div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-end">
    <div class="md:col-span-4">
        {!! Form::text('nome', 'Nome')
            ->required()
            ->attrs([
                'class' => 'mt-1 block w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2.5 transition-all',
                'placeholder' => 'Digite o nome do grupo...'
            ]) 
        !!}
    </div>

    <div class="md:col-span-8">
        <button type="submit" 
                class="inline-flex items-center px-8 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md shadow-indigo-100 active:scale-95 gap-2">
            <i class="bx bx-save text-lg"></i>
            Salvar Registro
        </button>
    </div>
</div>