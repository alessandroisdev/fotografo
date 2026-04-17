<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Galeria do Fotógrafo')</title>

    <!-- Google Fonts for Premium Typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    @vite(['resources/css/app.css', 'resources/js/app.ts'])

    <!-- Custom Styling & Utilities via Global Thematic Scope -->
    @include('partials.theme')
</head>
<body class="public-body">
    <!-- Navbar Bootstrap Padrão Elevado -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top glass-header">
        <div class="container">
            <a class="navbar-brand logo-brand" href="/">
                <i class="bi bi-camera me-2 text-secondary"></i>
                {{ config('settings.site_title', 'Fotógrafo.io') }}
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item">
                        <a class="nav-link active" href="/"><i class="bi bi-grid"></i> Portfólio</a>
                    </li>
                    <li class="nav-item ms-lg-3 mt-2 mt-lg-0">
                        <a class="btn btn-outline-secondary rounded-pill px-4" href="/client"><i class="bi bi-person-fill"></i> Área do Cliente</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="page-content container">
        @yield('content')
    </main>

    <footer class="mt-auto py-4 text-center text-white-50">
        <div class="container">
            <p class="mb-0"><i class="bi bi-c-circle"></i> {{ date('Y') }} Todos os direitos reservados.</p>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle -->
    <!-- Global Toasts Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-4" style="z-index: 1060">
    @foreach (['success', 'danger', 'warning', 'info'] as $msg)
        @if(Session::has($msg) || ($msg == 'danger' && Session::has('error')))
            <div class="toast align-items-center text-bg-{{ $msg == 'error' ? 'danger' : $msg }} border-0 mb-2 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="4000">
                <div class="d-flex">
                    <div class="toast-body fw-semibold">
                        @if($msg == 'success') <i class="bi bi-check-circle-fill me-2"></i> @endif
                        @if($msg == 'danger' || $msg == 'error') <i class="bi bi-x-circle-fill me-2"></i> @endif
                        @if($msg == 'warning') <i class="bi bi-exclamation-circle-fill me-2"></i> @endif
                        {{ Session::get($msg) ?? Session::get('error') }}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        @endif
    @endforeach
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var toastElList = [].slice.call(document.querySelectorAll('.toast'));
        toastElList.map(function (toastEl) {
          return new bootstrap.Toast(toastEl, { autohide: true }).show();
        });
        
        // Add loading states to forms
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                let btn = form.querySelector('button[type="submit"]');
                if(btn && !btn.classList.contains('disabled')) {
                    btn.classList.add('disabled');
                    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Processando...`;
                }
            });
        });
    });
</script>
</body>
</html>
