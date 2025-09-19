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

        <div class="rounded rounded-3 bg-white py-2 px-3 mb-2 d-flex align-items-center justify-content-between">
            <input class="d-none d-lg-inline-block w-50" type="text" id="search">
            <div>
                <a href="#" class="btn btn-sm btn-primary text-white" data-bs-toggle="modal" data-bs-target="#create-club">
                    <span class="bi-plus-lg pe-2"></span>Add Club
                </a>
                <a href="#" class="btn btn-sm btn-primary text-white text-nowrap" data-bs-toggle="modal" data-bs-target="#create-team">
                    <span class="bi-plus-lg pe-2"></span>Add Team
                </a>
            </div>
        </div>

        <div class="rounded rounded-3 bg-white p-4 mb-1">
            <table id="clubs-table" class="table align-middle">
                <thead class="">
                    <tr>
                        <th class="club-name">Club</th>
                        <th class="d-none d-lg-table-cell">Location</th>
                        <th class="teams">Teams</th>
                    </tr>
                </thead>
                <tbody class="table-group-divider">
                @foreach($clubs as $club)
                    <tr class="{{ strtolower(str_replace(' ', '-', $club->name)) }}">
                        <td class="club-name position-relative" data-bs-toggle="modal" data-bs-target="#club-{{ $club->id }}">
                            <img class="logo img-fluid ms-2 me-3" src="{{ asset($club->logo) }}" onerror="this.onerror=null;this.src='{{ asset('img/logo_none.png') }}';"/>
                            {{ $club->name }}
                            <span class="bi-chevron-down position-absolute"></span>
                        </td>
                        <td class="d-none d-lg-table-cell">
                        @if($club->city)
                            {{ $club->city }}, 
                            @if($club->city)
                                {{ $club->state }}
                            @endif
                        @endif
                        </td>
                        <td>
                            <div class="d-flex">
                                <div>
                                    <span class="badge text-bg-secondary fs-3 d-none d-lg-inline-block">{{ $club->teams->count() }}</span>
                                </div>
                                <div class="dropdown">
                                    <span class="bi-three-dots-vertical fs-4 ms-3" data-bs-toggle="dropdown"></span>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('clubs.edit', ['club' => $club->id]) }}">Edit</a></li>
                                        <li><a class="dropdown-item" target="_blank" href="{{ $club->website }}">Website</a></li>
                                    </ul>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
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
        $('#clubs-table tr').hide();
        curSearch = curSearch.toLowerCase();
        $('tr[class^=' + curSearch + '], tr[class*=' + curSearch + ']').show();
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
