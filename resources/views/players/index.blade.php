@extends('layouts.main')

@section('body-id', 'players')

@section('content')
    <div class="container main-content">

        <div class="d-flex justify-content-between mb-3">
            <div><h2>Players</h2></div>
            <div class="d-flex gap-2 align-items-center justify-content-end">
            @can('edit things')
                <div class="ps-2">
                    <a href="#" class="btn btn-sm btn-dark text-white rounded-pill py-2 px-3" data-bs-toggle="modal" data-bs-target="#create-player">
                        <span class="bi-plus-lg pe-0 pe-lg-2"></span><span class="d-none d-lg-inline-block">Add Player</span>
                    </a>
                </div>
            @endcan
            </div>
        </div>

        <div class="rounded rounded-3 bg-white position-relative p-4 mb-3">

        @if ($errors->any())
            <div class="alert alert-danger mt-3">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

            {{-- Every player in the system, not just the selected team's roster.
                 Birth year identifies a club player, graduation year a high school
                 one --}}
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Birth Year</th>
                        <th>Graduation Year</th>
                        <th>Teams</th>
                        <th>Positions</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @foreach($players as $player)
                    <tr>
                        <td>
                            <a class="text-decoration-none" href="{{ route('players.show', ['player' => $player->id]) }}">{{ $player->name }}</a>
                        @if($player->nickname)
                            <div class="text-muted smaller">{{ $player->nickname }}</div>
                        @endif
                        </td>
                        <td>{{ $player->birth_year ?: '' }}</td>
                        <td>{{ $player->graduation_year ?: '' }}</td>
                        <td class="smaller">
                        @foreach($player->teams as $playerTeam)
                            @continue(!$playerTeam->clubTeam)
                            <div>{{ $playerTeam->clubTeam->club->name ?? '' }}: {{ $playerTeam->clubTeam->name }}</div>
                        @endforeach
                        </td>
                        <td class="smaller">{{ $player->positions->pluck('position_name')->implode(', ') }}</td>
                        <td class="text-end">
                        @can('edit things')
                            <a class="link-secondary text-decoration-none" href="{{ route('players.edit', ['player' => $player->id]) }}" title="Edit player">
                                <span class="bi bi-pencil pe-2"></span>Edit
                            </a>
                        @endcan
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

    </div><!--/container-->

@can('edit things')
    <div id="create-player" class="modal fade" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content py-4 px-2">
                <div class="modal-header">
                    <h5 class="modal-title">Add Player</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
@include('players.create-form')
                </div>
            </div>
        </div>
    </div>
@endcan

<script>
$('.table').DataTable({
    autoWidth: false,
    paging: true,
    pageLength: 50,
    searching: true,
    info: true,
    order: [[0, 'asc']],
    columnDefs: [{ targets: [4, 5], orderable: false }],
    // DataTables 2 puts the page length above the table by default. With 140
    // players the search box is what you want up top, so the length dropdown
    // moves down beside the info line and the top row is just search.
    layout: {
        topStart: null,
        topEnd: 'search',
        bottomStart: ['info', 'pageLength'],
        bottomEnd: 'paging'
    }
});
</script>
@endsection
