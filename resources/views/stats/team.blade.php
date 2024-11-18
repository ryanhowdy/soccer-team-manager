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

        <div class="fw-bold text-secondary fs-5 ps-1 pb-2">Team Stats</div>

        <div class="row">

            <div class="col-12 col-md-6">
                <div class="rounded rounded-3 bg-white p-4 mb-3">
                    {{-- w/d/l table --}}
                    <table class="table">
                        <thead>
                            <tr class="text-center">
                                <th></th>
                                <th>Games</th>
                                <th>Win</th>
                                <th>Draw</th>
                                <th>Loss</th>
                                <th>
                                    <span class="text-success">W</span>
                                    <span class="text-primary-dark">D</span>
                                    <span class="text-danger">L</span>
                                    %
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Home</td>
                                <td class="text-end">{{ $stats['homeaway']['home']['games'] }}</td>
                                <td class="text-end">{{ $stats['homeaway']['home']['wins'] }}</td>
                                <td class="text-end">{{ $stats['homeaway']['home']['draws'] }}</td>
                                <td class="text-end">{{ $stats['homeaway']['home']['losses'] }}</td>
                                <td class="ps-4 align-middle">
                                @if($stats['homeaway']['home']['games'])
                                    <div class="progress-stacked" style="height: 5px; min-width:100px;">
                                        <div class="progress" role="progressbar" style="width: {{ round(($stats['homeaway']['home']['wins'] / $stats['homeaway']['home']['games']) * 100) }}%">
                                            <div class="progress-bar bg-success"></div>
                                        </div>
                                        <div class="progress" role="progressbar" style="width: {{ round(($stats['homeaway']['home']['draws'] / $stats['homeaway']['home']['games']) * 100) }}%">
                                            <div class="progress-bar bg-primary-dark"></div>
                                        </div>
                                        <div class="progress" role="progressbar" style="width: {{ round(($stats['homeaway']['home']['losses'] / $stats['homeaway']['home']['games']) * 100) }}%">
                                            <div class="progress-bar bg-danger"></div>
                                        </div>
                                    </div>
                                @endif
                                </td>
                            </tr>
                            <tr>
                                <td>Away</td>
                                <td class="text-end">{{ $stats['homeaway']['away']['games'] }}</td>
                                <td class="text-end">{{ $stats['homeaway']['away']['wins'] }}</td>
                                <td class="text-end">{{ $stats['homeaway']['away']['draws'] }}</td>
                                <td class="text-end">{{ $stats['homeaway']['away']['losses'] }}</td>
                                <td class="ps-4 align-middle">
                                @if($stats['homeaway']['away']['games'])
                                    <div class="progress-stacked" style="height: 5px; min-width:100px;">
                                        <div class="progress" role="progressbar" style="width: {{ round(($stats['homeaway']['away']['wins'] / $stats['homeaway']['away']['games']) * 100) }}%">
                                            <div class="progress-bar bg-success"></div>
                                        </div>
                                        <div class="progress" role="progressbar" style="width: {{ round(($stats['homeaway']['away']['draws'] / $stats['homeaway']['away']['games']) * 100) }}%">
                                            <div class="progress-bar bg-primary-dark"></div>
                                        </div>
                                        <div class="progress" role="progressbar" style="width: {{ round(($stats['homeaway']['away']['losses'] / $stats['homeaway']['away']['games']) * 100) }}%">
                                            <div class="progress-bar bg-danger"></div>
                                        </div>
                                    </div>
                                @endif
                                </td>
                            </tr>
                            <tr class="fw-bold">
                                <td>Overall</td>
                                <td class="text-end">{{ $stats['homeaway']['overall']['games'] }}</td>
                                <td class="text-end table-success">{{ $stats['homeaway']['overall']['wins'] }}</td>
                                <td class="text-end table-light">{{ $stats['homeaway']['overall']['draws'] }}</td>
                                <td class="text-end table-danger">{{ $stats['homeaway']['overall']['losses'] }}</td>
                                <td class="ps-4 align-middle">
                                @if($stats['homeaway']['overall']['games'])
                                    <div class="progress-stacked" style="height: 5px; min-width:100px;">
                                        <div class="progress" role="progressbar" style="width: {{ round(($stats['homeaway']['overall']['wins'] / $stats['homeaway']['overall']['games']) * 100) }}%">
                                            <div class="progress-bar bg-success"></div>
                                        </div>
                                        <div class="progress" role="progressbar" style="width: {{ round(($stats['homeaway']['overall']['draws'] / $stats['homeaway']['overall']['games']) * 100) }}%">
                                            <div class="progress-bar bg-primary-dark"></div>
                                        </div>
                                        <div class="progress" role="progressbar" style="width: {{ round(($stats['homeaway']['overall']['losses'] / $stats['homeaway']['overall']['games']) * 100) }}%">
                                            <div class="progress-bar bg-danger"></div>
                                        </div>
                                    </div>
                                @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="rounded rounded-3 bg-white p-4 mb-3">
                    {{-- stats table --}}
                    <table class="table table-bordered mb-0">
                        <thead>
                            <tr>
                                <th class="fw-bold text-muted">Stats</th>
                                <th class="text-center table-light">Overall</th>
                                <th class="text-center">Home</th>
                                <th class="text-center">Away</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="fw-bold">Win %</td>
                                <td class="text-center table-light">
                                    <span @class([
                                        "badge",
                                        "bg-success" => $stats['homeaway']['overall']['win_percent'] >= 75,
                                        "bg-success opacity-75" => $stats['homeaway']['overall']['win_percent'] >= 60 && $stats['homeaway']['overall']['win_percent'] < 75,
                                        "bg-success opacity-50" => $stats['homeaway']['overall']['win_percent'] >= 50 && $stats['homeaway']['overall']['win_percent'] < 60,
                                        "bg-danger opacity-75" => $stats['homeaway']['overall']['win_percent'] >= 40 && $stats['homeaway']['overall']['win_percent'] < 50,
                                        "bg-danger" => $stats['homeaway']['overall']['win_percent'] < 40,
                                        ])>{{ $stats['homeaway']['overall']['win_percent'] }}&#37;</span>
                                </td>
                                <td class="text-center">
                                    <span @class([
                                        "badge",
                                        "bg-success" => $stats['homeaway']['home']['win_percent'] >= 75,
                                        "bg-success opacity-75" => $stats['homeaway']['home']['win_percent'] >= 60 && $stats['homeaway']['home']['win_percent'] < 75,
                                        "bg-success opacity-50" => $stats['homeaway']['home']['win_percent'] >= 50 && $stats['homeaway']['home']['win_percent'] < 60,
                                        "bg-danger opacity-75" => $stats['homeaway']['home']['win_percent'] >= 40 && $stats['homeaway']['home']['win_percent'] < 50,
                                        "bg-danger" => $stats['homeaway']['home']['win_percent'] < 40,
                                        ])>{{ $stats['homeaway']['home']['win_percent'] }}&#37;</span>
                                </td>
                                <td class="text-center">
                                    <span @class([
                                        "badge",
                                        "bg-success" => $stats['homeaway']['away']['win_percent'] >= 75,
                                        "bg-success opacity-75" => $stats['homeaway']['away']['win_percent'] >= 60 && $stats['homeaway']['away']['win_percent'] < 75,
                                        "bg-success opacity-50" => $stats['homeaway']['away']['win_percent'] >= 50 && $stats['homeaway']['away']['win_percent'] < 60,
                                        "bg-danger opacity-75" => $stats['homeaway']['away']['win_percent'] >= 40 && $stats['homeaway']['away']['win_percent'] < 50,
                                        "bg-danger" => $stats['homeaway']['away']['win_percent'] < 40,
                                    ])>{{ $stats['homeaway']['away']['win_percent'] }}&#37;</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Goals</td>
                                <td class="text-center table-light">{{ $stats['homeaway']['overall']['goals'] }}</td>
                                <td class="text-center">{{ $stats['homeaway']['home']['goals'] }}</td>
                                <td class="text-center">{{ $stats['homeaway']['away']['goals'] }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">xG</td>
                                <td class="text-center table-light">{{ $stats['homeaway']['overall']['xg'] }}</td>
                                <td class="text-center">{{ $stats['homeaway']['home']['xg'] }}</td>
                                <td class="text-center">{{ $stats['homeaway']['away']['xg'] }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Goals Against</td>
                                <td class="text-center table-light">{{ $stats['homeaway']['overall']['goals_against'] }}</td>
                                <td class="text-center">{{ $stats['homeaway']['home']['goals_against'] }}</td>
                                <td class="text-center">{{ $stats['homeaway']['away']['goals_against'] }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">xG Against</td>
                                <td class="text-center table-light">{{ $stats['homeaway']['overall']['xg_against'] }}</td>
                                <td class="text-center">{{ $stats['homeaway']['home']['xg_against'] }}</td>
                                <td class="text-center">{{ $stats['homeaway']['away']['xg_against'] }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Shot Conversion</td>
                                <td class="text-center table-light">
                                    <span @class([
                                        "badge",
                                        "bg-success" => $stats['homeaway']['overall']['shot_conversion'] >= 30,
                                        "bg-success opacity-75" => $stats['homeaway']['overall']['shot_conversion'] >= 20 && $stats['homeaway']['overall']['shot_conversion'] < 30,
                                        "bg-success opacity-50" => $stats['homeaway']['overall']['shot_conversion'] >= 10 && $stats['homeaway']['overall']['shot_conversion'] < 20,
                                        "bg-danger opacity-75" => $stats['homeaway']['overall']['shot_conversion'] >= 5 && $stats['homeaway']['overall']['shot_conversion'] < 10,
                                        "bg-danger" => $stats['homeaway']['overall']['shot_conversion'] < 5,
                                    ])>{{ $stats['homeaway']['overall']['shot_conversion'] }}&#37;</span>
                                </td>
                                <td class="text-center">
                                    <span @class([
                                        "badge",
                                        "bg-success" => $stats['homeaway']['home']['shot_conversion'] >= 30,
                                        "bg-success opacity-75" => $stats['homeaway']['home']['shot_conversion'] >= 20 && $stats['homeaway']['home']['shot_conversion'] < 30,
                                        "bg-success opacity-50" => $stats['homeaway']['home']['shot_conversion'] >= 10 && $stats['homeaway']['home']['shot_conversion'] < 20,
                                        "bg-danger opacity-75" => $stats['homeaway']['home']['shot_conversion'] >= 5 && $stats['homeaway']['home']['shot_conversion'] < 10,
                                        "bg-danger" => $stats['homeaway']['home']['shot_conversion'] < 5,
                                    ])>{{ $stats['homeaway']['home']['shot_conversion'] }}&#37;</span>
                                </td>
                                <td class="text-center">
                                    <span @class([
                                        "badge",
                                        "bg-success" => $stats['homeaway']['away']['shot_conversion'] >= 30,
                                        "bg-success opacity-75" => $stats['homeaway']['away']['shot_conversion'] >= 20 && $stats['homeaway']['away']['shot_conversion'] < 30,
                                        "bg-success opacity-50" => $stats['homeaway']['away']['shot_conversion'] >= 10 && $stats['homeaway']['away']['shot_conversion'] < 20,
                                        "bg-danger opacity-75" => $stats['homeaway']['away']['shot_conversion'] >= 5 && $stats['homeaway']['away']['shot_conversion'] < 10,
                                        "bg-danger" => $stats['homeaway']['away']['shot_conversion'] < 5,
                                    ])>{{ $stats['homeaway']['away']['shot_conversion'] }}&#37;</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Clean Sheets</td>
                                <td class="text-center table-light">{{ $stats['homeaway']['overall']['clean'] }}</td>
                                <td class="text-center">{{ $stats['homeaway']['home']['clean'] }}</td>
                                <td class="text-center">{{ $stats['homeaway']['away']['clean'] }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">Per Game</td>
                                <td class="text-center table-light"></td>
                                <td class="text-center"></td>
                                <td class="text-center"></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">GPG</td>
                                <td class="text-center table-light">
                                @if($stats['homeaway']['overall']['games'])
                                    {{ round($stats['homeaway']['overall']['goals'] / $stats['homeaway']['overall']['games'], 1) }}
                                @endif
                                </td>
                                <td class="text-center">
                                @if($stats['homeaway']['home']['games'])
                                    {{ round($stats['homeaway']['home']['goals'] / $stats['homeaway']['home']['games'], 1) }}
                                @endif
                                </td>
                                <td class="text-center">
                                @if($stats['homeaway']['away']['games'])
                                    {{ round($stats['homeaway']['away']['goals'] / $stats['homeaway']['away']['games'], 1) }}
                                @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold">GPG Against</td>
                                <td class="text-center table-light">
                                @if($stats['homeaway']['overall']['games'])
                                    {{ round($stats['homeaway']['overall']['goals_against'] / $stats['homeaway']['overall']['games'], 1) }}
                                @endif
                                </td>
                                <td class="text-center">
                                @if($stats['homeaway']['home']['games'])
                                    {{ round($stats['homeaway']['home']['goals_against'] / $stats['homeaway']['home']['games'], 1) }}
                                @endif
                                </td>
                                <td class="text-center">
                                @if($stats['homeaway']['away']['games'])
                                    {{ round($stats['homeaway']['away']['goals_against'] / $stats['homeaway']['away']['games'], 1) }}
                                @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-12 col-md-6">
                <div class="game-listing-small rounded rounded-3 bg-white p-4 mb-3">
                @foreach($results as $result)
                    <a href="{{ route('games.show', ['id' => $result->id]) }}" 
                        class="home-v-away d-grid align-items-center justify-content-center mb-3 text-decoration-none rounded rounded-2 text-dark">
                        <div class="home-team d-flex align-items-center justify-content-end">
                            <div class="me-2">{{ $result->homeTeam->name }}</div>
                            <img class="logo img-fluid" data-bs-toggle="tooltip" data-bs-title="{{ $result->homeTeam->club->name }}" 
                                src="{{ asset($result->homeTeam->club->logo) }}"/>
                        </div>
                        <div class="score text-center">
                            <span @class([
                                'badge',
                                'rounded-pill',
                                'text-white',
                                'bg-success'   => ($result->win_draw_loss == 'W'),
                                'bg-secondary' => ($result->win_draw_loss == 'D'),
                                'bg-danger' => ($result->win_draw_loss == 'L'),
                            ])>{{ $result->home_team_score }} - {{ $result->away_team_score }}</span>
                        </div>
                        <div class="away-team d-flex align-items-center">
                            <img class="logo img-fluid" data-bs-toggle="tooltip" data-bs-title="{{ $result->awayTeam->club->name }}"
                                src="{{ asset($result->awayTeam->club->logo) }}"/>
                            <div class="ms-2">{{ $result->awayTeam->name }}</div>
                        </div>
                    </a>
                @endforeach
                </div>
            </div>

        </div>

        <div class="fw-bold text-secondary fs-5 ps-1 pb-2">Player Stats</div>

            {{-- players stats table --}}
            <div class="rounded rounded-3 bg-white py-2 px-3 mb-3">
                <table id="player-stats" class="table table-hover table-sm small">
                    <thead>
                        <tr class="text-center">
                            <th class="border-end"></th>
                            <th colspan="2" class="border-end">Playing Time</th>
                            <th colspan="7" class="border-end">Shooting</th>
                            <th colspan="3" class="border-end">Percentages</th>
                            <th colspan="4">Misc</th>
                        </tr>
                        <tr class="text-center">
                            <th class="text-start border-end">Player</th>
                            <th>Starts</th>
                            <th class="border-end" data-bs-toggle="tooltip" data-bs-title="Minutes Played">Min</th>
                            <th data-bs-toggle="tooltip" data-bs-title="Goals">Gls</th>
                            <th data-bs-toggle="tooltip" data-bs-title="Assists">Ast</th>
                            <th data-bs-toggle="tooltip" data-bs-title="Total Shots">Sh</th>
                            <th data-bs-toggle="tooltip" data-bs-title="Shots On Target">Sot</th>
                            <th data-bs-toggle="tooltip" data-bs-title="Chance Creation (Passes lead to Goals or Shots)">Cha</th>
                            <th data-bs-toggle="tooltip" data-bs-title="Free Kicks">FK</th>
                            <th class="border-end" data-bs-toggle="tooltip" data-bs-title="Penalty Kicks">PK</th>
                            <th data-bs-toggle="tooltip" data-bs-title="% of Team Total Goals">Gls</th>
                            <th data-bs-toggle="tooltip" data-bs-title="% of Team Total Assists">Ast</th>
                            <th class="border-end" data-bs-toggle="tooltip" data-bs-title="Shot Conversion">ShCv</th>
                            <th data-bs-toggle="tooltip" data-bs-title="Offsides">Off</th>
                            <th data-bs-toggle="tooltip" data-bs-title="Tackles">Tkl</th>
                            <th data-bs-toggle="tooltip" data-bs-title="Yellow Cards">YCd</th>
                            <th data-bs-toggle="tooltip" data-bs-title="Red Cards">RCd</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($stats['players'] as $name => $player)
                        <tr class="text-end">
                            <td class="text-start border-end">{{ $name }}</td>
                            <td @class(['text-secondary' => $player['starts'] == 0])>{{ $player['starts'] }}</td>
                            <td @class(['border-end', 'text-secondary' => $player['time']['minutes'] == 0])">{{ $player['time']['minutes'] }}</td>
                            <td @class(['text-secondary' => $player['goals'] == 0])>{{ $player['goals'] }}</td>
                            <td @class(['text-secondary' => $player['assists'] == 0])>{{ $player['assists'] }}</td>
                            <td @class(['text-secondary' => $player['shots'] == 0])>{{ $player['shots'] }}</td>
                            <td @class(['text-secondary' => $player['shotsOn'] == 0])>{{ $player['shotsOn'] }}</td>
                            <td @class(['text-secondary' => $player['chances'] == 0])>{{ $player['chances'] }}</td>
                            <td @class(['text-secondary' => $player['fks'] == 0])>{{ $player['fks'] }}</td>
                            <td @class(['border-end', 'text-secondary' => $player['pks'] == 0])>{{ $player['pks'] }}</td>
                            <td @class(['text-secondary' => $player['percent']['goals'] == 0])>{{ $player['percent']['goals'] }}</td>
                            <td @class(['text-secondary' => $player['percent']['assists'] == 0])>{{ $player['percent']['assists'] }}</td>
                            <td @class(['border-end', 'text-secondary' => $player['percent']['shotConversion'] == 0])>{{ $player['percent']['shotConversion'] }}</td>
                            <td @class(['text-secondary' => $player['offsides'] == 0])>{{ $player['offsides'] }}</td>
                            <td @class(['text-secondary' => $player['tackles'] == 0])>{{ $player['tackles'] }}</td>
                            <td @class(['text-secondary' => $player['yCards'] == 0])>{{ $player['yCards'] }}</td>
                            <td @class(['text-secondary' => $player['rCards'] == 0])>{{ $player['rCards'] }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

        </div>

    </div><!--/container-->

<script>
$('#player-stats.table').DataTable({
    autoWidth: false,
    paging: false,
    searching: false,
    info: false,
    order: [[0, 'asc']]
});
</script>
@endsection
