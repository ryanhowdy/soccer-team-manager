@extends('layouts.main')

@section('body-id', 'teams')

@section('content')
    <div class="container main-content">

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

        <div class="rounded rounded-3 bg-white p-4 mb-1">
            <h5 class="mb-3">Edit Club</h5>

            <form method="post" style="max-width:500px" action="{{ route('clubs.update', ['club' => $club]) }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="name">Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ $club->name }}">
                </div>
                <label class="form-label" for="logo">Logo</label>
                <div class="row g-2 mb-3">
                    <div class="col-auto">
                        <img class="logo img-fluid ms-2 me-3" src="{{ asset($club->logo) }}"/>
                    </div>
                    <div class="col">
                        <input type="file" class="form-control" id="logo" name="logo">
                    </div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-8">
                        <label class="form-label" for="city">City</label>
                        <input type="text" class="form-control" name="city" id="city" value="{{ $club->city }}">
                    </div>
                    <div class="col-4">
                        <label class="form-label" for="state">State</label>
                        <input type="text" class="form-control" name="state" id="state" value="{{ $club->state }}">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="website">Website</label>
                    <input type="text" class="form-control" id="website" name="website" value="{{ $club->website }}">
                </div>
                <div class="mb-3">
                    <label for="notes">Notes</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3">{{ $club->notes }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary text-white">Submit</button>
                <a href="{{ route('teams.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>

    </div><!--/container-->
@endsection
