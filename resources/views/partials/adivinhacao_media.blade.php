{{-- Coluna do arquivo --}}
<div class="col-12 col-md-5 d-flex justify-content-center align-items-center">
    @php
        $file = $adivinhacao->imagem;
        $fileUrl = str_starts_with($file, 'http') ? $file : asset('/storage/' . $file);
        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    @endphp

    @if(in_array($extension, ['jpg','jpeg','png','gif','webp','avif']))
        <img
            src="{{ $fileUrl }}"
            class="img-fluid rounded-4 w-100"
            alt="Arquivo da adivinhação"
            loading="lazy"
            style="aspect-ratio: 4/3; object-fit: contain; width: 100%; height: auto;"
            width="600"
            height="450"
            fetchpriority="high"
        >
    @elseif(in_array($extension, ['mp4','webm','ogg']))
        <video
            class="rounded-4 w-100"
            controls
            preload="metadata"
            style="aspect-ratio: 4/3; object-fit: contain; width: 100%; height: auto;"
        >
            <source src="{{ $fileUrl }}" type="video/{{ $extension }}">
            Seu navegador não suporta vídeo.
        </video>
    @elseif(in_array($extension, ['mp3','wav','ogg']))
        <audio
            class="w-100"
            controls
            preload="metadata"
        >
            <source src="{{ $fileUrl }}" type="audio/{{ $extension }}">
            Seu navegador não suporta áudio.
        </audio>
    @elseif($extension === 'pdf')
        <iframe
            src="{{ $fileUrl }}"
            class="w-100 rounded-4"
            style="aspect-ratio: 4/3; border:none;"
            loading="lazy"
        ></iframe>
    @else
        <div class="alert alert-warning text-center w-100">
            Arquivo não suportado
            <br>
            <a href="{{ $fileUrl }}" target="_blank" class="btn btn-sm btn-primary mt-2">Download</a>
        </div>
    @endif
</div>
