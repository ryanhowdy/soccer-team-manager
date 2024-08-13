<form method="post" action="{{ $action }}" enctype="multipart/form-data">
    @csrf
    <div class="h4 mb-3">Pick a team</div>
    <div class="mb-3">
        <select class="form-select" id="club_team_id" name="club_team_id">
            <option></option>
    @foreach ($managedTeams as $team)
            <option value="{{ $team->id }}">{{ $team->name }}</option>
    @endforeach
        </select>
    </div>

    <div class="h4 mt-4 mb-3">New or existing?</div>
    <div class="d-flex justify-content-between align-items-stretch mb-3">
        <div class="rounded bg-light p-5">
            <div class="mb-3">
                <label class="form-label" for="name">Name</label>
                <input type="text" class="form-control" id="name" name="name" placeholder="Player Name">
            </div>
            <div class="mb-3">
                <label class="form-label" for="name">Nickname</label>
                <input type="text" class="form-control" id="nickname" name="nickname">
            </div>
            <div class="mb-3">
                <label class="form-label" for="photo">Photo</label>
                <input type="file" class="form-control" id="photo" name="photo">
            </div>
            <div class="mb-3">
                <label class="form-label" for="birth_year">Birth Year</label>
                <input type="number" class="form-control" id="birth_year" name="birth_year">
            </div>
        </div>
        <div class="">
            <div class="vr h-100"></div>
        </div>
        <div class="p-5">
            <div class="mb-3">
                <label class="form-label" for="player_id">Existing Players</label>
                <div style="min-width:200px">
                    <select class="form-select" style="width:100%" id="player_id" name="player_id">
                        <option></option>
                @foreach ($allPlayers as $player)
                        <option value="{{ $player->id }}">{{ $player->name }} ({{ $player->birth_year }})</option>
                @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Submit</button>
</form>
