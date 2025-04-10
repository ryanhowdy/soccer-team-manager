<form method="post" action="{{ $action }}">
    @csrf
    <div class="mb-3">
        <div class="d-flex justify-content-between">
            <label class="form-label" for="season_id">Season</label>
            <a href="#" class="smaller lh-lg link-secondary link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover"
                data-bs-toggle="modal" data-bs-target="#create-season">Add Season</a>
        </div>
        <select class="form-select" id="season_id" name="season_id">
    @foreach ($seasons as $i => $season)
        @if ($loop->first)
            <optgroup label="{{ $season->year }}">
        @else
            @if ($seasons[$i]->year !== $seasons[$i-1]->year)
            <optgroup label="{{ $season->year }}">
            @endif
        @endif
            <option value="{{ $season->id }}" @if($loop->last) selected @endif>{{ $season->season }} {{ $season->year }}</option>
    @endforeach
        </select>
    </div>
    <div class="mb-3">
        <div class="d-flex justify-content-between">
            <label class="form-label" for="competition_id">Competition</label>
            <a href="#" class="smaller lh-lg link-secondary link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover"
                data-bs-toggle="modal" data-bs-target="#create-competition">Add Competition</a>
        </div>
        <select class="form-select" id="competition_id" name="competition_id">
            <option></option>
    @foreach ($competitions as $type => $comps)
            <optgroup label="{{ $type }}">
        @foreach ($comps as $competition)
            <option value="{{ $competition->id }}">
                {{ $competition->name }} - {{ $competition->started_at->format('M j, Y') }}
            </option>
        @endforeach
    @endforeach
        </select>
    </div>
    <div class="mb-3">
        <div class="d-flex justify-content-between">
            <label class="form-label" for="location_id">Location</label>
            <a href="#" class="smaller lh-lg link-secondary link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover">Add Location</a>
        </div>
        <select class="form-select" id="location_id" name="location_id">
    @foreach ($locations as $type => $locs)
            <optgroup label="{{ $type }}">
        @foreach ($locs as $location)
            <option value="{{ $location->id }}">{{ $location->name }}</option>
        @endforeach
    @endforeach
        </select>
    </div>
    <div class="row align-items-start mb-3">
        <div class="col-auto">
            <label class="form-label" for="date">Date</label>
            <input type="date" class="form-control" id="date" name="date" value="{{ date('Y-m-d') }}">
        </div>
        <div class="col-auto">
            <label for="time" class="col-sm-2 col-form-label">Time</label>
            <input type="time" class="form-control" id="time" name="time">
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label" for="my_team_id">Team</label>
        <select class="form-select" id="my_team_id" name="my_team_id">
    @foreach ($managedTeams as $i => $team)
            <option value="{{ $team->id }}">{{ $team->name }}</option>
    @endforeach
        </select>
    </div>
    <div class="mb-3">
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" checked name="my_home_away" id="home" value="home">
            <label class="form-check-label small" for="home">Home</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="my_home_away" id="away" value="away">
            <label class="form-check-label small" for="away">Away</label>
        </div>
    </div>
    <div class="mb-3">
        <div class="d-flex justify-content-between">
            <label class="form-label" for="opponent_team_id">Opponent</label>
            <a href="#" class="smaller lh-lg link-secondary link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover">Add Team</a>
        </div>
        <div>
            <select class="form-select" style="width:100%" id="opponent_team_id" name="opponent_team_id">
        @foreach ($teamsByClub as $clubName => $teams)
                <optgroup label="{{ $clubName }}">
            @foreach ($teamsByClub[$clubName] as $team)
                <option value="{{ $team['id'] }}">{{ $team['name'] }}</option>
            @endforeach
        @endforeach
            </select>
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Submit</button>
</form>
