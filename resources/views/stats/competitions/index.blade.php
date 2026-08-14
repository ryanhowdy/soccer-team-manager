@extends('layouts.main')

@section('body-id', 'stats')
@section('page-title', 'Competition Statistics')
@section('page-desc', "Learn all about a team")

@section('content')
    <div class="container main-content">

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

        <form class="keepAlive"></form>

        <div class="d-flex justify-content-between mb-3">
            <div><h2>Competition Stats</h2></div>
            <div class="d-flex gap-2 align-items-center justify-content-end">
                @include('partials.season-filter', ['filterRoute' => 'stats.competitions.index'])
            @can('edit things')
                <div><div class="vr"></div></div>
                <div class="ps-2">
                    <a href="#" class="btn btn-sm btn-dark text-white rounded-pill py-2 px-3" data-bs-toggle="modal" data-bs-target="#create-competition">
                        <span class="bi-plus-lg pe-0 pe-lg-2"></span><span class="d-none d-lg-inline-block">Add Competition</span>
                    </a>
                </div>
            @endcan
            </div>
        </div>

        @php
            $statusLabels = ['A' => 'Active', 'D' => 'Done', 'C' => 'Cancelled'];
        @endphp

        @foreach ($statusLabels as $statusKey => $statusLabel)
            @if ($competitions->has($statusKey))
                <div class="fw-bold text-secondary fs-5 ps-1 pb-2">{{ $statusLabel }}</div>
                <div class="row gy-3 mb-4">
                @foreach ($competitions[$statusKey] as $comp)
                    @php
                        $record = $recordsByCompetition[$comp->id] ?? ['W' => 0, 'D' => 0, 'L' => 0];
                        $games  = $record['W'] + $record['D'] + $record['L'];
                        $winPct = $games ? round($record['W'] / $games * 100) : 0;
                    @endphp
                    <div class="col-12 col-lg-6">
                        <div class="rounded rounded-3 bg-white position-relative p-4 h-100">
                            <div class="d-flex">
                                <div>
                                    <div class="circle-icon rounded-circle text-bg-primary text-center me-2">
                                        <span class="bi-trophy-fill"></span>
                                    </div>
                                </div>
                                <div class="pe-5">
                                    <div class="text-muted small text-uppercase">{{ $comp->type }}</div>
                                    <div class="fs-5">
                                        <a class="link-underline link-underline-opacity-0 link-underline-opacity-100-hover text-reset"
                                            href="{{ route('competitions.show', ['competition' => $comp->id]) }}">{{ $comp->name }}</a>
                                    </div>
                                    <span class="smaller fw-bold text-primary">{{ $comp->division }}</span>
                                </div>
                            @if($comp->place)
                                <span @class([
                                    'position-absolute top-0 end-0 fs-4 m-3 badge',
                                    'text-bg-success text-white' => ($comp->place == 1),
                                    'text-bg-info' => ($comp->place == 2),
                                    'text-bg-dark' => ($comp->place >= 3),
                                    ])>{{ $comp->place_ordinal }}</span>
                            @endif
                            </div>

                            <div class="d-flex align-items-center pt-3 mt-3 border-top">
                            @if($games)
                                <div class="pe-4">
                                    <div class="fs-4 lh-1">{{ $record['W'] }}-{{ $record['D'] }}-{{ $record['L'] }}</div>
                                    <div class="text-muted smaller">{{ $games }} {{ $games == 1 ? 'game' : 'games' }}</div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="progress-stacked" style="height: 5px;">
                                        <div class="progress" role="progressbar" style="width: {{ $winPct }}%">
                                            <div class="progress-bar bg-success"></div>
                                        </div>
                                        <div class="progress" role="progressbar" style="width: {{ $games ? round($record['D'] / $games * 100) : 0 }}%">
                                            <div class="progress-bar bg-secondary"></div>
                                        </div>
                                        <div class="progress" role="progressbar" style="width: {{ $games ? round($record['L'] / $games * 100) : 0 }}%">
                                            <div class="progress-bar bg-danger"></div>
                                        </div>
                                    </div>
                                    <div class="text-muted smaller pt-1">{{ $winPct }}% win rate</div>
                                </div>
                            @else
                                <div class="text-muted smaller fst-italic">No completed games</div>
                            @endif
                            </div>

                            <div class="d-flex justify-content-between align-items-center pt-2">
                                <div class="text-secondary fst-italic small">
                                    {{ $comp->started_at->format('M jS') }} - {{ $comp->ended_at->format('M jS, Y') }}
                                </div>
                                <div class="d-flex gap-3 align-items-center">
                                @if($comp->website)
                                    <a class="link-secondary fs-5 lh-1" href="{{ $comp->website }}" target="_blank" rel="noopener"
                                        title="Competition website" aria-label="Competition website">
                                        <span class="bi-link"></span>
                                    </a>
                                @endif
                                @can('edit things')
                                    <a class="link-secondary fs-5 lh-1" href="#" data-bs-toggle="modal" data-bs-target="#edit-competition-{{ $comp->id }}"
                                        title="Edit competition" aria-label="Edit competition">
                                        <span class="bi-pencil"></span>
                                    </a>
                                @endcan
                                {{-- Notes sit off on their own at the far right, behind a divider --}}
                                @if($comp->notes)
                                    <div class="vr"></div>
                                    <a class="notes-toggle link-secondary fs-5 lh-1 collapsed" href="#comp-notes-{{ $comp->id }}" role="button"
                                        data-bs-toggle="collapse" aria-expanded="false" aria-controls="comp-notes-{{ $comp->id }}"
                                        title="Show/hide notes" aria-label="Show/hide notes">
                                        <span class="bi-caret-down-fill"></span>
                                    </a>
                                @endif
                                </div>
                            </div>

                        @if($comp->notes)
                            <div class="collapse" id="comp-notes-{{ $comp->id }}">
                                <div class="border-top mt-3 pt-3 small text-secondary" style="white-space: pre-line;">{{ html_entity_decode($comp->notes) }}</div>
                            </div>
                        @endif
                        </div>
                    </div>
                @endforeach
                </div>
            @endif
        @endforeach

        @if ($competitions->isEmpty())
            <div class="rounded rounded-3 bg-white p-5 text-center mb-3">
                <img class="opacity-50 w-25" src="{{ asset('img/empty-state.svg') }}">
                <div class="fs-3 fw-bold mt-5 pb-1">No Competitions</div>
                <small class="pb-3 d-block text-muted">No competitions found for this team and season.</small>
            </div>
        @endif

    </div><!--/container-->

@can('edit things')
    <div id="create-competition" class="modal fade" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content py-4 px-2">
                <div class="modal-header">
                    <h5 class="modal-title">New Competition</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
@include('competitions.create-form')
                </div>
            </div>
        </div>
    </div>

    @foreach ($competitions->flatten() as $comp)
    <div id="edit-competition-{{ $comp->id }}" class="modal fade" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content py-4 px-2">
                <div class="modal-header">
                    <h5 class="modal-title">Edit {{ $comp->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
@include('competitions.edit-form', ['comp' => $comp])
                </div>
            </div>
        </div>
    </div>
    @endforeach
@endcan
@endsection
