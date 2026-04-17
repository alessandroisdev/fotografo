@extends('layouts.public')

@section('title', $gallery->name)

@section('content')
<style>
    /* Styling for the selectable gallery items */
    .photo-item {
        position: relative;
        cursor: pointer;
    }
    .photo-item img {
        transition: all 0.3s ease;
        border: 3px solid transparent;
        border-radius: 8px;
    }
    .photo-item.selected img {
        border-color: var(--bs-secondary);
        transform: scale(0.97);
        filter: brightness(0.8);
    }
    .check-icon {
        position: absolute;
        top: 15px;
        right: 15px;
        font-size: 1.5rem;
        color: var(--bs-secondary);
        opacity: 0;
        transform: scale(0.5);
        transition: all 0.3s ease;
    }
    .photo-item.selected .check-icon {
        opacity: 1;
        transform: scale(1);
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
            <p class="text-white-50">{{ $gallery->description ?? 'Selecione abaixo as suas fotos favoritas tocando nelas.' }}</p>
        </div>
        <div class="text-end">
            <span class="badge bg-primary fs-6">{{ $gallery->photos->count() }} Fotos Disponíveis</span>
        </div>
    </div>
</div>

<div class="gallery-grid" id="selection-grid">
    @forelse($gallery->photos as $photo)
        <div class="photo-item" data-id="{{ $photo->id }}" onclick="toggleSelection(this)">
            <!-- Carrega a versão com Marca d'Água construída pelo Worker -->
            <img src="{{ Storage::url($photo->watermark_path) }}" alt="{{ $photo->original_name }}" class="img-fluid w-100 shadow-sm" loading="lazy">
            <i class="bi bi-check-circle-fill check-icon"></i>
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

<script>
    let selectedPhotos = new Set();
    const cartBar = document.getElementById('cartBar');
    const countDisplay = document.getElementById('selectedCount');
    const checkoutForm = document.getElementById('checkoutForm');
    const idsInput = document.getElementById('photo_ids_input');

    function toggleSelection(element) {
        const id = element.getAttribute('data-id');
        
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
