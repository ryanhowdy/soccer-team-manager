@extends('layouts.auth')

@section('content')

    <form action="{{ route('login') }}" method="post">
        @csrf
        <h2>Sign In</h2>

        @if ($errors->any())
            <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
            </div>
        @endif

        <div class="form-floating">
            <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com">
            <label for="email">Email</label>
        </div>

        <div class="form-floating mb-3">
            <input type="password" class="form-control" id="password" name="password" placeholder="Password">
            <label for="password">Password</label>
        </div>

        <div class="checkbox mb-3">
            <label>
                <input type="checkbox" class="me-1" name="remember-me" value="1">Remember Me
            </label>
        </div>

        <button class="w-100 btn btn-lg btn-primary" type="submit">Sign In</button>
    </form>
    <p class="mt-3">
        <a href="{{ route('password.request') }}">Forgot Password</a>
    </p>

@endsection
