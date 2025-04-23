@extends('layouts.main')

@section('body-id', 'teams')

@section('content')
    <div class="container main-content">

        <div class="rounded rounded-3 bg-white p-4 mb-1">
            <div class="row">
                <div class="col-12 col-lg-4 col-md-6">
                    <div class="alert alert-secondary">
                        <h5>Step 1: {{ $clubs[0]->name }}</h5>
                    </div>
                    <div class="alert alert-primary">
                        <h5>Step 2: Managed Team</h5>
                        <p>Next we will need to create a Managed Team.</p>
                    </div>
                </div>
                <div class="col-12 col-lg-8 col-md-6">
@include('teams.create-form')
                </div>
            </div>
        </div>
        <style>
        form .row:first-of-type { display: none }
        </style>

    </div><!--/container-->
@endsection
