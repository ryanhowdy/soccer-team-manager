@include('partials.header')

<body id="@yield('body-id', '')" class="bg-light">

    @include('partials.navigation')

    <div class="bg-dark text-white" style="height:250px">
        <div class="container pt-4">
            <h4 class="">@yield('page-title')</h4>
            <p class="small text-light">@yield('page-desc')</p>
        </div>
    </div>

    @yield('content')

</body>
</html>
