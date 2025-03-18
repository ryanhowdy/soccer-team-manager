@extends('layouts.main')

@section('body-id', 'teams')

@section('content')
    <div class="container main-content">
        <div class="rounded rounded-3 bg-white p-4 mb-1">
            <form method="post" action="{{ route('teams.edit', ['id' => $team->id]) }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="club_id">Club</label>
                    <select class="form-select" id="club_id" name="club_id">
                    @foreach ($clubs as $type => $club)
                        <option @selected(old('club_id', $team->club_id) == $club->id) value="{{ $club->id }}">{{ $club->name }}</option>
                    @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="name">Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $team->name) }}" placeholder="Team Name">
                </div>
                <div class="mb-3">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" @checked(old('managed', $team->managed)) name="managed" id="managed">
                        <label class="form-check-label small" for="managed">Managed</label>
                    </div>
                </div>
                <div class="row align-items-start mb-3">
                    <div class="col-auto">
                        <label class="form-label" for="birth_year">Birth Year</label>
                        <input type="number" class="form-control" id="birth_year" name="birth_year" value="{{ old('birth_year', $team->birth_year) }}">
                    </div>
                    <div class="col-auto">
                        <label class="form-label" for="rank">Rank</label>
                        <select class="form-select" id="rank" name="rank">
                            <option></option>
                            <option @selected(old('rank', $team->rank) == 'A') value="A">A (best)</option>
                            <option @selected(old('rank', $team->rank) == 'B') value="B">B</option>
                            <option @selected(old('rank', $team->rank) == 'C') value="C">C</option>
                            <option @selected(old('rank', $team->rank) == 'D') value="D">D</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="website">Website</label>
                    <input type="text" class="form-control" id="website" name="website" value="{{ old('website', $team->website) }}" placeholder="https://www.my-soccer-club.com">
                </div>
                <div class="mb-3">
                    <label for="notes">Notes</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes', $team->notes) }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>
    </div>
@endsection
