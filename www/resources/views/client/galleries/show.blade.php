@extends('layouts.public')

@section('title', $gallery->name)

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css" />
<style>
    /* Styling for the selectable gallery items */
    .gallery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 15px;
    }
    .photo-item {
        position: relative;
        overflow: hidden;
        border-radius: 8px;
    }
    .photo-item img {
        transition: all 0.3s ease;
        border: 3px solid transparent;
        border-radius: 8px;
        aspect-ratio: 1;
        object-fit: cover;
    }
    .photo-item.selected img {
        border-color: var(--bs-secondary);
        transform: scale(0.97);
        filter: brightness(0.8);
    }
    
    /* Overlay actions */
    .action-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        padding: 40px 15px 15px;
        background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        opacity: 0;
        transition: opacity 0.3s ease;
        pointer-events: none;
    }
    
    .photo-item:hover .action-overlay,
    .photo-item.selected .action-overlay {
        opacity: 1;
    }

    .btn-select {
        pointer-events: auto;
        cursor: pointer;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(255,255,255,0.2);
        backdrop-filter: blur(5px);
        display: flex;
        justify-content: center;
        align-items: center;
        border: 2px solid white;
        transition: all 0.2s ease;
    }
    .btn-select i {
        font-size: 1.2rem;
        color: white;
    }
    
    .btn-zoom {
        pointer-events: auto;
        cursor: pointer;
        color: white;
        text-shadow: 0 2px 4px rgba(0,0,0,0.5);
    }
    .btn-zoom:hover {
        color: var(--bs-secondary);
    }

    .photo-item.selected .btn-select {
        background: var(--bs-secondary);
        border-color: var(--bs-secondary);
    }
    
    .check-icon {
        display: none;
    }
    .photo-item.selected .check-icon {
        display: inline-block;
    }
    .photo-item.selected .circle-icon {
        display: none;
    }
    
    /* Cart Floating Bar */
    .selection-cart {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        background: rgba(15, 23, 42, 0.95);
        backdrop-filter: blur(10px);
        border-top: 1px solid rgba(255,255,255,0.1);
        padding: 15px 0;
        transform: translateY(100%);
        transition: transform 0.4s ease;
        z-index: 1050;
    }
    .selection-cart.active {
        transform: translateY(0);
    }
</style>

<div class="row pt-3 text-white mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <a href="{{ route('client.dashboard') }}" class="btn btn-sm btn-outline-light rounded-pill mb-3"><i class="bi bi-arrow-left"></i> Voltar</a>
            <h2 class="fw-bold">{{ $gallery->name }}</h2>
            <p class="text-white-50">{{ $gallery->description ?? 'Utilize o botão de seleção em cada foto para montar o seu pacote ideal.' }}</p>
        </div>
        <div class="text-end">
            <span class="badge bg-primary fs-6">{{ $gallery->photos->count() }} Fotos Disponíveis</span>
        </div>
    </div>
</div>

<div class="gallery-grid" id="selection-grid">
    @forelse($gallery->photos as $photo)
        <div class="photo-item" id="photo-card-{{ $photo->id }}" data-id="{{ $photo->id }}">
            
            <!-- Thumbnail exibida no grid ligada ao Watermark da lightboox -->
            <a href="{{ Storage::url($photo->watermark_path) }}" class="glightbox" data-gallery="client-gallery">
                <img src="{{ Storage::url($photo->thumbnail_path) }}" alt="{{ $photo->original_name }}" class="img-fluid w-100 shadow-sm" loading="lazy">
            </a>
            
            <!-- Controles de sobreposição -->
            <div class="action-overlay">
                <a href="{{ Storage::url($photo->watermark_path) }}" class="glightbox btn-zoom" data-gallery="client-gallery-zoom">
                    <i class="bi bi-arrows-fullscreen fs-4"></i>
                </a>
                
                <div class="btn-select shadow" onclick="toggleSelection('{{ $photo->id }}')">
                    <i class="bi bi-circle circle-icon"></i>
                    <i class="bi bi-check-lg check-icon"></i>
                </div>
            </div>

        </div>
    @empty
        <div class="col-12 text-center py-5">
             <span class="text-muted">As imagens ainda estão sendo renderizadas, recarregue a página em alguns instantes.</span>
        </div>
    @endforelse
</div>

<!-- Barra de Ações Fixa (Oculta até selecionar) -->
<div class="selection-cart" id="cartBar">
    <div class="container d-flex justify-content-between align-items-center">
        <div class="text-white">
            <h5 class="mb-0 fw-bold"><i class="bi bi-images text-secondary"></i> <span id="selectedCount">0</span> Fotos Selecionadas</h5>
        </div>
        <div>
            <!-- Formulário Injetor oculto -->
            <form id="checkoutForm" action="{{ route('client.checkout.review', $gallery->uuid) }}" method="POST" class="d-none">
                @csrf
                <input type="hidden" name="photo_ids" id="photo_ids_input" value="">
            </form>
            <button class="btn btn-secondary px-4 fw-bold rounded-pill" onclick="checkout()"><i class="bi bi-cart-check"></i> Fechar Pacote</button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/gh/mcstudios/glightbox/dist/js/glightbox.min.js"></script>
<script>
    // Inicializar o Lightbox
    const lightbox = GLightbox({
        selector: '.glightbox',
        touchNavigation: true,
        loop: true,
    });

    let selectedPhotos = new Set();
    const cartBar = document.getElementById('cartBar');
    const countDisplay = document.getElementById('selectedCount');
    const checkoutForm = document.getElementById('checkoutForm');
    const idsInput = document.getElementById('photo_ids_input');

    function toggleSelection(id) {
        const element = document.getElementById('photo-card-' + id);
        
        if(selectedPhotos.has(id)) {
            selectedPhotos.delete(id);
            element.classList.remove('selected');
        } else {
            selectedPhotos.add(id);
            element.classList.add('selected');
        }
        
        updateCartUi();
    }

    function updateCartUi() {
        countDisplay.textContent = selectedPhotos.size;
        
        if(selectedPhotos.size > 0) {
            cartBar.classList.add('active');
        } else {
            cartBar.classList.remove('active');
        }
    }
    
    function checkout() {
        let photosArray = Array.from(selectedPhotos);
        idsInput.value = photosArray.join(',');
        checkoutForm.submit();
    }
</script>
@endsection
