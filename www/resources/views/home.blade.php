@extends('layouts.public')

@section('content')
<div class="row pt-5">
    <div class="col-12 text-center text-white mb-5">
        <h1 class="display-4 fw-bold">Capturando Momentos Inesquecíveis</h1>
        <p class="lead">Nossas galerias mais recentes preparadas especialmente para você.</p>
    </div>
</div>

<div class="row g-4">
    @forelse($galleries as $gallery)
    <div class="col-md-4">
        <a href="{{ route('portfolio.show', $gallery->uuid) }}" class="text-decoration-none">
            <div class="gallery-card p-2 text-center text-white h-100 d-flex flex-column hover-effect">
                @php
                    $cover = $gallery->photos->first();
                    $bgImage = $cover && $cover->watermark_path ? asset('storage/' . $cover->watermark_path) : null;
                @endphp
                
                <div class="bg-dark rounded p-5 d-flex align-items-center justify-content-center shadow-lg cover-container" style="min-height: 250px; background-image: url('{{ $bgImage }}'); background-size: cover; background-position: center; border: 1px solid rgba(255,255,255,0.1);">
                    @if(!$bgImage)
                        <i class="bi bi-image fs-1 text-secondary"></i>
                    @endif
                </div>
                <h4 class="mt-3 fw-bold text-truncate text-white">{{ $gallery->name }}</h4>
            <div class="mt-auto">
                <span class="badge bg-primary text-white"><i class="bi bi-camera me-1"></i> {{ $gallery->photos_count }} fotos exclusivas</span>
            </div>
        </a>
    </div>
    @empty
    <div class="col-12 text-center py-5 opacity-50">
        <i class="bi bi-camera-fill display-1 mb-3 d-block"></i>
        <h4 class="fw-light">Nenhum ensaio publicado no momento.</h4>
    </div>
    @endforelse
</div>
@endsection
