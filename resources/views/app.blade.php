<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title inertia>Mundo Yacus</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @routes
    @vite(['resources/js/app.js', "resources/js/Pages/" . $page['component'] . ".vue"])
    @inertiaHead
</head>
<body class="font-sans antialiased">
    @inertia
</body>
</html>