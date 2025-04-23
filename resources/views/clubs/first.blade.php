@extends('layouts.main')

@section('body-id', 'teams')

@section('content')
    <div class="container main-content">

        <div class="rounded rounded-3 bg-white p-4 mb-1">
            <div class="row">
                <div class="col-12 col-lg-4 col-md-6">
                    <div class="alert alert-primary">
                        <h5>Step 1: Managed Club</h5>
                        <p>In order to begin we first need to create a Managed Club with 1 or more Managed Teams.</p>
                    </div>
                </div>
                <div class="col-12 col-lg-8 col-md-6">
@include('teams.create-club-form')
                </div>
            </div>
        </div>

    </div><!--/container-->
@endsection
