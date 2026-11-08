<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('fincore-pro.theme.brand_name', 'FinCore Accounting') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold" href="{{ route('fincore.dashboard') }}">
                {{ config('fincore-pro.theme.brand_name', 'FinCore Accounting') }}
            </a>
            <div class="d-flex align-items-center">
                <a href="{{ config('fincore-pro.theme.back_to_erp_url', '/') }}" class="btn btn-outline-light btn-sm">
                    &larr; Back to Main ERP
                </a>
            </div>
        </div>
    </nav>

    <main class="container-fluid px-4 py-4">
        @yield('fincore_standalone_body')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
