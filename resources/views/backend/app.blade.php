<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PetMart Marketplace Admin</title>
    
    <!-- Theme & App CSS -->
    <link rel="stylesheet" href="{{ asset('backend_assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('backend_assets/css/icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('backend_assets/css/app.min.css') }}">
    <link rel="stylesheet" href="{{ asset('backend_assets/css/custom.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <div id="app">
        <App></App>
    </div>

    <!-- Global JS Environment Vars for Legacy Vue Mixin -->
    <script>
        window.baseUrl = "{{ url('/') }}";
        window.publicPath = "{{ asset('') }}";
        window.UploadPath = "{{ asset('uploads') }}";
        var baseUrl = window.baseUrl;
        var publicPath = window.publicPath;
        var UploadPath = window.UploadPath;
    </script>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('backend_assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ mix('js/app.js') }}"></script>
</body>
</html>
