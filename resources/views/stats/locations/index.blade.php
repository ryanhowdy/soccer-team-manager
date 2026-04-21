@extends('layouts.main')

@section('body-id', 'stats')
@section('page-title', 'Team Statistics')
@section('page-desc', "Learn all about a team")

@section('content')
    <div class="container main-content">

        <div class="d-flex justify-content-between align-items-center ps-1 pb-2">
            <div class="fw-bold text-secondary fs-5">Location Stats</div>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="toggle-single-game">
                <label class="form-check-label" for="toggle-single-game">Show single-game locations</label>
            </div>
        </div>

        <div class="rounded rounded-3 bg-white p-4 mb-3">
            <table class="table" id="locations-table">
                <thead>
                    <tr>
                        <th>Location</th>
                        <th>
                            <span class="d-inline-block d-lg-none">G</span>
                            <span class="d-none d-lg-inline-block">Games</span>
                        </th>
                        <th>Win %</th>
                        <th>
                            <span class="d-inline-block d-lg-none">W</span>
                            <span class="d-none d-lg-inline-block">Wins</span>
                        </th>
                        <th>
                            <span class="d-inline-block d-lg-none">D</span>
                            <span class="d-none d-lg-inline-block">Draws</span>
                        </th>
                        <th>
                            <span class="d-inline-block d-lg-none">L</span>
                            <span class="d-none d-lg-inline-block">Losses</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                @foreach($resultsByLocation as $locationId => $results)
                    @php
                        $wdl = [
                            'W' => 0,
                            'D' => 0,
                            'L' => 0,
                        ];
                    @endphp
                    @foreach($results as $k => $result)
                        @php
                            $wdl[ $result['win_draw_loss'] ]++;
                        @endphp
                    @endforeach
                    @php
                        $winPct = round($wdl['W'] / count($results) * 100);
                        $badgeClass = $winPct < 30 ? 'danger' : ($winPct < 50 ? 'warning' : ($winPct < 60 ? 'secondary' : ($winPct < 90 ? 'success bg-opacity-75' : 'success')));
                        $isSingle = count($results) <= 1;
                    @endphp
                    <tr @class(['single-game' => $isSingle, 'opacity-50' => $isSingle])>
                        <td>{{ $results[0]['location']['name'] }}</td>
                        <td>{{ count($results) }}</td>
                        <td data-order="{{ $winPct }}">
                            <span class="badge bg-{{ $badgeClass }}" style="min-width:35px">{{ $winPct }}</span>
                        </td>
                        <td>{{ $wdl['W'] }}</td>
                        <td>{{ $wdl['D'] }}</td>
                        <td>{{ $wdl['L'] }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div><!--/container-->

<script>
let showSingleGame = false;

$.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
    if (settings.nTable.id !== 'locations-table') return true;
    if (showSingleGame) return true;
    return !$(settings.aoData[dataIndex].nTr).hasClass('single-game');
});

let locationsTable = $('#locations-table').DataTable({
    autoWidth: false,
    paging: false,
    searching: true,
    info: false,
    order: [[2, 'desc'], [3, 'desc']],
    dom: 't'
});

$('#toggle-single-game').on('change', function() {
    showSingleGame = this.checked;
    locationsTable.draw();
});
</script>
@endsection
