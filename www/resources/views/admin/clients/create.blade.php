@extends('layouts.admin')

@section('title', 'Novo Cliente')
@section('header_title', 'Cadastrar Cliente')

@section('content')
<div class="card border-0 shadow-sm rounded-4 max-w-xl mx-auto">
    <div class="card-body p-5">
        <h5 class="mb-4 text-primary fw-bold"><i class="bi bi-person-plus text-secondary me-2"></i> Informações do Cliente</h5>
        
        <form action="{{ route('admin.clients.store') }}" method="POST">
            @csrf
            
            <div class="mb-3">
                <label for="name" class="form-label">Nome Completo</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            
            <div class="mb-3">
                <label for="email" class="form-label">Email Principal</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="document" class="form-label">CPF / CNPJ</label>
                    <input type="text" class="form-control" id="document" name="document">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="phone" class="form-label">Telefone / WhatsApp</label>
                    <input type="text" class="form-control" id="phone" name="phone">
                </div>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <a href="{{ route('admin.clients.index') }}" class="btn btn-outline-secondary me-2">Cancelar</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Salvar Cliente</button>
            </div>
        </form>
    </div>
</div>
@endsection
