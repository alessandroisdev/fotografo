@extends('layouts.public')

@section('content')
<div class="row pt-5">
    <div class="col-12 text-center text-white mb-5">
        <h1 class="display-4 fw-bold">Capturando Momentos Inesquecíveis</h1>
        <p class="lead">Nossas galerias mais recentes preparadas especialmente para você.</p>
    </div>
</div>

<div class="row g-4">
    <!-- Demo Masonry/Grid Itens -->
    <div class="col-md-4">
        <div class="gallery-card p-2 text-center text-white">
            <div class="bg-dark rounded p-5 d-flex align-items-center justify-content-center" style="min-height: 250px;">
                <i class="bi bi-image fs-1 text-secondary"></i>
            </div>
            <h4 class="mt-3">Casamento Silva</h4>
            <span class="badge bg-primary text-white">120 fotos</span>
        </div>
    </div>
    <div class="col-md-4">
        <div class="gallery-card p-2 text-center text-white">
            <div class="bg-dark rounded p-5 d-flex align-items-center justify-content-center" style="min-height: 250px;">
                <i class="bi bi-image fs-1 text-secondary"></i>
            </div>
            <h4 class="mt-3">Ensaio Corporativo</h4>
            <span class="badge bg-primary text-white">45 fotos</span>
        </div>
    </div>
    <div class="col-md-4">
        <div class="gallery-card p-2 text-center text-white">
            <div class="bg-dark rounded p-5 d-flex align-items-center justify-content-center" style="min-height: 250px;">
                <i class="bi bi-image fs-1 text-secondary"></i>
            </div>
            <h4 class="mt-3">Aniversário 15 anos</h4>
            <span class="badge bg-primary text-white">200 fotos</span>
        </div>
    </div>
</div>
@endsection
