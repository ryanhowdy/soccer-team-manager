@extends('layouts.main')

@section('body-id', 'game')

@section('content')
    <div class="container main-content">

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

        <div class="rounded rounded-3 bg-white p-4 mb-3">
            <form method="post" style="max-width:500px" action="{{ route('games.update', ['id' => $result->id]) }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="season_id">Season</label>
                    <select class="form-select" id="season_id" name="season_id">
                @foreach ($seasons as $i => $season)
                    @if ($loop->first)
                        <optgroup label="{{ $season->year }}">
                    @else
                        @if ($seasons[$i]->year !== $seasons[$i-1]->year)
                        <optgroup label="{{ $season->year }}">
                        @endif
                    @endif
                        <option value="{{ $season->id }}" @selected($result->season_id == $season->id)>{{ $season->season }} {{ $season->year }}</option>
                @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="competition_id">Competition</label>
                    <select class="form-select" id="competition_id" name="competition_id">
                        <option></option>
                @foreach ($competitions as $type => $comps)
                        <optgroup label="{{ $type }}">
                    @foreach ($comps as $competition)
                        <option @selected($result->competition_id == $competition->id) value="{{ $competition->id }}">
                            {{ $competition->name }} - {{ $competition->started_at->format('M j, Y') }}
                        </option>
                    @endforeach
                @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="location_id">Location</label>
                    <select class="form-select" id="location_id" name="location_id">
                    @foreach ($locations as $location)
                        <option @selected($result->location_id == $location->id) value="{{ $location->id }}">{{ $location->name }}</option>
                    @endforeach
                    </select>
                </div>
                <div class="row align-items-start mb-3">
                    <div class="col-auto">
                        <label class="form-label" for="date">Date</label>
                        <input type="date" class="form-control" id="date" name="date" value="{{ $result->date->inUserTimezone()->format('Y-m-d') }}">
                    </div>
                    <div class="col-auto">
                        <label class="form-label" for="time">Time</label>
                        <input type="time" class="form-control" id="time" name="time" value="{{ $result->date->inUserTimezone()->format('H:i') }}">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="my_team_id">Team</label>
                    <select class="form-select" id="my_team_id" name="my_team_id">
                    @foreach ($managedTeams as $i => $team)
                        <option @selected($result->{$goodGuys . 'Team'}->id == $team->id) value="{{ $team->id }}">{{ $team->name }}</option>
                    @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" @checked($goodGuys == 'home') name="my_home_away" id="home" value="home">
                        <label class="form-check-label small" for="home">Home</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" @checked($goodGuys == 'away') name="my_home_away" id="away" value="away">
                        <label class="form-check-label small" for="away">Away</label>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="opponent_team_id">Opponent</label>
                    <div>
                        <select class="form-select" style="width:100%" id="opponent_team_id" name="opponent_team_id">
                    @foreach ($teamsByClub as $clubName => $teams)
                            <optgroup label="{{ $clubName }}">
                        @foreach ($teamsByClub[$clubName] as $team)
                            <option @selected($result->{$badGuys . 'Team'}->id == $team['id']) value="{{ $team['id'] }}">{{ $team['name'] }}</option>
                        @endforeach
                    @endforeach
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="status">Status</label>
                    <select class="form-select" id="status" name="status">
                    @foreach (\App\Enums\ResultStatus::cases() as $status)
                        <option @selected($result->status == $status->value) value="{{ $status->value }}">{{ $status->name }}</option>
                    @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary text-white">Submit</button>
                <a href="{{ route('teams.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
@endsection
