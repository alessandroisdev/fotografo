@extends('layouts.admin')

@section('title', 'Editar Pacote')
@section('header_title', 'Editar Pacote de Venda')

@section('content')
<div class="row">
    <div class="col-12 col-md-8 offset-md-2">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <form action="{{ route('admin.packages.update', $package->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nome do Pacote</label>
                            <input type="text" name="name" class="form-control bg-light" required placeholder="Ex: Casamento Prata" value="{{ $package->name }}">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Quantidade de Fotos Inclusas</label>
                            <input type="number" name="included_photos_count" class="form-control bg-light" required placeholder="Ex: 50" min="1" value="{{ $package->included_photos_count }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">Descrição</label>
                            <textarea name="description" class="form-control bg-light" rows="3" required placeholder="O que este pacote oferece?">{{ $package->description }}</textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-success">Preço Fixo do Pacote (R$)</label>
                            <input type="number" step="0.01" name="price" class="form-control bg-light" required placeholder="Ex: 1500.00" min="0" value="{{ $package->price }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-warning">Preço por Foto Extra (R$)</label>
                            <input type="number" step="0.01" name="extra_photo_price" class="form-control bg-light" required placeholder="Ex: 25.00" min="0" value="{{ $package->extra_photo_price }}">
                            <div class="form-text"><i class="bi bi-info-circle"></i> Valor cobrado se o cliente exceder a cota fixa.</div>
                        </div>
                        
                        <div class="col-12 mt-4">
                            <div class="form-check form-switch fs-5">
                                <input class="form-check-input" type="checkbox" role="switch" id="activeSwitch" name="is_active" value="1" {{ $package->is_active ? 'checked' : '' }}>
                                <label class="form-check-label ms-2" for="activeSwitch">Pacote Disponível para Venda</label>
                            </div>
                        </div>

                        <div class="col-12 text-end mt-4">
                            <a href="{{ route('admin.packages.index') }}" class="btn btn-light px-4 rounded-pill me-2">Cancelar</a>
                            <button type="submit" class="btn btn-primary px-4 fw-bold rounded-pill shadow-sm"><i class="bi bi-save"></i> Atualizar Pacote</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
