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

<!-- Global Confirmation Modal -->
<div class="modal fade" id="globalConfirmModal" tabindex="-1" aria-labelledby="globalConfirmModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold text-danger" id="globalConfirmModalLabel"><i class="bi bi-exclamation-triangle-fill me-2"></i>Atenção</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-secondary" id="globalConfirmModalBody">
         Deseja realmente prosseguir com esta ação?
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger rounded-pill px-4" id="globalConfirmModalBtn">Confirmar</button>
      </div>
    </div>
  </div>
</div>

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

<!-- Bootstrap JS & jQuery (needed for DataTables) -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Global Script Overrides -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Init Toasts natively
        var toastElList = [].slice.call(document.querySelectorAll('.toast'));
        var toastList = toastElList.map(function (toastEl) {
          var t = new bootstrap.Toast(toastEl, { autohide: true });
          t.show();
          return t;
        });

        // Add loading states to all forms automatically
        document.querySelectorAll('form').forEach(form => {
            // Ignore Dropzone which handles asynchronously
            if(form.classList.contains('dropzone')) return;

            form.addEventListener('submit', function() {
                let btn = form.querySelector('button[type="submit"]');
                if(btn && !btn.classList.contains('disabled')) {
                    btn.classList.add('disabled');
                    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Processando...`;
                }
            });
        });
    });

    // Global Confirm Abstraction
    window.askConfirm = function(title, text, callback) {
        document.getElementById('globalConfirmModalLabel').innerHTML = title;
        document.getElementById('globalConfirmModalBody').innerHTML = text;
        
        let confirmModal = new bootstrap.Modal(document.getElementById('globalConfirmModal'));
        
        let confirmBtn = document.getElementById('globalConfirmModalBtn');
        let newBtn = confirmBtn.cloneNode(true);
        confirmBtn.parentNode.replaceChild(newBtn, confirmBtn);
        
        newBtn.addEventListener('click', function() {
            confirmModal.hide();
            callback();
        });
        
        confirmModal.show();
    };
</script>

</body>
</html>
