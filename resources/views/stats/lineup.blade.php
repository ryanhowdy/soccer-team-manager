@extends('layouts.main')

@section('body-id', 'stats')
@section('page-title', 'Team Statistics')
@section('page-desc', "Learn all about a team")

@section('content')
    <div class="container main-content">

        <div class="d-flex justify-content-between mb-3">
            <div><h2>Lineup Stats</h2></div>
            <div class="d-flex gap-2 align-items-center justify-content-end">
                <div class="form-check form-switch pe-3">
                    <input class="form-check-input" type="checkbox" id="toggle-low-sample">
                    <label class="form-check-label small" for="toggle-low-sample">Show low-sample</label>
                </div>
                <div class="pe-2">
                    <div class="dropdown">
                        <button type="button" class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown">
                            <span class="d-none d-lg-inline-block">
                            @php
                                $currentSeason = $selectedSeason ? $seasons[$selectedSeason] ?? null : null;
                            @endphp
                            {{ $currentSeason ? $currentSeason->season . ' ' . $currentSeason->year : 'All Seasons' }}
                            </span><span class="bi-filter ps-1"></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item @if(!$selectedSeason) active @endif" href="{{ route('stats.lineups.index') }}">All Seasons</a>
                            </li>
                        @foreach ($seasons as $i => $season)
                            @if ($loop->first || $seasons[$i]->year !== $seasons[$i-1]->year)
                                <li><h6 class="dropdown-header">{{ $season->year }}</h6></li>
                            @endif
                            <li>
                                <a class="dropdown-item ps-4 @if($selectedSeason == $season->id) active @endif"
                                    href="{{ route('stats.lineups.index', ['filter-seasons' => $season->id]) }}">
                                    {{ $season->season }} {{ $season->year }}
                                </a>
                            </li>
                        @endforeach
                        </ul>
                    </div><!--/.dropdown-->
                </div>
            </div>
        </div>

        @php
            $minMinutes = 30;
        @endphp

        <div class="row">

            {{-- lineups table --}}
            <div class="col-12 col-md-6">
                <div class="fw-bold text-secondary fs-5 ps-1 pb-2">
                    Lineup Stats
                    <span class="bi-question-circle ps-2"
                        role="button"
                        data-bs-toggle="tooltip"
                        data-bs-title="All stats are per 90 minutes"></span>
                </div>
                <div class="rounded rounded-3 bg-white p-4 mb-3">
                    <div class="table-responsive">
                        <table id="lineups-table" class="table">
                            <thead>
                                <tr class="text-center">
                                    <th class="text-start">Lineup</th>
                                    <th>
                                        <span class="d-inline-block d-lg-none">Min</span>
                                        <span class="d-none d-lg-inline-block">Minutes</span>
                                    </th>
                                    <th data-bs-toggle="tooltip" data-bs-title="Goals">G</th>
                                    <th data-bs-toggle="tooltip" data-bs-title="Goals Against">GA</th>
                                    <th data-bs-toggle="tooltip" data-bs-title="Expected Goals">xG</th>
                                    <th data-bs-toggle="tooltip" data-bs-title="Expected Goals Against">xGA</th>
                                    <th>
                                        <span class="d-inline-block d-lg-none">Dif</span>
                                        <span class="d-none d-lg-inline-block">Diff</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($stats['lineups'] as $lineupKey => $s)
                                @php
                                    $diff90         = $s['diff_per_90'];
                                    $players        = $s['players'];
                                    $visiblePlayers = array_slice($players, 0, 3);
                                    $hiddenPlayers  = array_slice($players, 3);
                                    $lowSample      = $s['minutes'] < $minMinutes;
                                @endphp
                                <tr @class(['low-sample' => $lowSample, 'opacity-50' => $lowSample])>
                                    <td>
                                        <span class="lineup-visible">
                                        @foreach($visiblePlayers as $i => $p)
                                            <span @class(['fw-semibold' => $p['managed']])>{{ $p['name'] }}</span>@if(!$loop->last), @endif
                                        @endforeach
                                        </span>
                                    @if(count($hiddenPlayers))
                                        <span class="lineup-hidden d-none">
                                            , @foreach($hiddenPlayers as $i => $p)<span @class(['fw-semibold' => $p['managed']])>{{ $p['name'] }}</span>@if(!$loop->last), @endif
                                            @endforeach
                                        </span>
                                        <a href="#" class="lineup-toggle small text-decoration-none ms-1"
                                            data-more-text="(+{{ count($hiddenPlayers) }} more {{ count($hiddenPlayers) === 1 ? 'player' : 'players' }})"
                                            data-less-text="(show less)">(+{{ count($hiddenPlayers) }} more {{ count($hiddenPlayers) === 1 ? 'player' : 'players' }})</a>
                                    @endif
                                    </td>
                                    <td class="text-end" data-order="{{ $s['minutes'] }}">
                                        {{ round($s['minutes']) }}
                                    </td>
                                    <td class="text-end" data-order="{{ $s['goals_per_90'] }}"
                                        title="{{ $s['goals'] }} {{ $s['goals'] === 1 ? 'goal' : 'goals' }} in {{ round($s['minutes']) }} min">
                                        {{ number_format($s['goals_per_90'], 2) }}
                                    </td>
                                    <td class="text-end" data-order="{{ $s['ga_per_90'] }}"
                                        title="{{ $s['goals_against'] }} against in {{ round($s['minutes']) }} min">
                                        {{ number_format($s['ga_per_90'], 2) }}
                                    </td>
                                    <td class="text-end" data-order="{{ $s['xg_per_90'] }}"
                                        title="{{ number_format($s['xg'], 1) }} xG in {{ round($s['minutes']) }} min">
                                        {{ number_format($s['xg_per_90'], 2) }}
                                    </td>
                                    <td class="text-end" data-order="{{ $s['xga_per_90'] }}"
                                        title="{{ number_format($s['xg_against'], 1) }} xGA in {{ round($s['minutes']) }} min">
                                        {{ number_format($s['xga_per_90'], 2) }}
                                    </td>
                                    <td class="text-end" data-order="{{ $diff90 }}">
                                        <span @class([
                                        'badge',
                                        'text-bg-success'   => $diff90 > 0,
                                        'text-bg-danger'    => $diff90 < 0,
                                        'text-bg-secondary' => $diff90 == 0,
                                        ])>{{ number_format($diff90, 2) }}</span>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- players table --}}
            <div class="col-12 col-md-6">
                <div class="fw-bold text-secondary fs-5 ps-1 pb-2">
                    Player Stats
                    <span class="bi-question-circle ps-2"
                        role="button"
                        data-bs-toggle="tooltip"
                        data-bs-title="All stats are per 90 minutes"></span>
                </div>
                <div class="rounded rounded-3 bg-white p-4 mb-3">
                    <div class="table-responsive">
                        <table id="players-table" class="table">
                            <thead>
                                <tr class="text-center">
                                    <th class="text-start">Player</th>
                                    <th>
                                        <span class="d-inline-block d-lg-none">Min</span>
                                        <span class="d-none d-lg-inline-block">Minutes</span>
                                    </th>
                                    <th data-bs-toggle="tooltip" data-bs-title="Goals">G</th>
                                    <th data-bs-toggle="tooltip" data-bs-title="Goals Against">GA</th>
                                    <th data-bs-toggle="tooltip" data-bs-title="Expected Goals">xG</th>
                                    <th data-bs-toggle="tooltip" data-bs-title="Expected Goals Against">xGA</th>
                                    <th>Win %</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($stats['players'] as $playerId => $p)
                                @php
                                    $games     = count($p['games']);
                                    $winPct    = $p['win_pct'];
                                    $winBadge  = $winPct < 30 ? 'danger' : ($winPct < 50 ? 'warning' : ($winPct < 60 ? 'secondary' : ($winPct < 90 ? 'success bg-opacity-75' : 'success')));
                                    $lowSample = $p['minutes'] < $minMinutes;
                                @endphp
                                <tr @class(['low-sample' => $lowSample, 'opacity-50' => $lowSample])>
                                    <td>
                                        <span @class(['fw-semibold' => $p['managed']])>{{ $p['name'] }}</span>
                                    </td>
                                    <td class="text-end" data-order="{{ $p['minutes'] }}">
                                        {{ round($p['minutes']) }}
                                    </td>
                                    <td class="text-end" data-order="{{ $p['goals_per_90'] }}"
                                        title="{{ $p['goals'] }} in {{ round($p['minutes']) }} min">
                                        {{ number_format($p['goals_per_90'], 2) }}
                                    </td>
                                    <td class="text-end" data-order="{{ $p['ga_per_90'] }}"
                                        title="{{ $p['goals_against'] }} against in {{ round($p['minutes']) }} min">
                                        {{ number_format($p['ga_per_90'], 2) }}
                                    </td>
                                    <td class="text-end" data-order="{{ $p['xg_per_90'] }}"
                                        title="{{ number_format($p['xg'], 1) }} xG in {{ round($p['minutes']) }} min">
                                        {{ number_format($p['xg_per_90'], 2) }}
                                    </td>
                                    <td class="text-end" data-order="{{ $p['xga_per_90'] }}"
                                        title="{{ number_format($p['xg_against'], 1) }} xGA in {{ round($p['minutes']) }} min">
                                        {{ number_format($p['xga_per_90'], 2) }}
                                    </td>
                                    <td class="text-end" data-order="{{ $winPct }}"
                                        title="{{ $p['wins'] }}W - {{ $p['draws'] }}D - {{ $p['losses'] }}L across {{ $games }} {{ $games === 1 ? 'game' : 'games' }}">
                                        <span class="badge bg-{{ $winBadge }}" style="min-width:40px">{{ $winPct }}%</span>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div><!--/.row-->


    </div><!--/container-->

<script>
let showLowSample = false;

$.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
    if (settings.nTable.id !== 'lineups-table' && settings.nTable.id !== 'players-table') return true;
    if (showLowSample) return true;
    return !$(settings.aoData[dataIndex].nTr).hasClass('low-sample');
});

let lineupsTable = $('#lineups-table').DataTable({
    autoWidth: false,
    paging: false,
    searching: true,
    info: false,
    order: [[6, 'desc']],
    dom: 't'
});
let playersTable = $('#players-table').DataTable({
    autoWidth: false,
    paging: false,
    searching: true,
    info: false,
    order: [[6, 'desc']],
    dom: 't'
});

$('#toggle-low-sample').on('change', function() {
    showLowSample = this.checked;
    lineupsTable.draw();
    playersTable.draw();
});

document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el) {
    new bootstrap.Tooltip(el);
});

$('#lineups-table').on('click', '.lineup-toggle', function(e) {
    e.preventDefault();
    let $toggle = $(this);
    let $hidden = $toggle.siblings('.lineup-hidden');
    let expanded = !$hidden.hasClass('d-none');
    $hidden.toggleClass('d-none');
    $toggle.text(expanded ? $toggle.data('more-text') : $toggle.data('less-text'));
});
</script>
@endsection
