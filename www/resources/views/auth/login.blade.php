@extends('layouts.public')

@section('title', 'Acesso ao Portal')

@section('content')
<div class="row justify-content-center align-items-center mt-5" style="min-height: 60vh;">
    <div class="col-12 col-md-8 col-lg-5">
        <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5" style="background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(15px);">
            <div class="text-center mb-4">
                <i class="bi bi-camera-fill text-secondary display-4"></i>
                <h3 class="text-white fw-bold mt-3">Acesso Secreto</h3>
                <p class="text-muted">Entre com suas credenciais para visualizar suas fotos ou administrar o estúdio.</p>
            </div>

            @if($errors->any())
                <div class="alert alert-danger rounded-3 border-0 bg-danger text-white bg-opacity-25 pb-0">
                    <ul class="mb-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}">
                @csrf
                <div class="mb-4">
                    <label for="email" class="form-label text-white fw-bold">E-mail</label>
                    <input type="email" class="form-control form-control-lg border-0 bg-dark text-white" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="voce@exemplo.com">
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label text-white fw-bold">Senha</label>
                    <input type="password" class="form-control form-control-lg border-0 bg-dark text-white" id="password" name="password" required placeholder="Sua senha secreta">
                </div>
                
                <div class="mb-4 form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label text-white-50" for="remember">Lembrar de mim por 30 dias</label>
                </div>

                <div class="d-grid mt-5">
                    <button type="submit" class="btn btn-secondary btn-lg fw-bold rounded-pill text-dark">Acessar Painel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
