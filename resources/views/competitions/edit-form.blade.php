{{-- Expects $comp (the Competition) and $managedTeams --}}
<form method="post" action="{{ route('competitions.update', ['competition' => $comp->id]) }}">
    @csrf
    <div class="row align-items-start mb-3">
        <div class="col">
            <label class="form-label required" for="club_team_id-{{ $comp->id }}">Team</label>
            <select class="form-select" id="club_team_id-{{ $comp->id }}" name="club_team_id" required>
        @foreach ($managedTeams as $team)
                <option value="{{ $team->id }}" @selected($team->id == $comp->club_team_id)>{{ $team->club->name }}: {{ $team->name }} {{ $team->birth_year }}</option>
        @endforeach
            </select>
        </div>
        <div class="col-auto">
            <label class="form-label required" for="status-{{ $comp->id }}">Status</label>
            <select class="form-select" id="status-{{ $comp->id }}" name="status" required>
                <option value="A" @selected($comp->status == 'A')>Active</option>
                <option value="D" @selected($comp->status == 'D')>Done</option>
                <option value="C" @selected($comp->status == 'C')>Cancelled</option>
            </select>
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label required" for="type-{{ $comp->id }}">Type</label>
        <select class="form-select" id="type-{{ $comp->id }}" name="type" required>
            <option value="Cup" @selected($comp->type == 'Cup')>Cup</option>
            <option value="League" @selected($comp->type == 'League')>League</option>
            <option value="Friendly" @selected($comp->type == 'Friendly')>Friendly</option>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label required" for="name-{{ $comp->id }}">Name</label>
        <input type="text" class="form-control" id="name-{{ $comp->id }}" name="name" required value="{{ $comp->name }}">
    </div>
    <div class="mb-3">
        <label class="form-label required" for="division-{{ $comp->id }}">Division</label>
        <input type="text" class="form-control" id="division-{{ $comp->id }}" name="division" required value="{{ $comp->division }}">
    </div>
    <div class="row align-items-start mb-3">
        <div class="col">
            <label class="form-label" for="place-{{ $comp->id }}">Place</label>
            <input type="number" class="form-control" id="place-{{ $comp->id }}" name="place" min="1" max="99" value="{{ $comp->place }}">
        </div>
        <div class="col">
            <label class="form-label" for="level-{{ $comp->id }}">Current Level</label>
            <input type="number" class="form-control" id="level-{{ $comp->id }}" name="level" value="{{ $comp->level }}">
        </div>
        <div class="col">
            <label class="form-label" for="total_levels-{{ $comp->id }}">Total Levels</label>
            <input type="number" class="form-control" id="total_levels-{{ $comp->id }}" name="total_levels" value="{{ $comp->total_levels }}">
        </div>
    </div>
    <div class="row align-items-start mb-3">
        <div class="col-auto">
            <label class="form-label required" for="started_at-{{ $comp->id }}">Start Date</label>
            <input type="date" class="form-control" id="started_at-{{ $comp->id }}" name="started_at" required value="{{ $comp->started_at->format('Y-m-d') }}">
        </div>
        <div class="col-auto">
            <label class="form-label required" for="ended_at-{{ $comp->id }}">End Date</label>
            <input type="date" class="form-control" id="ended_at-{{ $comp->id }}" name="ended_at" required value="{{ $comp->ended_at->format('Y-m-d') }}">
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label" for="website-{{ $comp->id }}">Website</label>
        <input type="text" class="form-control" id="website-{{ $comp->id }}" name="website" value="{{ $comp->website }}">
    </div>
    <div class="mb-3">
        <label for="notes-{{ $comp->id }}">Notes</label>
        <textarea class="form-control" id="notes-{{ $comp->id }}" name="notes" rows="3">{{ $comp->notes }}</textarea>
    </div>
    <button type="submit" class="btn btn-primary">Save</button>
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
</form>
