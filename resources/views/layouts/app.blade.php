<!DOCTYPE html>
<html>
<head>
    <title>Laravel Mail App</title> <!-- Page title -->

    <!-- Bootstrap 5 CSS CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <style>
        /* Navbar custom styling */
        .navbar {
            /* border-radius: 10px; */ /* Commented out optional rounded corners */
            margin-bottom: 20px; /* Space below navbar */
            text-align: center; /* Center align text */
        }

        /* Card styling */
        .card {
            border-radius: 15px; /* Rounded corners */
        }

        /* Body background */
        body {
            background: #f5f6fa; /* Light gray background */
        }
    </style>
</head>

<body>

<!-- Navbar -->
<nav class="navbar navbar-dark bg-dark w-100 shadow-sm"> <!-- Dark navbar with full width and shadow -->
    <div class="container-fluid d-flex justify-content-center"> <!-- Center content horizontally -->
        <span class="navbar-brand mb-0 h1 text-center" style="font-size: 22px;">
            Mail Sender <!-- Navbar title -->
        </span>
    </div>
</nav>

<!-- Main container for Blade content -->
<div class="container">
    @yield('content') <!-- Placeholder for page-specific content -->
</div>

</body>
</html>
