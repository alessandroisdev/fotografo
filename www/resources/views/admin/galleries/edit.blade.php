@extends('layouts.admin')

@section('title', 'Editar Galeria')
@section('header_title', 'Configurações da Galeria')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-5">
                <form action="{{ route('admin.galleries.update', $gallery->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Selecione o Cliente Dono do Álbum</label>
                        <select name="user_id" class="form-select bg-light" required>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ $gallery->user_id == $client->id ? 'selected' : '' }}>{{ $client->name }} ({{ $client->email }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Nome da Galeria</label>
                        <input type="text" name="name" class="form-control bg-light" value="{{ $gallery->name }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Descrição ou Observação (Opcional)</label>
                        <textarea name="description" class="form-control bg-light" rows="3">{{ $gallery->description }}</textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-primary">Status de Publicação</label>
                        <select name="status" class="form-select bg-light border-primary" required>
                            <option value="draft" {{ $gallery->status === \App\Enums\GalleryStatusEnum::DRAFT ? 'selected' : '' }}>Rascunho (Oculto)</option>
                            <option value="published" {{ $gallery->status === \App\Enums\GalleryStatusEnum::PUBLISHED ? 'selected' : '' }}>Publicado (Acessível ao Cliente)</option>
                            <option value="archived" {{ $gallery->status === \App\Enums\GalleryStatusEnum::ARCHIVED ? 'selected' : '' }}>Arquivado</option>
                        </select>
                        <div class="form-text"><i class="bi bi-info-circle"></i> O cliente só verá a galeria no Painel e Site se ela estiver Publicada.</div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.galleries.show', $gallery->id) }}" class="btn btn-outline-secondary rounded-pill px-4">Voltar</a>
                        <button type="submit" class="btn btn-primary fw-bold rounded-pill px-4 shadow">Atualizar Permissões</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
