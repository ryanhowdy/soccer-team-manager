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

        @include('stats.charts')

    </div><!--/container-->
@endsection
