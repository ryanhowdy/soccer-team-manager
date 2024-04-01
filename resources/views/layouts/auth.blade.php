@include('partials.header')

<body class="text-center bg-light">

    <div class="bg-dark text-white" style="height:160px">
        <div class="p-3">
            <div class="position-relative d-inline-block me-3" style="width:3rem; height:3rem;">
                <div class="rounded-circle d-flex align-items-center justify-content-center w-100 h-100 bg-white text-dark">
                    <i class="bi bi-box-fill"></i>
                </div>
            </div>
            <h1 class="d-inline-block align-middle">STM</h1>
        </div>
    </div>

    <div class="mx-auto bg-white border p-5" style="width: 400px; margin-top:-70px">
        @yield('content')
    </div>
</body>
</html>
