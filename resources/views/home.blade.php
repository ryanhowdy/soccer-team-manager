@extends('layouts.main')

@section('body-id', 'home')
@section('page-title', 'Home')
@section('page-desc', "Welcome to your team's home page")

@section('content')
    <div class="container main-content">

        <h2>Scheduled</h2>

    @if (count($scheduledToday))
        @include('home.scheduled-today')
    @endif

    @if (count($scheduled))
        @include('home.scheduled')
    @endif

    @if($dashboard['seasonRecord']['games'])
        <h2>Stats</h2>
        @include('home.dashboard')
    @endif

    @if($resultsByCompetition->isNotEmpty())
        <h2>Results</h2>
        <div class="row">
        @foreach($resultsByCompetition as $competition)
            @if($competition->results->isNotEmpty())
            <div class="col-12 col-lg-6">
                <div class="rounded rounded-3 bg-white p-3 p-lg-5 mb-3">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-uppercase text-secondary small">{{ $competition->type }}</div>
                            <a class="fw-bold fs-4 text-primary text-decoration-none"
                                href="{{ route('competitions.show', ['competition' => $competition->id]) }}">
                                {{ $competition->name }}
                            </a>
                        </div>
                        <div class="d-flex text-end">
                            <div class="me-3 d-none d-md-block">
                                <div class="text-uppercase text-secondary small">Win</div>
                                <div class="fw-bold fs-4 text-primary">{{ $competition->results->where('win_draw_loss', 'W')->count() }}</div>
                            </div>
                            <div class="me-3 d-none d-md-block">
                                <div class="text-uppercase text-secondary small">Draw</div>
                                <div class="fw-bold fs-4 text-primary">{{ $competition->results->where('win_draw_loss', 'D')->count() }}</div>
                            </div>
                            <div class="me-3 d-none d-md-block">
                                <div class="text-uppercase text-secondary small">Lost</div>
                                <div class="fw-bold fs-4 text-primary">{{ $competition->results->where('win_draw_loss', 'L')->count() }}</div>
                            </div>
                            <div class="me-3">
                                <div class="text-uppercase text-secondary small">Goals Diff</div>
                                <div class="fw-bold fs-4 text-primary">{{ sprintf("%+d", $competition->results->sum('us_goals') - $competition->results->sum('them_goals')) }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="game-listing-small rounded rounded-3 mt-4">
                    @foreach($competition->results as $result)
                        <a href="{{ route('games.show', ['id' => $result->id]) }}"
                            class="mb-3 text-decoration-none rounded rounded-2 text-dark">
                            <div class="small text-center text-secondary">{{ $result->date->inUserTimezone()->format('M j, Y') }}</div>
                            <div class="home-v-away d-grid align-items-center justify-content-center mb-3">
                                <div class="home-team d-flex align-items-center justify-content-end">
                                    <div class="me-2 d-none d-lg-block">{{ $result->homeTeam->name }}</div>
                                    <img class="logo img-fluid me-2 me-md-1 me-lg-0" data-bs-toggle="tooltip" data-bs-title="{{ $result->homeTeam->club->name }}"
                                        src="{{ asset($result->homeTeam->club->logo) }}" onerror="this.onerror=null;this.src='{{ asset('img/logo_none.png') }}';"/>
                                </div>
                                <div class="score text-center">
                                    <span @class([
                                        'badge',
                                        'rounded-pill',
                                        'text-white',
                                        'bg-success' => ($result->win_draw_loss == 'W'),
                                        'bg-warning' => ($result->win_draw_loss == 'D'),
                                        'bg-danger' => ($result->win_draw_loss == 'L'),
                                    ])>{{ $result->home_team_score }} - {{ $result->away_team_score }}</span>
                                </div>
                                <div class="away-team d-flex align-items-center">
                                    <img class="logo img-fluid ms-2 ms-md-1 ms-lg-0" data-bs-toggle="tooltip" data-bs-title="{{ $result->awayTeam->club->name }}"
                                        src="{{ asset($result->awayTeam->club->logo) }}" onerror="this.onerror=null;this.src='{{ asset('img/logo_none.png') }}';"/>
                                    <div class="ms-2 d-none d-lg-block">{{ $result->awayTeam->name }}</div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                    </div>
                </div>
            </div>
            @endif
        @endforeach
        </div>
    @else
        <div class="rounded rounded-3 bg-white p-5 text-center mb-3">
            <img class="opacity-50 w-50" src="{{ asset('img/empty-state.svg') }}">
            <div class="fw-bold mt-5 pb-1 text-secondary">No Stats</div>
            <small class="pb-3 d-block text-secondary">Either no games have been played yet, or no stats have been entered for these games.</small>
        </div>
    @endif

    </div><!--/container-->
@endsection
