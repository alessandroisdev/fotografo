@extends('layouts.public')

@section('content')
<!-- Include Dropzone CSS natively for this view -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css" />

<div class="row pt-5">
    <div class="col-12 text-center text-white mb-5">
        <span class="badge bg-primary px-3 py-2 rounded-pill shadow mb-3"><i class="bi bi-person-fill"></i> Álbum Exclusivo de {{ $gallery->user->name ?? 'Acesso Livre' }}</span>
        <h1 class="display-4 fw-bold mb-0 shadow-sm">{{ $gallery->name }}</h1>
        <p class="lead opacity-75 mt-3"><i class="bi bi-camera me-2"></i>{{ $gallery->photos->count() }} Momentos Registrados</p>
        @if($gallery->description)
            <div class="text-white opacity-50 fst-italic mt-2 mx-auto" style="max-width:600px;">
                "{{ $gallery->description }}"
            </div>
        @endif
    </div>
</div>

<div class="row g-4 pb-5">
    @forelse($gallery->photos as $photo)
        <div class="col-6 col-md-4 col-lg-3 text-center">
            <div class="position-relative overflow-hidden rounded shadow-lg gallery-item">
                <a href="{{ Storage::url($photo->watermark_path) }}" class="glightbox" data-gallery="portfolio-gallery">
                    <img src="{{ Storage::url($photo->thumbnail_path) }}" class="img-fluid" alt="Foto {{ $loop->iteration }}" style="height:250px; object-fit:cover; width:100%; transition: transform 0.3s ease;">
                </a>
            </div>
        </div>
    @empty
        <div class="col-12 text-center py-5 opacity-50 text-white">
            <i class="bi bi-images display-1 mb-3 d-block"></i>
            <h4 class="fw-light">A galeria não possui fotos públicas no momento.</h4>
        </div>
    @endforelse
</div>

<div class="text-center pb-5">
    <a href="{{ url('/') }}" class="btn btn-outline-light px-4 py-2 rounded-pill"><i class="bi bi-arrow-left me-2"></i> Voltar ao Portfólio Principal</a>
</div>

<style>
    .gallery-item:hover img {
        transform: scale(1.05);
    }
</style>

<script src="https://cdn.jsdelivr.net/gh/mcstudios/glightbox/dist/js/glightbox.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const lightbox = GLightbox({
            selector: '.glightbox',
            touchNavigation: true,
            loop: true,
        });
    });
</script>
@endsection
