@extends('layouts.main')

@section('body-id', 'games')

@section('content')
    <div class="container main-content">

        <div class="rounded rounded-3 bg-white py-2 px-3 mb-2">
            <div class="d-flex align-items-center justify-content-end">
                <div class="pe-2">
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
                <div class="pe-2">
                    <div class="dropdown">
                        <button type="button" class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown" data-bs-auto-close="false">
                            Filter<span class="bi-filter ps-1"></span>
                        </button>
                        <div class="dropdown-menu p-3" style="width:300px">
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
                                    <select class="form-select" id="filter-clubs" name="filter-clubs">
                                        <option value="">All Clubs</option>
                                    @foreach ($teamsByClub as $clubName => $teams)
                                        <option value="{{ $teams[0]['club_id'] }}" @selected($selectedClub == $teams[0]['club_id'])>{{ $clubName }}</option>
                                    @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <select class="form-select" id="filter-teams" name="filter-teams">
                                        <option></option>
                                @foreach ($teamsByClub as $clubName => $teams)
                                    @foreach ($teamsByClub[$clubName] as $team)
                                        <option value="{{ $team['id'] }}" data-club-id="{{ $team['club_id'] }}" @selected($selectedTeam == $team['id'])>{{ $team['name'] }}</option>
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
                <div class="">
                    <div class="vr"></div>
                </div>
                <div class="ps-2">
                    <a href="#" class="btn btn-sm btn-primary text-white" data-bs-toggle="modal" data-bs-target="#create-game">
                        <span class="bi-plus-lg pe-2"></span>Add Game
                    </a>
                </div>
            </div><!--/.row-->
        </div><!--/.rounded-->

        <div class="game-listing rounded rounded-3 bg-white p-4 mb-1">
        @foreach($results as $status => $rs)
            <h3>{{ \App\Enums\ResultStatus::tryFrom($status)->name }}</h3>
            @foreach($rs as $result)
            <div class="home-v-away position-relative d-grid align-items-center justify-content-center mb-3 border p-2 rounded rounded-2">
                <div class="position-absolute top-0 start-0 small p-2">
                    {{ $result->date->inUserTimezone()->format('M j, Y') }}
                    <i class="bi bi-clock"></i>
                </div>
                <div class="position-absolute top-0 end-0 small p-2">
                    <a class="link-dark link-underline-opacity-0 link-underline-opacity-100-hover link-offset-2-hover"
                        href="{{ route('competitions.show', ['competition' => $result->competition->id]) }}">
                        <i class="bi bi-tag"></i>
                        {{ $result->competition->name }}
                    </a>
                </div>
                <div class="home-team d-flex align-items-center justify-content-end">
                    <div class="d-none d-md-block text-end pe-2">
                        <div class="club-name pe-2 text-uppercase small text-muted">{{ $result->homeTeam->club->name }}</div>
                        <div class="team-name pe-2">{{ $result->homeTeam->name }}</div>
                    </div>
                    <div class="pe-4"><img class="logo img-fluid" src="{{ asset($result->homeTeam->club->logo) }}"/></div>
                </div>
                <div class="score text-center">
                @if($status == 'D')
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
                @else
                    <a class="btn btn-sm btn-primary text-white" href="{{ route('games.preview', ['id' => $result->id]) }}">Preview</a>
                @endif
                </div>
                <div class="away-team d-flex align-items-center">
                    <div class="ps-4"><img class="logo img-fluid" src="{{ asset($result->awayTeam->club->logo) }}"/></div>
                    <div class="d-none d-md-block ps-4">
                        <div class="club-name pe-2 text-uppercase small text-muted">{{ $result->awayTeam->club->name }}</div>
                        <div class="team-name">{{ $result->awayTeam->name }}</div>
                    </div>
                </div>
            </div>
            @endforeach
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

    <div id="create-season" class="modal fade" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content py-4 px-2">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Season</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
@include('seasons.create-form')
                </div>
            </div>
        </div>
    </div>

    <div id="create-competition" class="modal fade" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content py-4 px-2">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Competition</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
@include('competitions.create-form')
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

    $('#filter-clubs').on('change', function() {
        let option = this;
        $('#filter-teams > option').hide();
        $('#filter-teams > option[data-club-id="' + option.value + '"]').show();
    });
    $('#filter-clubs').trigger('change');

    // submit create season as ajax
    $('#create-season form').on('submit', function(e) {
        e.preventDefault();

        let $form = $(this);
        let url   = $form.attr('action');

        $.ajax({
            url: url,
            type : 'POST',
            dataType: 'json',
            data : $form.serialize(),
        }).done(function(ret) {
            let season = ret.data;

            // add new season to dropdown
            let option = document.createElement('option');
            option.value = season.id;
            option.textContent = season.season_year;

            let found = false;
            $('#season_id optgroup').each(function(idx) {
                let $optGroup = $(this);
                if ($optGroup.attr('label') == season.year)
                {
                    $optGroup.append(option);
                    found = true;
                }
            });
            if (!found)
            {
                let optGroup = document.createElement('optgroup');
                optGroup.setAttribute('label', season.year);
                optGroup.append(option);
                $('#season_id').append(optGroup);
            }

            $("#season_id option:last").attr("selected", "selected");

            // close season modal
            $('#create-season').modal('hide');

            // reopen the game modal
            $('#create-game').modal('show');
        });
    });

    // submit create competition as ajax
    $('#create-competition form').on('submit', function(e) {
        e.preventDefault();

        let $form = $(this);
        let url   = $form.attr('action');

        $.ajax({
            url: url,
            type : 'POST',
            dataType: 'json',
            data : $form.serialize(),
        }).done(function(ret) {
            let comp = ret.data;

            // add new competition to dropdown
            let option = document.createElement('option');
            option.value = comp.id;
            option.textContent = comp.name + ' ' + comp.date;

            let found = false;
            $('#competition_id optgroup').each(function(idx) {
                let $optGroup = $(this);
                if ($optGroup.attr('label') == comp.type)
                {
                    $optGroup.append(option);
                    found = true;
                }
            });
            if (!found)
            {
                let optGroup = document.createElement('optgroup');
                optGroup.setAttribute('label', comp.year);
                optGroup.append(option);
                $('#competition_id').append(optGroup);
            }

            $("#competition_id option:last").attr("selected", "selected");

            // close competition modal
            $('#create-competition').modal('hide');

            // reopen the game modal
            $('#create-game').modal('show');
        });
    });
});
</script>

@endsection
