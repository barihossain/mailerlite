<!doctype html>
<html lang="en">
    <head>
        <title>@yield('page-title')</title>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset("assets/css/styles.css") }}">
    
    @yield('css-block')
</head>
<body class="bg-light">
    <main role="main" class="container">
        <div class="card mb-3 mt-3">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-6">
                        <h3>@yield('page-title')</h3>
                    </div>
                    <div class="col-md-6 d-flex">
                        @yield('extra-buttons')
                    </div>
                </div>
            </div>
            <div class="card-body">
            @yield('body')
            </div>
        </div>
    </main>

    <!-- Optional JavaScript; choose one of the two! -->

    <!-- Option 1: Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    @yield('js-block')
</body>
</html>