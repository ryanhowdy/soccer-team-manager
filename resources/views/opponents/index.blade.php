@extends('layouts.main')

@section('body-id', 'teams')
@section('page-title', 'Opponents')
@section('page-desc', "Clubs and teams you play against")

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
            <div><h2>Opponents</h2></div>
            <div class="d-flex gap-2 align-items-center justify-content-end">
                <div>
                    <input class="d-none d-lg-inline-block bg-light" type="text" id="search">
                </div>
            @can('edit things')
                <div>
                    <a href="#" class="btn btn-sm btn-dark text-white rounded-pill py-2 px-3" data-bs-toggle="modal" data-bs-target="#create-club">
                        <span class="bi-plus-lg pe-0 pe-lg-2"></span><span class="d-none d-lg-inline-block">Add Club</span>
                    </a>
                    <a href="#" class="btn btn-sm btn-dark text-white rounded-pill py-2 px-3" data-bs-toggle="modal" data-bs-target="#create-team">
                        <span class="bi-plus-lg pe-0 pe-lg-2"></span><span class="d-none d-lg-inline-block">Add Team</span>
                    </a>
                </div>
            @endcan
            </div>
        </div>

        <div id="clubs-cards" class="d-flex flex-wrap mb-5">
        @foreach($opponentClubs as $club)
            @php
                // A club can sit on both pages, so count only the teams that make
                // it an opponent here.  The details modal still lists every team.
                $opponentTeams = $club->teams->where('managed', '!=', 1);
            @endphp
            <div class="card p-3 me-3 mb-3 {{ strtolower(str_replace(' ', '-', $club->name)) }}" style="width:400px">
            @can('edit things')
                <div class="position-absolute end-0" style="top:2px">
                    <div class="dropdown">
                        <span class="bi-three-dots-vertical pe-1" role="button" data-bs-toggle="dropdown"></span>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('clubs.edit', ['club' => $club->id]) }}">Edit</a></li>
                        </ul>
                    </div>
                </div>
            @endcan
                <div class="d-flex align-items-center mb-3">
                    <div><img class="logo img-fluid me-3" src="{{ asset($club->logo) }}" onerror="this.onerror=null;this.src='{{ asset('img/logo_none.png') }}';"/></div>
                    <div class="fw-bold">{{ $club->name }}</div>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <div>
                        <span class="badge text-bg-light">{{ $opponentTeams->count() }} Teams</span>
                    @if(clubHasManagedTeam($club))
                        <a href="{{ route('teams.index') }}" class="badge text-bg-light link-primary text-decoration-none"
                            title="This club also has teams you manage">+ managed</a>
                    @endif
                    </div>
                @if($club->city)
                    <div>
                        <span class="border rounded-pill small p-2">
                            <span class="bi bi-geo-alt"></span>
                            {{ $club->city }},
                        @if($club->city)
                            {{ $club->state }}
                        @endif
                        </span>
                    </div>
                @endif
                </div>
                <div>
                    <a href="#" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#club-{{ $club->id }}" class="card-link">Details</a>
                @if($club->website)
                    <a href="{{ $club->website }}" class="btn btn-sm btn-outline-dark" target="_blank" class="card-link">Website</a>
                @endif
                </div>
            </div>
        @endforeach
        </div>

    </div><!--/container-->

@foreach($opponentClubs as $club)
@include('partials.club-details-modal', ['club' => $club])
@endforeach

<script>
$(document).keydown(function(e) {
    if (!$(event.target).is(':input'))
    {
        $('#search').focus();
    }
});
$(document).on('input', '#search', function() {
    let = curSearch = $('#search').val();

    if ((curSearch) && curSearch != '')
    {
        $('#clubs-cards .card').hide();
        curSearch = curSearch.toLowerCase();
        $('#clubs-cards > div[class^=' + curSearch + '], #clubs-cards > div[class*=' + curSearch + ']').show();
    }
});
</script>


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
