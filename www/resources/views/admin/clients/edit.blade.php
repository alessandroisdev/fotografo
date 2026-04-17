@extends('layouts.admin')

@section('title', 'Editar Cliente')
@section('header_title', 'Atualizar Cadastro')

@section('content')
<div class="card border-0 shadow-sm rounded-4 max-w-xl mx-auto">
    <div class="card-body p-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0 text-primary fw-bold"><i class="bi bi-person-lines-fill text-secondary me-2"></i> Dados de {{ $client->name }}</h5>
        </div>
        
        <form action="{{ route('admin.clients.update', $client->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Nome Completo</label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $client->name) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="document" class="form-label">CPF / CNPJ</label>
                    <input type="text" class="form-control" id="document" name="document" value="{{ old('document', $client->document) }}" placeholder="Somente números">
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email de Acesso</label>
                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $client->email) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="phone" class="form-label">WhatsApp</label>
                    <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone', $client->phone) }}" placeholder="(DDD) 9...">
                </div>
            </div>

            <hr class="my-4 text-muted">
            <!-- Reset de Senha (Opcional) -->
            <div class="mb-3">
                <label for="password" class="form-label fw-bold">Nova Senha (Opcional)</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Preencha apenas se desejar redefinir o acesso dele.">
            </div>

            <div class="d-flex justify-content-end mt-4">
                <a href="{{ route('admin.clients.index') }}" class="btn btn-outline-secondary me-2">Cancelar</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-cloud-arrow-up"></i> Atualizar Dados</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const docInput = document.getElementById('document');
        const phoneInput = document.getElementById('phone');
        
        // Formatar no load se tiver valor solto do DB
        const formatCpfCnpj = (v) => {
            v = v.replace(/\D/g, "");
            if (v.length <= 11) {
                v = v.replace(/(\d{3})(\d)/, "$1.$2");
                v = v.replace(/(\d{3})(\d)/, "$1.$2");
                v = v.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
            } else {
                v = v.replace(/^(\d{2})(\d)/, "$1.$2");
                v = v.replace(/^(\d{2})\.(\d{3})(\d)/, "$1.$2.$3");
                v = v.replace(/\.(\d{3})(\d)/, ".$1/$2");
                v = v.replace(/(\d{4})(\d)/, "$1-$2");
            }
            return v;
        };
        
        const formatPhone = (v) => {
            v = v.replace(/\D/g, "");
            v = v.replace(/^(\d{2})(\d)/g, "($1) $2");
            v = v.replace(/(\d)(\d{4})$/, "$1-$2");
            return v;
        };

        if(docInput) {
            if(docInput.value) docInput.value = formatCpfCnpj(docInput.value);
            docInput.addEventListener('input', function(e) {
                e.target.value = formatCpfCnpj(e.target.value);
            });
        }
        
        if(phoneInput) {
            if(phoneInput.value) phoneInput.value = formatPhone(phoneInput.value);
            phoneInput.addEventListener('input', function(e) {
                e.target.value = formatPhone(e.target.value);
            });
        }
    });
</script>
@endsection
