@extends('layouts.main')

@section('body-id', 'locations')

@section('content')
    <div class="container main-content">

        <div class="d-flex justify-content-between mb-3">
            <div><h2>Locations</h2></div>
            <div class="d-flex gap-2 align-items-center justify-content-end">
                <div class="ps-2">
                    <a href="#" class="btn btn-sm btn-dark text-white rounded-pill py-2 px-3" data-bs-toggle="modal" data-bs-target="#create-location">
                        <span class="bi-plus-lg pe-0 pe-lg-2"></span><span class="d-none d-lg-inline-block">Add Location</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="rounded rounded-3 bg-white position-relative p-4 mb-3">

        @if ($errors->any())
            <div class="alert alert-danger mt-3">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

            <table class="table table-bordered">
                <thead>
                    <th>Name</th>
                    <th>Address</th>
                </thead>
                <tbody>
                @foreach($locations as $location)
                    <tr>
                        <td>{{ $location->name }}</td>
                        <td>
                            <a target="_blank" href="{{ createGoogleMapsUrlFromAddress($location->address) }}">{{ $location->address }}</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

    </div><!--/container-->

    <div id="create-location" class="modal fade" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content py-4 px-2">
                <div class="modal-header">
                    <h5 class="modal-title">Add Location</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
@include('locations.create-form')
                </div>
            </div>
        </div>
    </div>

<script>
$('.table').DataTable({
    autoWidth: false,
    paging: false,
    searching: false,
    info: false,
    order: [[0, 'asc']]
});
</script>
@endsection
