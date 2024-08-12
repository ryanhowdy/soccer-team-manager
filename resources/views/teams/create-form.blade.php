<form method="post" action="{{ $createTeamAction }}">
    @csrf
    <div class="row mb-3">
        <div class="col">
            <label class="form-label" for="club_id">Club</label>
            <select class="form-select" id="club_id" name="club_id">
        @foreach ($clubs as $type => $club)
                <option value="{{ $club->id }}">{{ $club->name }}</option>
        @endforeach
            </select>
        </div>
        <div class="col-auto" style="margin-top:32.09px">
            <a href="#" class="btn btn-link" data-bs-toggle="modal" data-bs-target="#create-club">New Club</a>
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label" for="name">Name</label>
        <input type="text" class="form-control" id="name" name="name" placeholder="Team Name">
    </div>
    <div class="mb-3">
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="managed" id="managed">
            <label class="form-check-label small" for="managed">Managed</label>
        </div>
    </div>
    <div class="row align-items-start mb-3">
        <div class="col-auto">
            <label class="form-label" for="birth_year">Birth Year</label>
            <input type="number" class="form-control" id="birth_year" name="birth_year">
        </div>
        <div class="col-auto">
            <label class="form-label" for="rank">Rank</label>
            <select class="form-select" id="rank" name="rank">
                <option></option>
                <option value="A">A (best)</option>
                <option value="B">B</option>
                <option value="C">C</option>
                <option value="D">D</option>
            </select>
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label" for="website">Website</label>
        <input type="text" class="form-control" id="website" name="website" placeholder="https://www.my-soccer-club.com">
    </div>
    <div class="mb-3">
        <label for="notes">Notes</label>
        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Submit</button>
</form>
