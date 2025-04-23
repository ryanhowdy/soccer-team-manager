@extends('layouts.main')

@section('body-id', 'home')
@section('page-title', 'Home')
@section('page-desc', "Welcome to your team's home page")

@section('content')
    <div class="container main-content">

        @if (count($scheduledToday))
            @include('home.scheduled-today')
        @endif

        @if (count($scheduled))
            @include('home.scheduled')
        @endif

        <div id="filter" class="d-flex justify-content-end align-items-center mb-3">
        @if($managedTeams->isNotEmpty() && !is_null($competition))
            <div class="me-3 text-secondary fst-italic">
                {{ $competition->name }}
            </div>
            <div class="dropdown">
                <button class="btn bg-white dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    {{ $selectedManagedTeamName }}
                </button>
                <ul class="dropdown-menu">
                @foreach ($managedTeams as $i => $team)
                    <li>
                        <a href="{{ route('homeByTeam', ['teamId' => $team->id]) }}" @class([
                           'dropdown-item',
                           'active' => $selectedManagedTeamId == $team->id,
                           ])>{{ $team->name }}</a>
                    </li>
                @endforeach
                </ul>
            </div>
        @endif
        </div>

    @if(!empty($chartData))
        @include('stats.charts')
    @else
        <div class="rounded rounded-3 bg-white p-5 text-center mb-1">
            <img class="opacity-50 w-50" src="{{ asset('img/empty-state.svg') }}">
            <div class="fw-bold mt-5 pb-1 text-secondary">No Stats</div>
            <small class="pb-3 d-block text-secondary">Either no games have been played yet, or no stats have been entered for these games.</small>
        </div>
    @endif

    </div><!--/container-->
@endsection
