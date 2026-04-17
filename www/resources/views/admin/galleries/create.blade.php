@extends('layouts.admin')

@section('title', 'Nova Galeria')
@section('header_title', 'Criar Álbum')

@section('content')
<div class="card border-0 shadow-sm rounded-4 max-w-xl mx-auto">
    <div class="card-body p-5">
        <h5 class="mb-4 text-primary fw-bold"><i class="bi bi-hdd-network text-secondary me-2"></i> Configurações Iniciais do Álbum</h5>
        
        <form action="{{ route('admin.galleries.store') }}" method="POST">
            @csrf
            
            <div class="mb-4">
                <label for="user_id" class="form-label fw-bold">Cliente Vinculado</label>
                <select class="form-select" id="user_id" name="user_id" required>
                    <option value="">Selecione o titular desse ensaio...</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->name }} ({{ $client->email }})</option>
                    @endforeach
                </select>
                <div class="form-text">As notas fiscais e permissões privadas serão atreladas a este dono.</div>
            </div>

            <div class="mb-3">
                <label for="name" class="form-label fw-bold">Título da Galeria</label>
                <input type="text" class="form-control" id="name" name="name" placeholder="Ex: Casamento João e Maria" required>
            </div>
            
            <div class="mb-4">
                <label for="description" class="form-label fw-bold">Descrição Livre</label>
                <textarea class="form-control" id="description" name="description" rows="3" placeholder="Informações fofas ou técnicas sobre o ensaio..."></textarea>
            </div>

            <div class="alert alert-info border-0 bg-light text-primary">
                <i class="bi bi-info-circle-fill me-2"></i> Você irá fazer os uploads das fotos na página seguinte!
            </div>

            <div class="mb-4 form-check form-switch fs-5 mt-4">
                <input class="form-check-input" type="checkbox" role="switch" id="is_public_switch" name="is_public" value="1">
                <label class="form-check-label fw-bold text-dark" for="is_public_switch">Exibir no Portfólio Público?</label>
                <div class="form-text text-muted fs-6"><i class="bi bi-globe me-1"></i> Se marcado, esta galeria será listada no site base para qualquer visitante verificar seu trabalho.</div>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <a href="{{ route('admin.galleries.index') }}" class="btn btn-outline-secondary me-2">Cancelar</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-arrow-right"></i> Criar & Prosseguir</button>
            </div>
        </form>
    </div>
</div>
@endsection
