@extends('layouts.main')

@section('body-id', 'games')
@section('page-title', 'Games')
@section('page-desc', "See the scores of past games")

@section('content')
    <div class="container main-content">

        <form id="filter" class="row row-cols-md-auto gx-3 align-items-center justify-content-end">
            <div class="col-12 mb-3">
                <select class="form-select" id="filter-seasons" name="filter-seasons">
                    <option value="">All Seasons</option>
            @foreach ($seasons as $i => $season)
                @if ($loop->first)
                    <optgroup label="{{ $season->year }}">
                @else
                    @if ($seasons[$i]->year !== $seasons[$i-1]->year)
                    <optgroup label="{{ $season->year }}">
                    @endif
                @endif
                    <option value="{{ $season->id }}" @selected($selectedSeason == $season->id)>{{ $season->season }} {{ $season->year }}</option>
            @endforeach
                </select>
            </div>
            <div class="col-12 mb-3">
                <select class="form-select search-select" id="filter-teams" name="filter-teams">
                    <option value="">All Teams</option>
            @foreach ($teamsByClub as $clubName => $teams)
                    <optgroup label="{{ $clubName }}">
                @foreach ($teamsByClub[$clubName] as $team)
                    <option value="{{ $team['id'] }}" @selected($selectedTeam == $team['id'])>{{ $team['name'] }}</option>
                @endforeach
            @endforeach
                </select>
            </div>
            <div class="col-12 mb-3">
                <button type="submit" class="btn btn-info">Filter</button>
            </div>
        </form>

        <div class="game-listing rounded rounded-3 bg-white p-4 mb-1">
        @foreach($results as $result)
            <div class="home-v-away position-relative d-grid align-items-center justify-content-center mb-3 border p-2 rounded rounded-2">
                <div class="position-absolute top-0 start-0 small p-2">
                    {{ $result->date->inUserTimezone()->format('M j, Y') }}
                    <i class="bi bi-clock"></i>
                </div>
                <div class="position-absolute top-0 end-0 small p-2">
                    <i class="bi bi-tag"></i>
                    {{ $result->competition->name }}
                </div>
                <div class="home-team d-flex align-items-center justify-content-end">
                    <div class="me-4 text-center w-25">
                        <img class="logo img-fluid" src="{{ asset($result->homeTeam->club->logo) }}"/>
                        <div>{{ $result->homeTeam->name }}</div>
                    </div>
                </div>
                <div class="score text-center">
                    <a href="{{ route('games.show', ['id' => $result->id]) }}">
                        <span @class([
                            'badge',
                            'rounded-pill',
                            'text-white',
                            'bg-success'   => ($result->win_draw_loss == 'W'),
                            'bg-secondary' => ($result->win_draw_loss == 'D'),
                            'bg-danger' => ($result->win_draw_loss == 'L'),
                        ])>{{ $result->home_team_score }} - {{ $result->away_team_score }}</span>
                    </a>
                </div>
                <div class="away-team d-flex align-items-center">
                    <div class="ms-4 text-center w-25">
                        <img class="logo img-fluid" src="{{ asset($result->awayTeam->club->logo) }}"/>
                        <div>{{ $result->awayTeam->name }}</div>
                    </div>
                </div>
            </div>
        @endforeach
        </div>

    </div><!--/container-->
@endsection
