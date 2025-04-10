<form method="post" action="{{ route('competitions.store') }}">
    @csrf
    <div class="mb-3">
        <label class="form-label" for="club_team_id">Team</label>
        <select class="form-select" id="club_team_id" name="club_team_id">
            <option></option>
    @foreach ($managedTeams as $team)
            <option value="{{ $team->id }}">{{ $team->name }}</option>
    @endforeach
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label" for="rank">Type</label>
        <select class="form-select" id="type" name="type">
            <option value="Cup">Cup</option>
            <option value="League">League</option>
            <option value="Friendly">Friendly</option>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label" for="name">Name</label>
        <input type="text" class="form-control" id="name" name="name">
    </div>
    <div class="mb-3">
        <label class="form-label" for="division">Division</label>
        <input type="text" class="form-control" id="division" name="division" placeholder="U-13">
    </div>
    <div class="row align-items-start mb-3">
        <div class="col">
            <label class="form-label" for="level">Current Level</label>
            <input type="number" class="form-control" id="level" name="level">
        </div>
        <div class="col">
            <label class="form-label" for="total_levels">Total Levels</label>
            <input type="number" class="form-control" id="total_levels" name="total_levels">
        </div>
    </div>
    <div class="row align-items-start mb-3">
        <div class="col-auto">
            <label class="form-label" for="started_at">Start Date</label>
            <input type="date" class="form-control" id="started_at" name="started_at" value="{{ date('Y-m-d H:00') }}">
        </div>
        <div class="col-auto">
            <label class="form-label" for="ended_at">End Date</label>
            <input type="date" class="form-control" id="ended_at" name="ended_at">
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label" for="website">Website</label>
        <input type="text" class="form-control" id="website" name="website" placeholder="https://www.my-competition.com/u13">
    </div>
    <div class="mb-3">
        <label for="notes">Notes</label>
        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Submit</button>
</form>

