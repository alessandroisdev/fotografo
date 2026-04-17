<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Gestão Fotógrafo</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- DataTables CSS for Premium Look -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.ts'])
</head>
<body class="bg-light admin-body">

<div class="d-flex" id="wrapper">
    <!-- Sidebar -->
    <div class="bg-white border-end" id="sidebar-wrapper" style="width: 250px; min-height: 100vh;">
        <div class="sidebar-heading p-4 fs-5 fw-bold text-primary">
            <i class="bi bi-camera-fill text-secondary me-2"></i> Fotógrafo ADMIN
        </div>
        <div class="list-group list-group-flush mt-3 border-top">
            <a class="list-group-item list-group-item-action list-group-item-light p-3 border-0 active" href="/admin/dashboard"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
            <a class="list-group-item list-group-item-action list-group-item-light p-3 border-0" href="/admin/clients"><i class="bi bi-people me-2"></i> Clientes</a>
            <a class="list-group-item list-group-item-action list-group-item-light p-3 border-0" href="/admin/galleries"><i class="bi bi-images me-2"></i> Galerias</a>
            <a class="list-group-item list-group-item-action list-group-item-light p-3 border-0" href="/admin/packages"><i class="bi bi-box-seam me-2"></i> Pacotes</a>
            <a class="list-group-item list-group-item-action list-group-item-light p-3 border-0" href="/admin/settings"><i class="bi bi-gear me-2"></i> Configurações</a>
            <a class="list-group-item list-group-item-action list-group-item-light p-3 border-0 text-danger mt-4" href="/logout"><i class="bi bi-box-arrow-left me-2"></i> Sair</a>
        </div>
    </div>
    <!-- /#sidebar-wrapper -->

    <!-- Page Content -->
    <div id="page-content-wrapper" class="w-100 flex-grow-1">
        <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom px-4 py-3 sticky-top">
            <div class="container-fluid">
                <h2 class="mb-0 fs-4 fw-semibold text-dark">@yield('header_title', 'Visão Geral')</h2>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ms-auto mt-2 mt-lg-0">
                        <li class="nav-item">
                            <a class="nav-link fw-bold text-primary" href="/"><i class="bi bi-box-arrow-up-right"></i> Ver Site</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="container-fluid p-4">
            @yield('content')
        </div>
    </div>
    <!-- /#page-content-wrapper -->
</div>

<!-- Bootstrap JS & jQuery (needed for DataTables) -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
