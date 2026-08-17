@extends('layouts.main')

@section('body-id', 'teams')
@section('page-title', 'Teams')
@section('page-desc', "The teams you manage")

@section('content')
    <div class="container main-content">

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

        <div class="d-flex justify-content-between mb-3">
            <div><h2>Teams</h2></div>
            <div class="d-flex gap-2 align-items-center justify-content-end">
            @can('edit things')
                <div>
                    <a href="#" class="btn btn-sm btn-dark text-white rounded-pill py-2 px-3" data-bs-toggle="modal" data-bs-target="#create-team">
                        <span class="bi-plus-lg pe-0 pe-lg-2"></span><span class="d-none d-lg-inline-block">Add Team</span>
                    </a>
                </div>
            @endcan
            </div>
        </div>

    @forelse($myClubs as $club)
        <div class="rounded rounded-3 bg-white p-4 mb-3">
            <div class="d-flex align-items-center mb-3">
                <div><img class="logo img-fluid me-3" src="{{ asset($club->logo) }}" onerror="this.onerror=null;this.src='{{ asset('img/logo_none.png') }}';"/></div>
                <div class="fw-bold flex-grow-1">{{ $club->name }}</div>
                <div class="d-flex gap-2 align-items-center">
                    <a href="#" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#club-{{ $club->id }}">Details</a>
                @can('edit things')
                    <a href="{{ route('clubs.edit', ['club' => $club->id]) }}" class="link-secondary" title="Edit club" aria-label="Edit club">
                        <span class="bi-pencil"></span>
                    </a>
                @endcan
                </div>
            </div>
            <div class="row gy-3">
        @foreach($club->teams->where('managed', 1) as $team)
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="border rounded rounded-3 p-3 h-100">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fs-5">{{ $team->name }}</div>
                                <div class="text-muted smaller">{{ $team->birth_year }}</div>
                            </div>
                            <span @class([
                                'badge',
                                'text-bg-success text-white' => $team->rank == 'A',
                                'text-bg-info' => $team->rank == 'B',
                                'text-bg-warning' => $team->rank == 'C',
                                'text-bg-danger' => $team->rank == 'D',
                                'text-bg-secondary' => $team->rank == null,
                            ])>{{ $team->rank ?: '?' }}</span>
                        </div>
                        {{-- Roster and Games read the navbar picker rather than the url,
                             so these post through pickTeam to switch team on the way in --}}
                        <div class="d-flex gap-2 align-items-center pt-3">
                            <form method="post" action="{{ route('pickTeam', ['teamId' => $team->id]) }}">
                                @csrf
                                <input type="hidden" name="to" value="rosters.index">
                                <button type="submit" class="btn btn-sm btn-outline-primary">Roster</button>
                            </form>
                            <form method="post" action="{{ route('pickTeam', ['teamId' => $team->id]) }}">
                                @csrf
                                <input type="hidden" name="to" value="games.index">
                                <button type="submit" class="btn btn-sm btn-outline-primary">Games</button>
                            </form>
                        @can('edit things')
                            <a href="{{ route('teams.edit', ['id' => $team->id]) }}" class="link-secondary ps-1" title="Edit team" aria-label="Edit team">
                                <span class="bi-pencil"></span>
                            </a>
                        @endcan
                        </div>
                    </div>
                </div>
        @endforeach
            </div>
        </div>
    @empty
        <div class="rounded rounded-3 bg-white p-5 text-center mb-3">
            <img class="opacity-50 w-25" src="{{ asset('img/empty-state.svg') }}">
            <div class="fs-3 fw-bold mt-5 pb-1">No Teams</div>
            <small class="pb-3 d-block text-muted">Add a team and tick "Managed" to see it here.</small>
        </div>
    @endforelse

    </div><!--/container-->

@foreach($myClubs as $club)
@include('partials.club-details-modal', ['club' => $club])
@endforeach

    <div id="create-team" class="modal fade" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content py-4 px-2">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Team</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
@include('teams.create-form')
                </div>
            </div>
        </div>
    </div>
    {{-- No Add Club button here (that lives on Opponents), but the team form's
         "New Club" link opens this, so the modal has to exist on this page too --}}
    <div id="create-club" class="modal fade" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content py-4 px-2">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Club</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
@include('teams.create-club-form')
                </div>
            </div>
        </div>
    </div>
@endsection
