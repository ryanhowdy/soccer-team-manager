@extends('layouts.main')

@section('body-id', 'games')
@section('page-title', 'Games')
@section('page-desc', "See the scores of past games")

@section('content')
    <div class="container main-content">

        <div class="rounded rounded-3 bg-white py-2 px-3 mb-2">
            <div class="row row-cols-md-auto gx-2 align-items-center justify-content-end">
                <div class="col-12 d-inline-flex gap-1">
                    <div class="dropdown">
                        <button type="button" class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown" data-bs-auto-close="false">
                            Sort<span class="bi-chevron-expand ps-1"></span>
                        </button>
                        <div class="dropdown-menu p-3">
                            <button type="button" class="btn btn-secondary">
                                <span class="bi-sort-down"></span>
                            </button>
                            <button type="button" class="btn btn-secondary">
                                <span class="bi-sort-up"></span>
                            </button>
                        </div>
                    </div><!--/.dropdown-->
                </div>
                <div class="col-12 d-inline-flex gap-1">
                    <div class="dropdown">
                        <button type="button" class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown" data-bs-auto-close="false">
                            Filter<span class="bi-filter ps-1"></span>
                        </button>
                        <div class="dropdown-menu p-3">
                            <form id="filter" class="">
                                <div class="mb-3">
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
                                <div class="mb-3">
                                    <select class="form-select search-select" style="width:100%" id="filter-teams" name="filter-teams">
                                        <option value="">All Teams</option>
                                @foreach ($teamsByClub as $clubName => $teams)
                                        <optgroup label="{{ $clubName }}">
                                    @foreach ($teamsByClub[$clubName] as $team)
                                        <option value="{{ $team['id'] }}" @selected($selectedTeam == $team['id'])>{{ $team['name'] }}</option>
                                    @endforeach
                                @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <button type="submit" class="btn btn-secondary">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div><!--/.dropdown-->
                </div>
                <div class="col-12">
                    <div class="vr"></div>
                </div>
                <div class="col-12">
                    <a href="#" class="btn btn-sm btn-primary text-white" data-bs-toggle="modal" data-bs-target="#create-game">
                        <span class="bi-plus-lg pe-2"></span>Add Game
                    </a>
                </div>
            </div><!--/.row-->
        </div><!--/.rounded-->

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


    <div id="create-game" class="modal fade" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content py-4 px-2">
                <div class="modal-header">
                    <h5 class="modal-title">Schedule New Game</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
@include('games.create-form')
                </div>
            </div>
        </div>
    </div>

<script>
$(document).ready(function() {
    $('#opponent_team_id').select2({
        dropdownParent: $('#create-game'),
        matcher:optgroupMatcher
    });
});
</script>

@endsection
