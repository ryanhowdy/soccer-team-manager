@extends('layouts.main')

@section('body-id', 'formations')

@section('content')
    <div class="container main-content">

        <div class="rounded rounded-3 bg-white py-2 px-3 mb-2 text-end">
            <a href="#" class="btn btn-sm btn-primary text-white" data-bs-toggle="modal" data-bs-target="#create-formation">
                <span class="bi-plus-lg pe-2"></span>Add Formation
            </a>
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

        @foreach($formations as $playerCount => $fs)
            <h5>{{ $playerCount }}v{{ $playerCount }}</h5>
            <div class="d-flex flex-wrap mb-5">
            @foreach($fs as $formation)
                <div class="flex-fill mb-3">
                    <div class="card me-3">
                        <div class="card-header text-center"><h3>{{ implode('-', str_split($formation->name)) }}</h3></div>
                        <div class="card-body">
                            <div id="field{{ $formation->id }}" class="field field-sm mx-auto text-center position-relative">
                                <img class="position-absolute start-0 top-0" src="{{ asset('img/field.svg') }}" />
                            </div><!--/#field-->
                        </div>
                        <script>
                        let formation{{ $formation->id }} = {{ Js::from($formation) }};
                        let drawer{{ $formation->id }} = new FormationDrawer({}, {});
                        drawer{{ $formation->id }}.drawFormation(formation{{ $formation->id }}, '#field{{ $formation->id }}');
                        </script>
                    </div>
                </div>
            @endforeach
            </div>
        @endforeach

        </div>

    </div><!--/container-->

    <div id="create-formation" class="modal fade" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content py-4 px-2">
                <div class="modal-header">
                    <h5 class="modal-title">Add formation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
@include('formations.create-form')
                </div>
            </div>
        </div>
    </div>

<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
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
