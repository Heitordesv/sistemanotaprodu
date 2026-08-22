@extends('default.layout', ['title' => 'Vídeos de Ajuda'])

<script src="https://cdn.tailwindcss.com"></script>
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

@php
    if (!function_exists('getYoutubeId')) {
        function getYoutubeId($url) {
            preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);
            return $match[1] ?? null;
        }
    }
@endphp

<style>
.video-card {
    transition: all .25s ease;
}

.video-card:hover {
    transform: translateY(-6px);
}

.video-card:hover .thumbnail-img {
    transform: scale(1.08);
}

.video-card:hover .play-overlay {
    opacity: 1;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.fade-in {
    animation: fadeIn .25s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

@section('content')

<div class="p-6 bg-gradient-to-b from-gray-50 to-gray-100 min-h-screen">

    <!-- HEADER -->
    <div class="mb-8 border-b pb-5">
        <h1 class="text-3xl font-black text-gray-900 flex items-center gap-2">
            <i class='bx bxs-help-circle text-red-600'></i>
            Central de Vídeos
        </h1>
        <p class="text-gray-500 mt-1">Tutoriais rápidos para te ajudar no sistema</p>
    </div>

    <!-- SEARCH PREMIUM -->
    <div class="mb-8 relative max-w-xl">
        <i class='bx bx-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xl'></i>

        <input 
            type="text" 
            id="searchVideo"
            placeholder="Buscar vídeo..."
            class="w-full pl-12 pr-4 py-3 rounded-2xl border bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500 transition"
        >
    </div>

    <!-- SEM RESULTADOS -->
    <div id="noResults" class="hidden text-center py-16">
        <i class='bx bx-search-alt text-6xl text-gray-300'></i>
        <p class="text-gray-500 mt-3">Nenhum vídeo encontrado</p>
    </div>

    <!-- GRID -->
    <div id="videoGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">

        @forelse($player as $item)

            @php
                $videoId = getYoutubeId($item->url_video);
                $thumbnailUrl = $videoId 
                    ? "https://img.youtube.com/vi/{$videoId}/hqdefault.jpg"
                    : asset('images/thumbnail-padrao.jpg');
            @endphp

            <div class="video-card group cursor-pointer bg-white rounded-2xl shadow-sm overflow-hidden"
                 data-search="{{ strtolower($item->url_sistema) }}"
                 data-bs-toggle="modal" 
                 data-bs-target="#modalVideo" 
                 data-id="{{ $item->id }}" 
                 data-url_sistema="{{ $item->url_sistema }}" 
                 data-url_video="{{ $item->url_video }}">

                <!-- THUMB -->
                <div class="relative aspect-video bg-black overflow-hidden">

                    <img src="{{ $thumbnailUrl }}"
                         class="thumbnail-img w-full h-full object-cover transition duration-300">

                    <div class="play-overlay absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 transition">
                        <i class='bx bx-play text-5xl text-white'></i>
                    </div>

                    <span class="absolute bottom-2 right-2 text-[10px] bg-black/70 text-white px-2 py-1 rounded-full">
                        TUTORIAL
                    </span>

                </div>

                <!-- INFO -->
                <div class="p-4">
                    <h3 class="font-semibold text-gray-900 line-clamp-2 group-hover:text-red-600 transition">
                        {{ $item->url_sistema }}
                    </h3>

                    <div class="flex items-center justify-between mt-3 text-xs text-gray-400">
                        <span>ID #{{ $item->id }}</span>
                        <i class='bx bx-play-circle text-lg text-gray-300 group-hover:text-red-500 transition'></i>
                    </div>
                </div>

            </div>

        @empty
            <div class="col-span-full text-center py-20">
                <i class='bx bx-video-off text-6xl text-gray-300'></i>
                <p class="text-gray-500 mt-3">Nenhum vídeo disponível</p>
            </div>
        @endforelse

    </div>
</div>

<!-- MODAL -->
<div class="modal fade" id="modalVideo" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content rounded-3xl overflow-hidden">

            <div class="modal-header border-0 p-5">
                <div>
                    <h5 id="videoUrlSistema" class="text-lg font-bold">Carregando...</h5>
                    <p class="text-xs text-gray-400">ID: #<span id="modalVideoId"></span></p>
                </div>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-5">
                <div class="aspect-video bg-black rounded-2xl overflow-hidden relative">
                    <iframe id="videoIframe"
                            class="absolute inset-0 w-full h-full"
                            frameborder="0"
                            allowfullscreen>
                    </iframe>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const input = document.getElementById('searchVideo');
    const cards = document.querySelectorAll('.video-card');
    const noResults = document.getElementById('noResults');
    const grid = document.getElementById('videoGrid');

    input.addEventListener('input', function () {
        const value = this.value.toLowerCase();
        let visible = 0;

        cards.forEach(card => {
            const text = card.getAttribute('data-search');

            if (text.includes(value)) {
                card.style.display = 'block';
                card.classList.add('fade-in');
                visible++;
            } else {
                card.style.display = 'none';
            }
        });

        noResults.style.display = visible === 0 ? 'block' : 'none';
        grid.style.display = visible === 0 ? 'none' : 'grid';
    });

    const modal = document.getElementById('modalVideo');
    const iframe = document.getElementById('videoIframe');
    const title = document.getElementById('videoUrlSistema');
    const idEl = document.getElementById('modalVideoId');

    modal.addEventListener('show.bs.modal', function (event) {
        const btn = event.relatedTarget;

        let url = btn.getAttribute('data-url_video');
        const id = btn.getAttribute('data-id');
        const name = btn.getAttribute('data-url_sistema');

        title.textContent = name;
        idEl.textContent = id;

        if (url.includes('watch?v=')) {
            url = url.replace('watch?v=', 'embed/');
        } else if (url.includes('youtu.be/')) {
            url = `https://www.youtube.com/embed/${url.split('/').pop()}`;
        }

        iframe.src = url + '?autoplay=1&rel=0';
    });

    modal.addEventListener('hide.bs.modal', function () {
        iframe.src = '';
    });

});
</script>

@endsection