<form method="post" action="{{ $action }}">
    @csrf
    <div class="mb-3">
        <div class="d-flex justify-content-between">
            <label class="form-label" for="season_id">Season</label>
            <a href="#" class="smaller lh-lg link-secondary link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover"
                data-bs-toggle="modal" data-bs-target="#create-season">New Season</a>
        </div>
        @php
            // Open on the season the page is filtered to, which is the one
            // remembered in the session.  "All Seasons" ($selectedSeason === null)
            // isn't something you can schedule into, so fall back to the newest —
            // ids are chronological, so the highest is the newest.
            $defaultSeasonId = old('season_id', ($selectedSeason ?? null)?->id ?? $seasons->max('id'));
            $prevYear        = null;
        @endphp
        <select class="form-select" id="season_id" name="season_id">
    @foreach ($seasons as $season)
        @if ($prevYear !== $season->year)
            <optgroup label="{{ $season->year }}">
        @endif
            <option value="{{ $season->id }}" @selected($season->id == $defaultSeasonId)>{{ $season->season_year }}</option>
        @php $prevYear = $season->year; @endphp
    @endforeach
        </select>
    </div>
    <div class="mb-3">
        <div class="d-flex justify-content-between">
            <label class="form-label" @if($competitions->isNotEmpty()) for="competition_id" @endif>Competition</label>
            <a href="#" class="smaller lh-lg link-secondary link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover"
                data-bs-toggle="modal" data-bs-target="#create-competition">Add Competition</a>
        </div>
        {{-- Active competitions for the selected team only (see GameController@index).
             With none there's nothing to pick, so drop the empty select entirely and
             leave the message plus the Add Competition link above it. --}}
    @if ($competitions->isNotEmpty())
        <select class="form-select" id="competition_id" name="competition_id">
            <option></option>
        @foreach ($competitions as $type => $comps)
            <optgroup label="{{ $type }}">
            @foreach ($comps as $competition)
            <option value="{{ $competition->id }}" data-club-team-id="{{ $competition->club_team_id }}" @selected(old('competition_id') == $competition->id)>
                {{ $competition->name }} - {{ $competition->division }} - {{ $competition->started_at->format('M j, Y') }}
            </option>
            @endforeach
        @endforeach
        </select>
    @else
        <div class="form-text mt-0">No active competitions for this team yet — add one first.</div>
    @endif
    </div>
    <div class="mb-3">
        <div class="d-flex justify-content-between">
            <label class="form-label" for="location_id">Location</label>
            <a href="#" class="smaller lh-lg link-secondary link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover"
                data-bs-toggle="modal" data-bs-target="#create-location">Add Location</a>
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
        {{-- The competition list above is scoped to the selected team, so start on
             that team rather than on whichever one sorts first --}}
        @php
            $defaultTeamId = old('my_team_id', auth()->user()->selected_club_team_id);
        @endphp
        <select class="form-select" id="my_team_id" name="my_team_id">
    @foreach ($managedTeams as $i => $team)
            <option value="{{ $team->id }}" @selected($team->id == $defaultTeamId)>{{ $team->club->name }}: {{ $team->name }} {{ $team->birth_year }}</option>
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
                <option value="{{ $team['id'] }}">{{ $team['name'] }} {{ $team['birth_year'] }}</option>
            @endforeach
        @endforeach
            </select>
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Submit</button>
</form>
