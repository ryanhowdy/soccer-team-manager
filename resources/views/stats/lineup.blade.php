@extends('layouts.main')

@section('body-id', 'stats')
@section('page-title', 'Team Statistics')
@section('page-desc', "Learn all about a team")

@section('content')
    <div class="container main-content">

        <form id="filter">
            <div class="rounded rounded-3 bg-white py-2 px-3 mb-3">
                <div class="d-flex align-items-center justify-content-end">
                    <div class="pe-2">
                        <select class="form-select form-select-sm bg-light border border-light" id="filter-managed" name="filter-managed">
                        @foreach ($managedTeams as $i => $team)
                            <option value="{{ $team->id }}" @selected($selectedManagedTeamId == $team->id)>{{ $team->name }}</option>
                        @endforeach
                        </select>
                    </div>
                    <div class="pe-2">
                        <select class="form-select form-select-sm bg-light border border-light" id="filter-seasons" name="filter-seasons">
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
                    <div class="pe-2">
                        <button type="submit" class="btn btn-sm btn-primary text-white">Filter</button>
                    </div>
                </div>
            </div>
        </form>


        <div class="row">

            {{-- lineups table --}}
            <div class="col-12 col-md-6">
                <div class="fw-bold text-secondary fs-5 ps-1 pb-2">Lineup Stats</div>
                <div class="rounded rounded-3 bg-white p-4 mb-3">
                    <table id="lineups-table" class="table">
                        <thead>
                            <tr class="text-center">
                                <th>Lineup</th>
                                <th>Games</th>
                                <th>Goals</th>
                                <th>Goals Against</th>
                                <th>Difference</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($stats['lineups'] as $lineup => $s)
                            @php
                                $diff = $s['goals'] - $s['goals_against'];
                            @endphp
                            <tr>
                                <td>{{ $lineup }}</td>
                                <td class="text-end">{{ count($s['games']) }}</td>
                                <td class="text-end">{{ $s['goals'] }}</td>
                                <td class="text-end">{{ $s['goals_against'] }}</td>
                                <td class="text-end">
                                    <span @class([
                                    'badge',
                                    'text-bg-success' => $diff > 0,
                                    'text-bg-danger'  => $diff < 0,
                                    'text-bg-secondary' => $diff == 0,
                                    ])>{{ $diff }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- players table --}}
            <div class="col-12 col-md-6">
                <div class="fw-bold text-secondary fs-5 ps-1 pb-2">Player Stats</div>
                <div class="rounded rounded-3 bg-white p-4 mb-3">
                    <table id="players-table" class="table">
                        <thead>
                            <tr class="text-center">
                                <th>Player</th>
                                <th>Games</th>
                                <th>Goals</th>
                                <th>Goals Against</th>
                                <th>Difference</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($stats['players'] as $playerId => $p)
                            @php
                                $diff = $p['goals'] - $p['goals_against'];
                            @endphp
                            <tr>
                                <td>{{ $p['name'] }}</td>
                                <td class="text-end">{{ count($p['games']) }}</td>
                                <td class="text-end">{{ $p['goals'] }}</td>
                                <td class="text-end">{{ $p['goals_against'] }}</td>
                                <td class="text-end">
                                    <span @class([
                                    'badge',
                                    'text-bg-success' => $diff > 0,
                                    'text-bg-danger'  => $diff < 0,
                                    'text-bg-secondary' => $diff == 0,
                                    ])>{{ $diff }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div><!--/.row-->


    </div><!--/container-->

<script>
$('#lineups-table').DataTable({
    autoWidth: false,
    paging: false,
    searching: false,
    info: false,
    order: [[4, 'desc']]
});
$('#players-table').DataTable({
    autoWidth: false,
    paging: false,
    searching: false,
    info: false,
    order: [[4, 'desc']]
});
</script>
@endsection
