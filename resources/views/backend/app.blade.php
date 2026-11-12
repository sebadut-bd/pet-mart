<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PetMart Admin</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">

    <link rel="stylesheet" href="{{ asset('backend_assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('backend_assets/css/icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('backend_assets/css/app.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
</head>
<body>
    <div id="app">
        <App></App>
    </div>

    <script>
        window.baseUrl = @json(url('/'));
        window.publicPath = @json(asset(''));
        window.UploadPath = @json(asset('storage'));
        window.frontendUrl = @json(config('app.frontend_url'));
        var baseUrl = window.baseUrl;
        var publicPath = window.publicPath;
        var UploadPath = window.UploadPath;
    </script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('backend_assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ mix('js/app.js') }}"></script>
</body>
</html>
