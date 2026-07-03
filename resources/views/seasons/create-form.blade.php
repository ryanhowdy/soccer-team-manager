@php
    // Fall back to fetching the teams so this partial works wherever it's included.
    $seasonTeams = $managedTeams ?? \App\Models\ClubTeam::where('managed', 1)->orderBy('name')->get();
@endphp
<form class="row g-3" action="{{ route('seasons.store') }}" method="post">
    @csrf
    <div class="col-12 col-sm-6">
        <label for="season" class="form-label required">Season</label>
        <input type="text" class="form-control" name="season" id="season" required placeholder="Fall/Spring">
    </div>
    <div class="col-12 col-sm-6">
        <label for="year" class="form-label required">Year</label>
        <input type="number" class="form-control" name="year" id="year" required placeholder="{{ date('Y') }}">
    </div>
@if($seasonTeams->isNotEmpty())
    <div class="col-12">
        <label class="form-label">Teams</label>
        <p class="small text-muted mb-2">Choose which teams are active this season. You can add or remove teams later.</p>
        <div class="row g-2">
        @foreach($seasonTeams as $team)
            <div class="col-12 col-sm-6">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="teams[]" value="{{ $team->id }}" id="season-team-{{ $team->id }}" checked>
                    <label class="form-check-label" for="season-team-{{ $team->id }}">{{ $team->name }}</label>
                </div>
            </div>
        @endforeach
        </div>
    </div>
@endif
    <div class="col-12">
        <button type="submit" class="btn btn-primary">Create</button>
    </div>
</form>

