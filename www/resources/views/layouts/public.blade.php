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

    <!-- Dinâmico Settings Injetados -->
    <style>
        :root, [data-bs-theme="dark"] {
            /* Definindo Cores Principais do Site e sobrepondo o Bootstrap */
            --primary-color: {{ \App\Models\Setting::where('key','primary_color')->value('value') ?? '#0a58ca' }}; /* Azul Moderno Default */
            --secondary-color: {{ \App\Models\Setting::where('key','secondary_color')->value('value') ?? '#ffc107' }}; /* Amarelo Ouro Default */
            
            --bs-primary: var(--primary-color);
            --bs-primary-rgb: 10, 88, 202;
            
            --bs-secondary: var(--secondary-color);
            --bs-secondary-rgb: 255, 193, 7;

            --bg-color: #0f172a;
        }
    </style>
</head>
<body class="public-body">
    <!-- Navbar Bootstrap Padrão Elevado -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top glass-header">
        <div class="container">
            <a class="navbar-brand logo-brand" href="/">
                <i class="bi bi-camera me-2 text-secondary"></i>
                {{ \App\Models\Setting::where('key','site_name')->value('value') ?? 'Fotógrafo.io' }}
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
