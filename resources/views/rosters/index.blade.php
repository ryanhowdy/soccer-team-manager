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

        @foreach($playersBySeasonTeam as $seasonName => $teams)
            <h3>{{ $seasonName }}</h3>
            <div class="d-flex mb-5">
            @foreach($teams as $teamName => $players)
                <div class="flex-fill">
                    <div class="card me-3">
                        <div class="card-header">{{ $teamName }}<span class="ps-3 small">({{ count($players) }} players)</div>
                        <ul class="list-group list-group-flush">
                        @foreach($players as $p)
                            <li class="list-group-item">
                                <form class="row gx-3 m-0 align-items-center">
                                    <div class="col-auto {{ $p['class'] }}">
                                        <span class="player-number d-inline-block text-end me-1 text-info">
                                            @if($p['number'])#{{ $p['number'] }}@endif
                                        </span>
                                        {{ $p['name'] }}
                                    </div>
                                @if(empty($p['number']) && $p['class'] != 'rem')
                                    <div class="col-2">
                                        <input type="text" class="form-control form-control-sm" id="number-{{ $p['id'] }}">
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
                    </div>
                </div>
            @endforeach
            </div>
        @endforeach

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
                    <form class="row g-3" action="{{ route('seasons.store') }}" method="post">
                        @csrf
                        <div class="col-12 col-sm-6">
                            <label for="season" class="form-label">Season</label>
                            <input type="text" class="form-control" name="season" id="season" placeholder="Fall/Spring">
                        </div>
                        <div class="col-12 col-sm-6">
                            <label for="year" class="form-label">Year</label>
                            <input type="number" class="form-control" name="year" id="year" placeholder="{{ date('Y') }}">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">Create</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div><!--/.modal-->

<script>
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
