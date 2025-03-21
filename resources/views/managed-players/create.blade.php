@extends('layouts.main')

@section('body-id', 'me')

@section('content')
    <div class="container main-content">
        <div class="rounded rounded-3 bg-white p-3 mb-2">
            <h5 class="mb-3">Add Managed Player</h5>
            <form method="post" style="max-width:500px" action="{{ route('managed-players.create') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="player_id">Player</label>
                    <select class="form-select" id="player_id" name="player_id">
                        <option></option>
                    @foreach($players as $birthYear => $ps)
                        <optgroup label="{{ $birthYear }}">
                        @foreach($ps as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                        </optgroup>
                    @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary text-white">Add</button>
                <a href="{{ route('managed-players.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
@endsection

