<!DOCTYPE html>

<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'Page with Background')</title>
<link href="https://www.google.com/search?q=https://cdn.jsdelivr.net/npm/bootstrap%405.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://www.google.com/search?q=https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
/* This style block contains the background styling for this layout */
*, *::before, *::after {
box-sizing: border-box;
}

html, body {
    height: 100%;
    margin: 0;
    padding: 0;
    width: 100%;
    font-family: 'Inter', sans-serif;
}

body {
    background-color: #f0f0f0; /* Fallback color */
    background-image: url('https://raw.githubusercontent.com/alexdame/nonense/main/background.jpg');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
}

</style>

</head>
<body>
@yield('content')
</body>
</html>