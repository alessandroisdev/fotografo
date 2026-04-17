@extends('layouts.admin')

@section('title', 'Novo Pacote')
@section('header_title', 'Criar Molde de Venda')

@section('content')
<div class="card border-0 shadow-sm rounded-4 max-w-xl mx-auto">
    <div class="card-body p-5">
        <h5 class="mb-4 text-primary fw-bold"><i class="bi bi-tags text-secondary me-2"></i> Estrutura de Preço</h5>
        
        <form action="{{ route('admin.packages.store') }}" method="POST">
            @csrf
            
            <div class="mb-3">
                <label for="name" class="form-label">Nome do Pacote</label>
                <input type="text" class="form-control" id="name" name="name" placeholder="Ex: Pacote Casamento Diamante" required>
            </div>
            
            <div class="mb-3">
                <label for="description" class="form-label">Descrição</label>
                <textarea class="form-control" id="description" name="description" required rows="2" placeholder="Ofereça detalhes do pacote..."></textarea>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="included_photos_count" class="form-label text-primary fw-bold">Fotos Inclusas</label>
                    <input type="number" class="form-control" id="included_photos_count" name="included_photos_count" required value="20">
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="price" class="form-label text-success fw-bold">Valor Base (R$)</label>
                    <input type="number" step="0.01" class="form-control" id="price" name="price" required placeholder="0.00">
                </div>

                <div class="col-md-4 mb-3">
                    <label for="extra_photo_price" class="form-label fw-bold">Preço Foto Extra (R$)</label>
                    <input type="number" step="0.01" class="form-control" id="extra_photo_price" name="extra_photo_price" required placeholder="0.00">
                </div>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <a href="{{ route('admin.packages.index') }}" class="btn btn-outline-secondary me-2">Cancelar</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-box"></i> Salvar Pacote</button>
            </div>
        </form>
    </div>
</div>
@endsection
