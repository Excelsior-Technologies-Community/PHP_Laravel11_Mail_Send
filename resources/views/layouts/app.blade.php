<!DOCTYPE html>
<html>
<head>
    <title>Laravel Mail App</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <style>
        .navbar {
            /* border-radius: 10px; */
            margin-bottom: 20px;
            text-align: center;
        }
        .card {
            border-radius: 15px;
        }
        body {
            background: #f5f6fa;
        }

        
    </style>
</head>

<body>

<nav class="navbar navbar-dark bg-dark w-100 shadow-sm">
    <div class="container-fluid d-flex justify-content-center">
        <span class="navbar-brand mb-0 h1 text-center" style="font-size: 22px;">
            Mail Sender
        </span>
    </div>
</nav>




<div class="container">
    @yield('content')
</div>

</body>
</html>
