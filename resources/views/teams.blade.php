@extends('layouts.main')

@section('body-id', 'teams')

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
            <div><h2>Club Teams</h2></div>
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
        @foreach($clubs as $club)
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
                        <span class="badge text-bg-light">{{ $club->teams->count() }} Teams</span>
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

@foreach($clubs as $club)
    <div id="club-{{ $club->id }}" class="modal modal-lg fade" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content py-4 px-2">
                <div class="modal-header">
                    <img class="logo img-fluid ms-2 me-3" src="{{ asset($club->logo) }}" onerror="this.onerror=null;this.src='{{ asset('img/logo_none.png') }}';"/>
                    {{ $club->name }}
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>{{ $club->notes }}</p>
                    <p><a href="{{ route('games.index', ['filter-seasons' => '', 'filter-clubs' => $club->id]) }}">See all games</a></p>
                </div>
                <div class="modal-body">
                    <table class="table align-middle">
                        <thead class="">
                            <tr>
                                <th>Team</th>
                                <th>Year</th>
                                <th>Rank</th>
                                <th>Notes</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody class="table-group-divider">
                        @foreach($club->teams as $team)
                            <tr>
                                <td>{{ $team->name }}</td>
                                <td>{{ $team->birth_year }}</td>
                                <td>
                                    <span @class([
                                        'badge',
                                        'text-bg-success text-white' => $team->rank == 'A',
                                        'text-bg-info' => $team->rank == 'B',
                                        'text-bg-warning' => $team->rank == 'C',
                                        'text-bg-danger' => $team->rank == 'D',
                                        'text-bg-secondary' => $team->rank == null,
                                    ])>{{ $team->rank ?: '?' }}</span>
                                </td>
                                <td>{{ $team->notes }}</td>
                                <td>
                                    <a href="{{ route('teams.edit', ['id' => $team->id]) }}"><span class="bi-pencil"></span></a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <script>
                    $('#club-{{ $club->id }}').on('shown.bs.modal', function (e) {
                        $(this).find('table').DataTable({
                            autoWidth: false,
                            paging: false,
                            searching: false,
                            info: false
                        });
                    });
                    </script>
                </div>
            </div>
        </div>
    </div><!--/.modal-->
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
