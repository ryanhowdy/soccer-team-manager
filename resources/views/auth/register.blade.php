@extends('layouts.auth')

@section('content')

    <form class="text-start" action="{{ route('register') }}" method="post">
        @csrf
        <h2 class="mb-3">Register</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <h4 class="alert-heading">An error has occurred</h4>
        @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
        </div>
    @endif

        <div class="mb-3 required">
            <label for="email">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}">
        </div>
        <div class="mb-3 required">
            <label for="password">Password</label>
            <input type="password" class="form-control" id="password" name="password">
        </div>
        <div class="mb-3 required">
            <label for="password_confirmation">Confirm Password</label>
            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
        </div>

        <div>
            <input type="submit" class="btn btn-primary" value="Register">
        </div>
    </form>

@endsection
