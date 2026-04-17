@extends('layouts.public')

@section('title', 'Área do Cliente')

@section('content')
<div class="row pt-4 text-center text-white mb-5">
    <div class="col-12">
        <h2 class="display-5 fw-bold">Olá, {{ explode(' ', $client->name)[0] }}!</h2>
        <p class="lead">Aqui estão os ensaios separados para sua curadoria.</p>
    </div>
</div>

<div class="row g-4">
    @forelse($galleries as $gallery)
    <div class="col-md-4">
        <div class="gallery-card p-3 text-center text-white text-decoration-none d-block">
            <div class="bg-dark rounded d-flex align-items-center justify-content-center mb-3" style="min-height: 200px; background: url('{{ $gallery->cover_path ? Storage::url($gallery->cover_path) : '' }}') no-repeat center/cover;">
                @if(!$gallery->cover_path)
                    <i class="bi bi-camera fs-1 text-secondary"></i>
                @endif
            </div>
            <h4 class="mb-1">{{ $gallery->name }}</h4>
            <p class="small text-muted mb-3">{{ $gallery->created_at->format('d M, Y') }}</p>
            <span class="badge bg-primary text-white mb-3">{{ $gallery->photos_count }} Fotos Processadas</span>
            
            <a href="{{ route('client.galleries.show', $gallery->uuid) }}" class="btn btn-outline-secondary w-100 rounded-pill">Acessar Álbum</a>
        </div>
    </div>
    @empty
    <div class="col-12 text-center text-white-50 py-5">
        <i class="bi bi-folder-x display-1 mb-3"></i>
        <h4 class="fw-normal">Nenhum álbum publicado no momento.</h4>
        <p>Quando as suas fotos estiverem prontas, elas aparecerão magicamente aqui.</p>
    </div>
    @endforelse
</div>
@endsection
