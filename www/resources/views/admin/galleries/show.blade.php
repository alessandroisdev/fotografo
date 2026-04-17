@extends('layouts.admin')

@section('title', 'Gerenciar Galeria')
@section('header_title', $gallery->name)

@section('content')
<!-- Include Dropzone CSS natively for this view -->
<link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" type="text/css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css" />

<style>
    .dropzone {
        border: 2px dashed var(--bs-primary);
        border-radius: 15px;
        background: rgba(10, 88, 202, 0.03);
        padding: 50px;
        text-align: center;
        transition: all 0.3s ease;
    }
    .dropzone:hover {
        background: rgba(10, 88, 202, 0.08);
    }
</style>

<div class="row g-4">
    <!-- Dropzone Area -->
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4">
                <h5 class="fw-bold text-primary mb-1"><i class="bi bi-cloud-arrow-up text-secondary me-2"></i> Upload Massivo de Imagens</h5>
                <p class="text-muted small mb-4">Arraste os arquivos JPEG, PNG ou WEBP originais. O servidor suporta até 500MB por bloco. O Processador Redis fará a marca d'água no plano de fundo automaticamente.</p>
                
                <form action="{{ route('admin.galleries.photos.store', $gallery->id) }}" class="dropzone" id="massUploader">
                    @csrf
                    <div class="dz-message needsclick">
                        <i class="bi bi-cloud-upload fs-1 text-primary"></i><br/>
                        <span class="fs-5 fw-bold">Solte os Arquivos Aqui</span><br/>
                        <span class="text-muted">ou clique para procurar no PC.</span>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Gallery Context -->
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 bg-primary text-white h-100">
            <div class="card-body p-4">
                <span class="badge bg-warning text-dark mb-2 rounded-pill">{{ strtoupper($gallery->status->value) }}</span>
                <h4 class="fw-bold mb-3">{{ $gallery->name }}</h4>
                <p class="opacity-75"><i class="bi bi-person me-2"></i> {{ $gallery->user->name ?? 'Cliente Desconhecido' }}</p>
                <p class="opacity-75"><i class="bi bi-calendar me-2"></i> {{ $gallery->created_at->format('d/m/Y') }}</p>
                <hr class="border-white opacity-25">
                <div class="d-grid gap-2">
                    <a href="{{ route('client.galleries.show', $gallery->uuid) }}" target="_blank" class="btn btn-light fw-bold"><i class="bi bi-eye"></i> Visualizar Link Público</a>
                    <a href="{{ route('admin.galleries.edit', $gallery->id) }}" class="btn btn-outline-light fw-bold"><i class="bi bi-gear"></i> Editar Permissões</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <h5 class="fw-bold text-primary mb-4"><i class="bi bi-columns-gap text-secondary me-2"></i> Fotos Processadas & Pendentes</h5>
                
                <div class="row g-3" id="photo-grid">
                    @forelse($gallery->photos as $photo)
                        @if($photo->status == 'processing')
                            <div class="col-6 col-md-3 col-lg-2 text-center photo-processing" id="photo-{{ $photo->id }}" data-id="{{ $photo->id }}">
                                <div class="bg-light rounded p-4 border text-muted d-flex align-items-center justify-content-center flex-column" style="height:150px;">
                                    <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
                                    <small class="fw-bold">Processando...</small>
                                </div>
                            </div>
                        @else
                            <div class="col-6 col-md-3 col-lg-2 text-center" id="photo-{{ $photo->id }}">
                                <div class="position-relative photo-admin-item">
                                    <a href="{{ Storage::url($photo->watermark_path) }}" class="glightbox" data-gallery="admin-gallery">
                                        <img src="{{ Storage::url($photo->thumbnail_path) }}" class="img-fluid rounded border shadow-sm" alt="Thumbnail" style="height:150px; object-fit:cover; width:100%;">
                                    </a>
                                    <span class="badge bg-success position-absolute bottom-0 start-0 m-1 shadow"><i class="bi bi-check-circle"></i></span>
                                    
                                    <form action="{{ route('admin.galleries.photos.destroy', [$gallery->id, $photo->id]) }}" method="POST" class="position-absolute top-0 end-0 m-1" data-confirm="Deseja excluir esta foto definitivamente do servidor?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger rounded-circle shadow opacity-75 hover-opacity-100" style="padding: 0.25rem 0.4rem;">
                                            <i class="bi bi-trash3-fill"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endif
                    @empty
                        <div class="col-12 text-center py-4 text-muted">
                            Nenhuma foto enviada ainda.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
<script src="https://cdn.jsdelivr.net/gh/mcstudios/glightbox/dist/js/glightbox.min.js"></script>
<script>
    // Inicializar o Lightbox
    const lightbox = GLightbox({
        selector: '.glightbox',
        touchNavigation: true,
        loop: true,
    });

    Dropzone.options.massUploader = {
        maxFilesize: 500, // MB format
        acceptedFiles: 'image/jpeg,image/png,image/jpg,image/webp,.cr2,.cr3,.dng,.arw,.nef',
        dictDefaultMessage: "Arraste os arquivos JPEG, WEBP ou RAW (.CR2, .CR3) aqui",
        success: function(file, response) {
            console.log("Uploaded successfully: ", response);
            // Poderíamos injetar a caixinha de spinner dinamicamente no DOM usando response.photo_id para visual instantâneo!
            let grid = document.getElementById('photo-grid');
            let emptyMsg = grid.querySelector('.py-4');
            if(emptyMsg) emptyMsg.remove();
            
            grid.insertAdjacentHTML('afterbegin', `
                <div class="col-6 col-md-3 col-lg-2 text-center photo-processing" id="photo-${response.photo_id}" data-id="${response.photo_id}">
                    <div class="bg-light rounded p-4 border text-muted d-flex align-items-center justify-content-center flex-column" style="height:150px;">
                        <div class="spinner-grow spinner-grow-sm text-primary mb-2" role="status"></div>
                        <small class="fw-bold text-primary">NaFila...</small>
                    </div>
                </div>
            `);
        }
    };

    // AJAX Polling Simples para buscar dados do Redis Worker transparentemente
    document.addEventListener('DOMContentLoaded', function() {
        setInterval(function() {
            let processingNodes = document.querySelectorAll('.photo-processing');
            if (processingNodes.length === 0) return;

            let ids = Array.from(processingNodes).map(node => node.dataset.id);
            let params = new URLSearchParams();
            ids.forEach(id => params.append('ids[]', id));

            fetch(`{{ route('admin.galleries.photos.poll', $gallery->id) }}?`+params.toString())
            .then(res => res.json())
            .then(data => {
                data.forEach(photo => {
                    let node = document.getElementById(`photo-${photo.id}`);
                    if(node) {
                        node.innerHTML = `
                            <div class="position-relative">
                                <img src="${photo.thumbnail_url}" class="img-fluid rounded border" alt="Thumbnail" style="height:150px; object-fit:cover; width:100%;">
                                <span class="badge bg-success position-absolute bottom-0 start-0 m-1"><i class="bi bi-check-circle"></i></span>
                            </div>
                        `;
                        node.classList.remove('photo-processing');
                    }
                });
            });
        }, 3000); // 3 seconds scan
    });
</script>
@endsection
