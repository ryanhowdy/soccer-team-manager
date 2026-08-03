@extends('layouts.main')

@section('body-id', 'stats')
@section('page-title', 'Competition Statistics')
@section('page-desc', "Learn all about a team")

@section('content')
    <div class="container main-content">

        <div class="d-flex justify-content-between mb-3">
            <div><h2>Competition Stats</h2></div>
            <div class="d-flex gap-2 align-items-center justify-content-end">
                @include('partials.season-filter', ['filterRoute' => 'stats.competitions.index'])
            </div>
        </div>

        @php
            $statusLabels = ['A' => 'Active', 'D' => 'Done', 'C' => 'Cancelled'];
        @endphp

        @foreach ($statusLabels as $statusKey => $statusLabel)
            @if ($competitions->has($statusKey))
                <div class="fw-bold text-secondary fs-5 ps-1 pb-2">{{ $statusLabel }}</div>
                <div class="row">
                @foreach ($competitions[$statusKey] as $comp)
                    @php
                        $record = $recordsByCompetition[$comp->id] ?? ['W' => 0, 'D' => 0, 'L' => 0];
                        $games  = $record['W'] + $record['D'] + $record['L'];
                        $winPct = $games ? round($record['W'] / $games * 100) : 0;
                    @endphp
                    <div class="col-12 col-md-6 col-xl-4">
                        <a class="d-block link-underline link-underline-opacity-0 text-reset"
                            href="{{ route('competitions.show', ['competition' => $comp->id]) }}">
                            <div class="rounded rounded-3 bg-white position-relative p-4 mb-3 h-100">
                                <div class="d-flex">
                                    <div>
                                        <div class="circle-icon rounded-circle text-bg-primary text-center me-2">
                                            <span class="bi-trophy-fill"></span>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-muted small text-uppercase">{{ $comp->type }}</div>
                                        <div class="fs-5">{{ $comp->name }}</div>
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

                                <div class="text-secondary fst-italic small pt-2">
                                    {{ $comp->started_at->format('M jS') }} - {{ $comp->ended_at->format('M jS, Y') }}
                                </div>
                            </div>
                        </a>
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
@endsection
