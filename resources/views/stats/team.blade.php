@extends('layouts.main')

@section('body-id', 'stats')
@section('page-title', 'Team Statistics')
@section('page-desc', "Learn all about a team")

@section('content')
    <div class="container main-content">

        <form id="filter" class="row row-cols-md-auto gx-3 align-items-center justify-content-end">
            <div class="col-12 mb-3">
                <div class="dropdown">
                    <button class="btn bg-white dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        {{ $selectedManagedTeam->name }}
                    </button>
                    <ul class="dropdown-menu">
                    @foreach ($managedTeams as $i => $team)
                        <li>
                            <a href="{{ route('stats.teams.show', ['id' => $team->id]) }}" @class([
                               'dropdown-item',
                               'active' => $selectedManagedTeam->id == $team->id,
                               ])>{{ $team->name }}</a>
                        </li>
                    @endforeach
                    </ul>
                </div>
            </div>
            <div class="col-12 mb-3">
                <select class="form-select" id="filter-seasons" name="filter-seasons">
                    <option value="">All Seasons</option>
            @foreach ($seasons as $i => $season)
                @if ($loop->first)
                    <optgroup label="{{ $season->year }}">
                @else
                    @if ($seasons[$i]->year !== $seasons[$i-1]->year)
                    <optgroup label="{{ $season->year }}">
                    @endif
                @endif
                    <option value="{{ $season->id }}" @selected($selectedSeason == $season->id)>{{ $season->season }} {{ $season->year }}</option>
            @endforeach
                </select>
            </div>
            <div class="col-12 mb-3">
                <select class="form-select search-select" id="filter-teams" name="filter-teams">
                    <option value="">All Teams</option>
            @foreach ($teams as $clubName => $clubTeams)
                    <optgroup label="{{ $clubName }}">
                @foreach ($teams[$clubName] as $team)
                    <option value="{{ $team['id'] }}" @selected($selectedTeam == $team['id'])>{{ $team['name'] }}</option>
                @endforeach
            @endforeach
                </select>
            </div>
            <div class="col-12 mb-3">
                <button type="submit" class="btn btn-info">Filter</button>
            </div>
        </form>

        @include('stats.charts')

    </div><!--/container-->
@endsection
