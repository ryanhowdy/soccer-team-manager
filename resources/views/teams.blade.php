@extends('layouts.main')

@section('body-id', 'teams')
@section('page-title', 'Teams')
@section('page-desc', "A list of all Club Teams played against")

@section('content')
    <div class="container main-content">

        <div class="rounded rounded-3 bg-white p-4 mb-1">
            <table class="table align-middle">
                <thead class="">
                    <tr>
                        <th class="club-name">Club</th>
                        <th>Location</th>
                        <th class="teams">Teams</th>
                    </tr>
                </thead>
                <tbody class="table-group-divider">
                @foreach($clubs as $club)
                    <tr>
                        <td class="club-name position-relative" data-bs-toggle="modal" data-bs-target="#club-{{ $club->id }}">
                            <img class="logo img-fluid ms-2 me-3" src="{{ asset($club->logo) }}"/>
                            {{ $club->name }}
                            <span class="bi-chevron-down position-absolute"></span>
                        </td>
                        <td>
                        @if($club->city)
                            {{ $club->city }}, 
                            @if($club->city)
                                {{ $club->state }}
                            @endif
                        @endif
                        </td>
                        <td>
                            <span class="badge text-bg-secondary fs-3">{{ $club->teams->count() }}</span>
                            <span class="bi-three-dots-vertical fs-4 ms-3"></span>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

    @foreach($clubs as $club)
        <div id="club-{{ $club->id }}" class="modal modal-lg fade" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content py-4 px-2">
                    <div class="modal-header">
                        <img class="logo img-fluid ms-2 me-3" src="{{ asset($club->logo) }}"/>
                        {{ $club->name }}
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                                        <a href="#{{ $team->id }}"><span class="bi-pencil"></span></a>
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

    </div><!--/container-->
@endsection
