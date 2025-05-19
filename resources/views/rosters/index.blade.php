@extends('layouts.main')

@section('body-id', 'rosters')
@section('page-title', 'Rosters')
@section('page-desc', 'Configure teams for each season.')

@section('content')
    <div class="container main-content">

        <div class="rounded rounded-3 bg-white py-2 px-3 mb-2 text-end">
            <a href="#" class="btn btn-sm btn-primary text-white" data-bs-toggle="modal" data-bs-target="#create-season">
                <span class="bi-plus-lg pe-2"></span>Add Season
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

            <ul id="seasons" class="mb-5 nav nav-underline">
            @foreach($playersBySeasonTeam as $seasonName => $teams)
                <li class="nav-item">
                    <a @class([
                        'nav-link',
                        'active' => $loop->first
                        ]) id="{{ Str::of($seasonName)->slug('-') }}-tab" data-bs-toggle="tab" 
                        data-bs-target="#{{ Str::of($seasonName)->slug('-') }}-pane" href="#">{{ $seasonName }}</a>
                </li>
            @endforeach
            </ul>

            <div id="seasons-content" class="tab-content">
        @foreach($playersBySeasonTeam as $seasonName => $teams)
                <div @class([
                    'tab-pane fade',
                    'show active' => $loop->first,
                    ]) id="{{ Str::of($seasonName)->slug('-') }}-pane" tabindex="0">
                    <h3>{{ $seasonName }}</h3>
                    <div class="d-flex flex-wrap">
                    @foreach($teams as $teamName => $players)
                        <div class="mb-5">
                            <div class="card me-3">
                                <div class="card-header">{{ $teamName }}<span class="ps-3 small">({{ count($players) }} players)</div>
                                <ul class="list-group list-group-flush">
                                @foreach($players as $p)
                                    <li class="list-group-item">
                                        <form class="row gx-3 m-0 align-items-center" action="{{ route('rosters.update', ['roster' => $p['roster_id']]) }}" method="post">
                                            @csrf
                                            <input type="hidden" name="club_team_season_id" value="{{ $p['club_team_season_id'] }}">
                                            <input type="hidden" name="player_id" value="{{ $p['id'] }}">
                                            <div class="col-auto {{ $p['class'] }}">
                                                <span class="player-number d-inline-block text-end me-1 text-info">
                                                    @if($p['number'])#{{ $p['number'] }}@endif
                                                </span>
                                                {{ $p['name'] }}
                                            @if($p['class'] != 'rem')
                                                <a href="{{ route('ajax.rosters.destroy', ['roster' => $p['roster_id']]) }}"
                                                    data-confirm-message="Are you sure you want to remove this player?" data-btn="danger"
                                                    class="rem-roster-player confirm-link link-danger position-absolute top-0 end-0 pt-2 pe-2">
                                                    <i class="bi bi-trash3"></i>
                                                </a>
                                            @endif
                                            </div>
                                        @if(empty($p['number']) && $p['class'] != 'rem')
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
</script>
@endsection
