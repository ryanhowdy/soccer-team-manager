@extends('layouts.main')

@section('body-id', 'rosters')
@section('page-title', 'Rosters')
@section('page-desc', 'Configure teams for each season.')

@section('content')
    <div class="container main-content">

        <div class="d-flex justify-content-between mb-3 align-items-center">
            <div><h2>Rosters</h2></div>
            <div class="d-flex gap-2 align-items-center justify-content-end">
                <div class="pe-2">
                    <div class="dropdown" id="seasons-dropdown">
                        <button type="button" class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown">
                            <span id="selected-season">{{ array_key_first($playersBySeasonTeam) ?? 'Season' }}</span>
                        </button>
                        <ul class="dropdown-menu">
                        @foreach($playersBySeasonTeam as $seasonName => $teams)
                            <li>
                                <a @class([
                                    'dropdown-item',
                                    'active' => $loop->first,
                                    ]) data-bs-target="#{{ Str::of($seasonName)->slug('-') }}-pane" href="#">{{ $seasonName }}</a>
                            </li>
                        @endforeach
                        </ul>
                    </div><!--/.dropdown-->
                </div>
            @can('edit things')
                <div class=""><div class="vr"></div></div>
                <div class="ps-2">
                    <a href="#" class="btn btn-sm btn-dark text-white rounded-pill py-2 px-3" data-bs-toggle="modal" data-bs-target="#create-season">
                        <span class="bi-plus-lg pe-0 pe-lg-2"></span><span class="d-none d-lg-inline-block">Add Season</span>
                    </a>
                </div>
            @endcan
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

            <div id="seasons-content" class="tab-content">
        @foreach($playersBySeasonTeam as $seasonName => $groups)
                <div @class([
                    'tab-pane fade',
                    'show active' => $loop->first,
                    ]) id="{{ Str::of($seasonName)->slug('-') }}-pane" tabindex="0">
                    <h3>{{ $seasonName }}</h3>
                    <div class="d-flex flex-wrap">
                @foreach($groups as $teamName => $teams)
                        <div class="mb-5">
                            <div class="card me-3">
                                <div class="card-header">
                                    {{ $teamName }}<span class="ps-3 small">({{ count($teams['team']) }} players)
                        @can('edit things')
                            @empty($teams['team'])
                                    <div class="float-end">
                                        <a href="{{ route('ajax.club-team-seasons.destroy', ['season' => $clubTeamSeasonLkup[$seasonName . '-' . $teamName]]) }}"
                                            data-confirm-message="Are you sure you want to remove this Team's Season?" data-btn="danger"
                                            class="confirm-link link-danger">
                                            <i class="bi bi-trash3"></i>
                                        </a>
                                    </div>
                            @endempty
                        @endcan
                                </div>
                                <ul class="list-group list-group-flush">
                        @foreach($teams as $g => $players)
                                @foreach($players as $p)
                                    <li class="list-group-item">
                                        <form class="row gx-3 m-0 align-items-center" action="{{ route('rosters.update', ['roster' => $p['roster_id']]) }}" method="post">
                                            @csrf
                                            <input type="hidden" name="club_team_season_id" value="{{ $p['club_team_season_id'] }}">
                                            <input type="hidden" name="player_id" value="{{ $p['id'] }}">
                                            <div class="col-auto {{ $p['class'] }}">
                                                <span class="player-number d-inline-block text-end me-1 text-info">
                                                    @if(is_int($p['number']))#{{ $p['number'] }}@endif
                                                </span>
                                                <a class="link-dark link-underline-opacity-0 link-underline-opacity-100-hover link-offset-2-hover"
                                                    href="{{ route('players.show', ['player' => $p['id']]) }}">{{ $p['name'] }}</a>
                                            @if($p['class'] != 'rem')
                                                <a href="{{ route('ajax.rosters.destroy', ['roster' => $p['roster_id']]) }}"
                                                    data-confirm-message="Are you sure you want to remove this player?" data-btn="danger"
                                                    class="rem-roster-player confirm-link link-danger position-absolute top-0 end-0 pt-2 pe-2">
                                                    <i class="bi bi-trash3"></i>
                                                </a>
                                            @endif
                                            </div>
                                        @if(!is_int($p['number']) && $p['class'] != 'rem')
                                            <div class="col-2">
                                                <input type="text" class="form-control form-control-sm" name="number">
                                            </div>
                                            <div class="col-auto">
                                                <button type="submit" class="btn btn-sm btn-light">Save</button>
                                            </div>
                                        @endif
                                        </form>
                                    </li>
                                @endforeach
                        @endforeach
                                    <li class="list-group-item">
                                        <form class="row gx-3 m-0 align-items-center">
                                            <div class="col-2">
                                                <input type="text" class="form-control form-control-sm add-number">
                                            </div>
                                            <div class="col-auto">
                                                <select class="form-select add-player" data-club-team-season-id="{{ $clubTeamSeasonLkup[$seasonName . '-' . $teamName] }}">
                                                    <option>Add Player</option>
                                                @foreach($availablePlayersBySeasonTeam[$seasonName][$teamName] as $p)
                                                    <option value="{{ $p['id'] }}">{{ $p['name'] }}</option>
                                                @endforeach
                                                </select>
                                            </div>
                                        </form>
                                    </li>
                                </ul>
                            </div><!--/.card-->
                        </div>
                @endforeach
                @if(!empty($availableTeamsBySeason[$seasonName]))
                        <div class="mb-5">
                            <div class="card me-3">
                                <div class="card-header">Add Team</div>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item">
                                        <select class="form-select add-team" data-season-id="{{ $seasonLkup[$seasonName] }}">
                                            <option value="">Add Team</option>
                                        @foreach($availableTeamsBySeason[$seasonName] as $t)
                                            <option value="{{ $t['id'] }}">{{ $t['name'] }}</option>
                                        @endforeach
                                        </select>
                                    </li>
                                </ul>
                            </div><!--/.card-->
                        </div>
                @endif
                    </div><!--/.flex-wrap-->
                </div><!--/.tab-pane-->
        @endforeach
            </div><!--/#seasons-content-->

        </div><!--/rounded-->
    </div><!--/container-->

    <div id="create-season" class="modal modal-lg fade" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    Create New Season
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
@include('seasons.create-form')
                </div>
            </div>
        </div>
    </div><!--/.modal-->

<script>
let confirmation = new ConfirmModal();

$('#seasons-dropdown .dropdown-item').on('click', function(e) {
    e.preventDefault();

    let $item  = $(this);
    let target = $item.attr('data-bs-target');

    // Show the selected season's pane
    $('#seasons-content .tab-pane').removeClass('show active');
    $(target).addClass('show active');

    // Reflect the selection in the dropdown and its button label
    $('#seasons-dropdown .dropdown-item').removeClass('active');
    $item.addClass('active');
    $('#selected-season').text($item.text());
});

$('.add-player').on('change', function() {
    let $select          = $(this);
    let playerId         = $select.val();
    let clubTeamSeasonId = $select.data('clubTeamSeasonId');
    let number           = $select.closest('form').find('.add-number').val();

    let data = {
        club_team_season_id : clubTeamSeasonId,
        player_id           : playerId,
    };

    if (number)
    {
        data.number = number;
    }

    $.ajax({
        url: '{{ route('ajax-create-roster') }}',
        type : 'POST',
        data : data,
    }).done(function(ret) {
        location.reload();
    });
});

$('.add-team').on('change', function() {
    let $select    = $(this);
    let clubTeamId = $select.val();
    let seasonId   = $select.data('seasonId');

    if (!clubTeamId)
    {
        return;
    }

    $.ajax({
        url: '{{ route('ajax.club-team-seasons.store') }}',
        type : 'POST',
        data : {
            season_id    : seasonId,
            club_team_id : clubTeamId,
        },
    }).done(function(ret) {
        location.reload();
    });
});
</script>
@endsection
