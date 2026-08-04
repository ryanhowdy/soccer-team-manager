@extends('layouts.main')

@section('body-id', 'rosters')
@section('page-title', 'Roster')
@section('page-desc', "Manage the selected team's roster for each season.")

@section('content')
    <div class="container main-content">

        <div class="d-flex justify-content-between mb-3 align-items-center">
            <div>
                <h2 class="mb-0">Roster</h2>
                <div class="text-muted small">{{ $selectedTeam->club->name }}: {{ $selectedTeam->name }}</div>
            </div>
            <div class="d-flex gap-2 align-items-center justify-content-end">
                <div>
                    @include('partials.season-filter', ['filterRoute' => 'rosters.index', 'allowAll' => false])
                </div>
            @can('edit things')
                <div><div class="vr"></div></div>
                <div>
                    <a href="#" class="btn btn-sm btn-dark text-white rounded-pill py-2 px-3" data-bs-toggle="modal" data-bs-target="#create-season">
                        <span class="bi-plus-lg pe-0 pe-lg-2"></span><span class="d-none d-lg-inline-block">New Season</span>
                    </a>
                    <a href="#" class="btn btn-sm btn-dark text-white rounded-pill py-2 px-3" data-bs-toggle="modal" data-bs-target="#create-player">
                        <span class="bi-plus-lg pe-0 pe-lg-2"></span><span class="d-none d-lg-inline-block">Add Team Player</span>
                    </a>
                </div>
            @endcan
            </div>
        </div>

        <div class="rounded rounded-3 bg-white position-relative p-4 mb-3">

        @if ($errors->any())
            <div class="alert alert-danger mt-3">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($seasons->isEmpty())
            <div class="text-center py-4">
                <p class="mb-3 text-muted">No seasons exist yet. Create a season to start building a roster.</p>
            @can('edit things')
                <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#create-season">New Season</a>
            @endcan
            </div>
        @elseif(!$clubTeamSeason)
            <div class="text-center py-4">
                <p class="mb-3">{{ $selectedTeam->name }} isn't set up for <strong>{{ $selectedSeason->season_year }}</strong> yet.</p>
            @can('edit things')
                <button id="activate-season" class="btn btn-primary"
                    data-season-id="{{ $selectedSeason->id }}" data-club-team-id="{{ $selectedTeam->id }}">
                    Add {{ $selectedSeason->season_year }} to this team
                </button>
            @endcan
            </div>
        @else
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Player</th>
                        <th>#</th>
                        <th>Positions</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class="table-group-divider">
                @forelse($rosterPlayers as $r)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div><img src="/{{ $r->player->photo }}" class="img-fluid rounded-circle" style="width:50px"/></div>
                                <div class="ps-2">
                                    <a class="d-inline-block text-decoration-none" href="{{ route('players.show', ['player' => $r->player->id]) }}">
                                        {{ $r->player->name }}
                                    </a>
                                @if($r->player->managed)
                                    <div class="fw-bold fst-italic small">* Managed</div>
                                @endif
                                </div>
                            </div>
                        </td>
                        <td>
                        @if(is_null($r->number))
                            @can('edit things')
                            <form class="d-flex gap-2 align-items-center" action="{{ route('rosters.update', ['roster' => $r->id]) }}" method="post">
                                @csrf
                                <input type="hidden" name="club_team_season_id" value="{{ $clubTeamSeason->id }}">
                                <input type="hidden" name="player_id" value="{{ $r->player_id }}">
                                <input type="text" class="form-control form-control-sm" name="number" style="width:60px">
                                <button type="submit" class="btn btn-sm btn-light">Save</button>
                            </form>
                            @endcan
                        @else
                            <span class="fw-bold text-info">#{{ $r->number }}</span>
                        @endif
                        </td>
                        <td>
                        @foreach($r->player->positions as $pos)
                            <span class="pe-2 fw-bold">
                                {{ $pos->position_name }}@can('edit things')<a href="{{ route('ajax.player-positions.destroy', ['playerPosition' => $pos->id]) }}"
                                    data-confirm-message="Are you sure you want to remove this position from this player?"
                                    data-btn="danger" class="confirm-link link-danger"><span class="bi-x"></span></a>@endcan
                            </span>
                        @endforeach
                        @can('edit things')
                            <select class="position form-select form-select-sm w-auto d-inline-block" data-id="{{ $r->player->id }}">
                                <option></option>
                            @foreach($positions as $pos)
                                <option value="{{ $pos->id }}">{{ $pos->position }}</option>
                            @endforeach
                            </select>
                        @endcan
                        </td>
                        <td class="text-end">
                        @can('edit things')
                            <a href="{{ route('ajax.rosters.destroy', ['roster' => $r->id]) }}"
                                data-confirm-message="Are you sure you want to remove this player from the roster?"
                                data-btn="danger" class="rem-roster-player confirm-link link-danger"
                                title="Remove from roster" aria-label="Remove from roster">
                                <i class="bi bi-trash3"></i>
                            </a>
                        @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-muted">No players on this roster yet. Add a team player below.</td>
                    </tr>
                @endforelse
                @can('edit things')
                    <tr>
                        <td>
                            <div class="d-flex gap-2 align-items-center">
                                <input type="text" class="form-control form-control-sm add-number" style="width:60px" placeholder="#">
                                <select class="add-player form-select form-select-sm w-auto d-inline-block" data-club-team-season-id="{{ $clubTeamSeason->id }}">
                                    <option value="">Add to roster&hellip;</option>
                                @foreach($availablePlayers as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                                </select>
                            </div>
                        </td>
                        <td colspan="3"></td>
                    </tr>
                @endcan
                </tbody>
            </table>
        @endif

        </div><!--/rounded-->
    </div><!--/container-->

    <div id="create-season" class="modal modal-lg fade" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    New Season
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
@include('seasons.create-form')
                </div>
            </div>
        </div>
    </div><!--/.modal-->

    <div id="create-player" class="modal fade" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content py-4 px-2">
                <div class="modal-header">
                    <h5 class="modal-title">Add Player to Team</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
@include('players.create-form')
                </div>
            </div>
        </div>
    </div><!--/.modal-->

<script>
let confirmation = new ConfirmModal();

$(document).ready(function() {
    $('#player_id').select2({
        placeholder: 'Choose',
        dropdownParent: $('#create-player'),
        allowClear: true,
        matcher: optgroupMatcher
    });
});

// Activate the selected season for this team (creates the club_team_season)
$('#activate-season').on('click', function() {
    let $btn = $(this);

    $.ajax({
        url: '{{ route('ajax.club-team-seasons.store') }}',
        type: 'POST',
        data: {
            season_id    : $btn.data('seasonId'),
            club_team_id : $btn.data('clubTeamId'),
        },
    }).done(function() {
        location.reload();
    });
});

// Add a position to a player (inline)
$('select.position').on('change', function(e) {
    let $input     = $(this);
    let playerId   = $input.data('id');
    let positionId = $input.val();
    let position   = $input.find('option:selected').text();

    if (!positionId)
    {
        return;
    }

    $.ajax({
        url: '{{ route('ajax-create-player-position') }}',
        type: 'POST',
        data: {
            player_id   : playerId,
            position_id : positionId,
        },
    }).done(function(ret) {
        let $td = $input.parent('td');

        $td.prepend('<span class="pe-2 fw-bold">' + position + '</span>');

        $input.find('option:selected').prop('selected', false);
    });
});

// Add a player to this season's roster
$('.add-player').on('change', function() {
    let $select          = $(this);
    let playerId         = $select.val();
    let clubTeamSeasonId = $select.data('clubTeamSeasonId');
    let number           = $select.closest('tr').find('.add-number').val();

    if (!playerId)
    {
        return;
    }

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
        type: 'POST',
        data: data,
    }).done(function() {
        location.reload();
    });
});
</script>
@endsection
