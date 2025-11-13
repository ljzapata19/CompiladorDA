<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nexus - @yield('title') </title>
    <link rel="icon" href="{{ asset('img/logo.png') }}" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container { max-width: 1200px; }
        .code-area { font-family: 'Courier New', monospace; font-size: 0.9em; }
        .output { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .table-responsive { max-height: 400px; overflow-y: auto; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <!-- <img src="{{ asset('img/logo.png') }}" alt="Nexus Logo" height="30" class="me-2">
            <a class="navbar-brand mb-0 h1" href="{{ route('index') }}">NEXUS</a> -->
             <div class="navbar-brand d-flex align-items-center">
                <img src="{{ asset('img/logo.png') }}" alt="Nexus Logo" height="30" class="me-2">
                
                <a class="mb-1 text-white text-decoration-none fw-bold ms-3" href="{{ route('index') }}">NEXUS</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>