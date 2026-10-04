@extends('layouts.main')

@section('body-id', 'formations')

@section('content')
    <div class="container main-content">

        <div class="d-flex justify-content-between mb-3">
            <div><h2>Formations</h2></div>
            <div class="d-flex gap-2 align-items-center justify-content-end">
                <div class="ps-2">
                    <a href="#" class="btn btn-sm btn-dark text-white rounded-pill py-2 px-3" data-bs-toggle="modal" data-bs-target="#create-formation">
                        <span class="bi-plus-lg pe-0 pe-lg-2"></span><span class="d-none d-lg-inline-block">Add Formation</span>
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

        <ul class="nav nav-underline mb-4" role="tablist">
        @foreach($formations as $playerCount => $fs)
            <li class="nav-item" role="presentation">
                <a @class(['nav-link', 'active' => $loop->first]) href="#" id="formations-{{ $playerCount }}-tab"
                    data-bs-toggle="tab" data-bs-target="#formations-{{ $playerCount }}-pane" role="tab">
                    {{ $playerCount }}v{{ $playerCount }}
                </a>
            </li>
        @endforeach
        </ul>

        <div class="tab-content">
        @foreach($formations as $playerCount => $fs)
            <div @class(['tab-pane', 'show active' => $loop->first]) id="formations-{{ $playerCount }}-pane" role="tabpanel">
            <div class="d-flex flex-wrap">
            @foreach($fs as $formation)
                <div class="flex-fill mb-3">
                    <div class="card me-3">
                        <div class="card-header text-center">
                            <h3 class="mb-0">{{ implode('-', str_split($formation->name)) }}</h3>
                            <div class="d-flex justify-content-center align-items-center text-secondary small">
                                <span>{{ $formation->description }}</span>
                            @can('edit things')
                                <a href="#" class="link-secondary ms-2" data-bs-toggle="modal" data-bs-target="#edit-formation-{{ $formation->id }}"
                                    title="Edit description" aria-label="Edit description">
                                    <span class="bi-pencil"></span>
                                </a>
                            @endcan
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="field{{ $formation->id }}" class="field field-sm field-labels mx-auto text-center position-relative">
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
            </div><!--/.tab-pane-->
        @endforeach
        </div><!--/.tab-content-->

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

@can('edit things')
    @foreach($formations as $fs)
        @foreach($fs as $formation)
    <div id="edit-formation-{{ $formation->id }}" class="modal fade" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content py-4 px-2">
                <div class="modal-header">
                    <h5 class="modal-title">Edit {{ implode('-', str_split($formation->name)) }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" action="{{ route('formations.update', ['formation' => $formation->id]) }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="description-{{ $formation->id }}">Description</label>
                            <input type="text" class="form-control" id="description-{{ $formation->id }}" name="description" value="{{ $formation->description }}"
                                placeholder="Defensive" maxlength="255">
                        </div>
                        <button type="submit" class="btn btn-primary text-white">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
        @endforeach
    @endforeach
@endcan

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
