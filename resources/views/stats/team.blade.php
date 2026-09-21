@extends('layouts.main')

@section('body-id', 'stats')
@section('page-title', 'Team Statistics')
@section('page-desc', "Learn all about a team")

@section('content')
    <div class="container main-content">

        <div class="d-flex justify-content-between mb-3">
            <div><h2>Team Stats</h2></div>
            <div class="d-flex gap-2 align-items-center justify-content-end">
                @include('partials.season-filter', ['filterRoute' => 'stats.teams.index'])
            </div>
        </div>

        <div class="row">

            <div class="col-12 col-md-6">
                <div class="rounded rounded-3 bg-white p-4 mb-3">
                    {{-- w/d/l table --}}
                    <table class="table">
                        <thead>
                            <tr class="text-center">
                                <th></th>
                                <th>Games</th>
                                <th class="d-none d-sm-table-cell d-md-none d-lg-table-cell">Win</th>
                                <th class="d-none d-sm-table-cell d-md-none d-lg-table-cell">Draw</th>
                                <th class="d-none d-sm-table-cell d-md-none d-lg-table-cell">Loss</th>
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
                                <td class="text-end d-none d-sm-table-cell d-md-none d-lg-table-cell">{{ $stats['homeaway']['home']['wins'] }}</td>
                                <td class="text-end d-none d-sm-table-cell d-md-none d-lg-table-cell">{{ $stats['homeaway']['home']['draws'] }}</td>
                                <td class="text-end d-none d-sm-table-cell d-md-none d-lg-table-cell">{{ $stats['homeaway']['home']['losses'] }}</td>
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
                                <td class="text-end d-none d-sm-table-cell d-md-none d-lg-table-cell">{{ $stats['homeaway']['away']['wins'] }}</td>
                                <td class="text-end d-none d-sm-table-cell d-md-none d-lg-table-cell">{{ $stats['homeaway']['away']['draws'] }}</td>
                                <td class="text-end d-none d-sm-table-cell d-md-none d-lg-table-cell">{{ $stats['homeaway']['away']['losses'] }}</td>
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
                                <td class="text-end table-success d-none d-sm-table-cell d-md-none d-lg-table-cell">{{ $stats['homeaway']['overall']['wins'] }}</td>
                                <td class="text-end table-light d-none d-sm-table-cell d-md-none d-lg-table-cell">{{ $stats['homeaway']['overall']['draws'] }}</td>
                                <td class="text-end table-danger d-none d-sm-table-cell d-md-none d-lg-table-cell">{{ $stats['homeaway']['overall']['losses'] }}</td>
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
                            {{-- Goals and xG each read as one number against
                                 the other, with the raw totals on hover. --}}
                            <tr>
                                <td class="fw-bold">Goal Diff</td>
                            @foreach(['overall', 'home', 'away'] as $side)
                                @php $diff = $stats['homeaway'][$side]['goals'] - $stats['homeaway'][$side]['goals_against']; @endphp
                                <td @class(['text-center', 'table-light' => $side === 'overall'])>
                                    <span data-bs-toggle="tooltip"
                                        data-bs-title="{{ $stats['homeaway'][$side]['goals'] }} goals, {{ $stats['homeaway'][$side]['goals_against'] }} goals against"
                                        @class([
                                            'text-success' => $diff > 0,
                                            'text-danger'  => $diff < 0,
                                        ])>{{ $diff > 0 ? '+' : '' }}{{ $diff }}</span>
                                </td>
                            @endforeach
                            </tr>
                            <tr>
                                <td class="fw-bold">xG Diff</td>
                            @foreach(['overall', 'home', 'away'] as $side)
                                @php $diff = round($stats['homeaway'][$side]['xg'] - $stats['homeaway'][$side]['xg_against'], 1); @endphp
                                <td @class(['text-center', 'table-light' => $side === 'overall'])>
                                    <span data-bs-toggle="tooltip"
                                        data-bs-title="{{ number_format($stats['homeaway'][$side]['xg'], 1) }} xG, {{ number_format($stats['homeaway'][$side]['xg_against'], 1) }} xG against"
                                        @class([
                                            'text-success' => $diff > 0,
                                            'text-danger'  => $diff < 0,
                                        ])>{{ $diff > 0 ? '+' : '' }}{{ number_format($diff, 1) }}</span>
                                </td>
                            @endforeach
                            </tr>
                            <tr>
                                <td class="fw-bold">Shot Conversion</td>
                                <td class="text-center table-light">{{ $stats['homeaway']['overall']['shot_conversion'] }}&#37;</td>
                                <td class="text-center">{{ $stats['homeaway']['home']['shot_conversion'] }}&#37;</td>
                                <td class="text-center">{{ $stats['homeaway']['away']['shot_conversion'] }}&#37;</td>
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
                        class="mb-3 text-decoration-none rounded rounded-2 text-dark">
                        <div class="small text-center text-secondary">{{ $result->date->inUserTimezone()->format('M j, Y') }}</div>
                        <div class="home-v-away d-grid align-items-center justify-content-center mb-3">
                            <div class="home-team d-flex align-items-center justify-content-end">
                                <div class="me-2 d-none d-lg-block">{{ $result->homeTeam->short_name }}</div>
                                <img class="logo img-fluid me-2 me-md-1 me-lg-0" data-bs-toggle="tooltip" data-bs-title="{{ $result->homeTeam->club->name }}" 
                                    src="{{ asset($result->homeTeam->club->logo) }}" onerror="this.onerror=null;this.src='{{ asset('img/logo_none.png') }}';"/>
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
                                <img class="logo img-fluid ms-2 ms-md-1 ms-lg-0" data-bs-toggle="tooltip" data-bs-title="{{ $result->awayTeam->club->name }}"
                                    src="{{ asset($result->awayTeam->club->logo) }}" onerror="this.onerror=null;this.src='{{ asset('img/logo_none.png') }}';"/>
                                <div class="ms-2 d-none d-lg-block">{{ $result->awayTeam->short_name }}</div>
                            </div>
                        </div>
                    </a>
                @endforeach
                </div>
            </div>

        </div>

        <div class="fw-bold text-secondary fs-5 ps-1 pb-2">Player Stats</div>

            {{-- players stats table --}}
            <div class="rounded rounded-3 bg-white py-2 px-3 mb-3">
                <div class="table-responsive">
                <table id="player-stats" class="table table-hover table-sm small">
                    <thead>
                        <tr class="text-center">
                            <th class="border-end"></th>
                            <th colspan="2" class="border-end">Playing Time</th>
                            <th colspan="9" class="border-end">Shooting</th>
                        @if($stats['fullGameMins'])
                            <th colspan="3" class="border-end" data-bs-toggle="tooltip"
                                data-bs-title="Per {{ $stats['fullGameMins'] }} minutes - how long a game has actually run in this filter">Per Full Game</th>
                        @endif
                            <th colspan="4" class="border-end">Percentages</th>
                            <th colspan="5">Misc</th>
                        </tr>
                        <tr class="text-center">
                            <th class="text-start border-end">Player</th>
                            <th>Starts</th>
                            <th class="border-end" data-bs-toggle="tooltip" data-bs-title="Minutes Played">Min</th>
                            <th data-bs-toggle="tooltip" data-bs-title="Goals">Gls</th>
                            <th data-bs-toggle="tooltip" data-bs-title="Assists">Ast</th>
                            <th data-bs-toggle="tooltip" data-bs-title="Total Shots">Sh</th>
                            <th data-bs-toggle="tooltip" data-bs-title="Shots On Target">Sot</th>
                            <th data-bs-toggle="tooltip" data-bs-title="Expected Goals (the quality of the chances taken)">xG</th>
                            <th data-bs-toggle="tooltip" data-bs-title="Goals minus Expected Goals - finishing above or below the quality of the chances taken">G-xG</th>
                            <th data-bs-toggle="tooltip" data-bs-title="Chance Creation (Passes that led to a goal or shot, penalties aside)">Cha</th>
                            <th data-bs-toggle="tooltip" data-bs-title="Free Kicks Taken">FK</th>
                            <th class="border-end" data-bs-toggle="tooltip" data-bs-title="Penalty Kicks">PK</th>
                        @if($stats['fullGameMins'])
                            <th data-bs-toggle="tooltip" data-bs-title="Goals per full game. Blank under {{ $stats['fullGameMins'] }} minutes played">Gls</th>
                            <th data-bs-toggle="tooltip" data-bs-title="Assists per full game. Blank under {{ $stats['fullGameMins'] }} minutes played">Ast</th>
                            <th class="border-end" data-bs-toggle="tooltip" data-bs-title="Expected Goals per full game. Blank under {{ $stats['fullGameMins'] }} minutes played">xG</th>
                        @endif
                            <th data-bs-toggle="tooltip" data-bs-title="% of Team Total Goals">Gls</th>
                            <th data-bs-toggle="tooltip" data-bs-title="% of Team Total Assists">Ast</th>
                            <th data-bs-toggle="tooltip" data-bs-title="% of Team Total Chances Created">Cha</th>
                            <th class="border-end" data-bs-toggle="tooltip" data-bs-title="Shot Conversion">ShCv</th>
                            <th data-bs-toggle="tooltip" data-bs-title="Offsides">Off</th>
                            <th data-bs-toggle="tooltip" data-bs-title="Tackles">Tkl</th>
                            <th data-bs-toggle="tooltip" data-bs-title="Yellow Cards">YCd</th>
                            <th data-bs-toggle="tooltip" data-bs-title="Red Cards">RCd</th>
                            <th data-bs-toggle="tooltip" data-bs-title="Average Player Rating">Rtg</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($stats['players'] as $name => $player)
                        @php $gxg = round($player['goals'] - $player['xg'], 1); @endphp
                        <tr class="text-end">
                            <td class="text-start border-end">{{ $name }}</td>
                            <td @class(['text-secondary' => $player['starts'] == 0])>{{ $player['starts'] }}</td>
                            <td @class(['border-end', 'text-secondary' => $player['time']['minutes'] == 0])">{{ $player['time']['minutes'] }}</td>
                            <td @class(['text-secondary' => $player['goals'] == 0])>{{ $player['goals'] }}</td>
                            <td @class(['text-secondary' => $player['assists'] == 0])>{{ $player['assists'] }}</td>
                            <td @class(['text-secondary' => $player['shots'] == 0])>{{ $player['shots'] }}</td>
                            <td @class(['text-secondary' => $player['shotsOn'] == 0])>{{ $player['shotsOn'] }}</td>
                            <td @class(['text-secondary' => $player['xg'] == 0])>{{ number_format($player['xg'], 1) }}</td>
                            <td data-order="{{ $gxg }}" @class([
                                'text-secondary' => $player['shots'] == 0,
                                'text-success'   => $player['shots'] > 0 && $gxg > 0,
                                'text-danger'    => $player['shots'] > 0 && $gxg < 0,
                            ])>
                            @if($player['shots'])
                                {{ $gxg > 0 ? '+' : '' }}{{ number_format($gxg, 1) }}
                            @endif
                            </td>
                            <td @class(['text-secondary' => $player['chances'] == 0])>{{ $player['chances'] }}</td>
                            <td @class(['text-secondary' => $player['fks'] == 0])>{{ $player['fks'] }}</td>
                            <td @class(['border-end', 'text-secondary' => $player['pks'] == 0])>{{ $player['pks'] }}</td>
                        @if($stats['fullGameMins'])
                            <td data-order="{{ $player['per']['goals'] ?? -1 }}" @class(['text-secondary' => !$player['per']['goals']])>
                                {{ $player['per']['goals'] !== null ? number_format($player['per']['goals'], 2) : '-' }}
                            </td>
                            <td data-order="{{ $player['per']['assists'] ?? -1 }}" @class(['text-secondary' => !$player['per']['assists']])>
                                {{ $player['per']['assists'] !== null ? number_format($player['per']['assists'], 2) : '-' }}
                            </td>
                            <td data-order="{{ $player['per']['xg'] ?? -1 }}" @class(['border-end', 'text-secondary' => !$player['per']['xg']])>
                                {{ $player['per']['xg'] !== null ? number_format($player['per']['xg'], 2) : '-' }}
                            </td>
                        @endif
                            <td @class(['text-secondary' => $player['percent']['goals'] == 0])>{{ $player['percent']['goals'] }}</td>
                            <td @class(['text-secondary' => $player['percent']['assists'] == 0])>{{ $player['percent']['assists'] }}</td>
                            <td @class(['text-secondary' => $player['percent']['chances'] == 0])>{{ $player['percent']['chances'] }}</td>
                            <td @class(['border-end', 'text-secondary' => $player['percent']['shotConversion'] == 0])>{{ $player['percent']['shotConversion'] }}</td>
                            <td @class(['text-secondary' => $player['offsides'] == 0])>{{ $player['offsides'] }}</td>
                            <td @class(['text-secondary' => $player['tackles'] == 0])>{{ $player['tackles'] }}</td>
                            <td @class(['text-secondary' => $player['yCards'] == 0])>{{ $player['yCards'] }}</td>
                            <td @class(['text-secondary' => $player['rCards'] == 0])>{{ $player['rCards'] }}</td>
                            <td data-order="{{ $player['rating'] ?? -1 }}">
                            @if($player['rating'] !== null)
                                @php $avg = $player['rating']; @endphp
                                <span class="badge rating-avg bg-{{ $avg < 3 ? 'danger' : ($avg < 5 ? 'warning' : ($avg < 6 ? 'secondary' : ($avg < 9 ? 'success bg-opacity-75' : 'success'))) }}"
                                    data-bs-toggle="tooltip"
                                    data-bs-title="Rated in {{ $player['rated'] }} {{ $player['rated'] == 1 ? 'game' : 'games' }}">{{ number_format($avg, 1) }}</span>
                            @else
                                <span class="text-secondary">-</span>
                            @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                </div>
            </div>

        @if($stats['keepers'])
            <div class="fw-bold text-secondary fs-5 ps-1 pb-2">Goalkeeping</div>

            {{-- keeper stats table --}}
            <div class="rounded rounded-3 bg-white py-2 px-3 mb-3">
                <div class="table-responsive">
                <table id="keeper-stats" class="table table-hover table-sm small">
                    <thead>
                        <tr class="text-center">
                            <th class="text-start border-end">Player</th>
                            <th data-bs-toggle="tooltip" data-bs-title="Games Kept Goal">GP</th>
                            <th data-bs-toggle="tooltip" data-bs-title="Saves">Sv</th>
                            <th data-bs-toggle="tooltip" data-bs-title="Goals Conceded While In Goal">GA</th>
                            <th data-bs-toggle="tooltip" data-bs-title="Save Percentage - saves out of the shots faced on target">Sv&#37;</th>
                            <th data-bs-toggle="tooltip" data-bs-title="Expected Goals Faced - the quality of the shots faced on target">xGF</th>
                            <th data-bs-toggle="tooltip" data-bs-title="Goals Prevented - Expected Goals Faced minus goals conceded. Above zero is saving more than the shots deserved">GP&#177;</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($stats['keepers'] as $name => $keeper)
                        <tr class="text-end">
                            <td class="text-start border-end">{{ $name }}</td>
                            <td>{{ $keeper['games'] }}</td>
                            <td @class(['text-secondary' => $keeper['saves'] == 0])>{{ $keeper['saves'] }}</td>
                            <td data-order="{{ $keeper['ga'] ?? -1 }}">
                            @if($keeper['ga'] !== null)
                                {{ $keeper['ga'] }}
                            @else
                                <span class="text-secondary" data-bs-toggle="tooltip"
                                    data-bs-title="No position was recorded in these games, so goals conceded cannot be charged to a keeper">-</span>
                            @endif
                            </td>
                            <td data-order="{{ $keeper['savePct'] ?? -1 }}">
                            @if($keeper['savePct'] !== null)
                                <span @class([
                                    'badge',
                                    'bg-success'            => $keeper['savePct'] >= 80,
                                    'bg-success opacity-75' => $keeper['savePct'] >= 70 && $keeper['savePct'] < 80,
                                    'bg-success opacity-50' => $keeper['savePct'] >= 60 && $keeper['savePct'] < 70,
                                    'bg-danger opacity-75'  => $keeper['savePct'] >= 50 && $keeper['savePct'] < 60,
                                    'bg-danger'             => $keeper['savePct'] < 50,
                                ])
                                @if($keeper['known'] < $keeper['games'])
                                    data-bs-toggle="tooltip" data-bs-title="From {{ $keeper['known'] }} of {{ $keeper['games'] }} games"
                                @endif
                                >{{ $keeper['savePct'] }}&#37;</span>
                            @else
                                <span class="text-secondary">-</span>
                            @endif
                            </td>
                            <td data-order="{{ $keeper['xgFaced'] ?? -1 }}" @class(['text-secondary' => !$keeper['xgFaced']])>
                                {{ $keeper['xgFaced'] !== null ? number_format($keeper['xgFaced'], 1) : '-' }}
                            </td>
                            <td data-order="{{ $keeper['prevented'] ?? -99 }}" @class([
                                'text-success' => $keeper['prevented'] !== null && $keeper['prevented'] > 0,
                                'text-danger'  => $keeper['prevented'] !== null && $keeper['prevented'] < 0,
                                'text-secondary' => $keeper['prevented'] === null,
                            ])>
                            @if($keeper['prevented'] !== null)
                                {{ $keeper['prevented'] > 0 ? '+' : '' }}{{ number_format($keeper['prevented'], 1) }}
                            @else
                                -
                            @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                </div>
            </div>
        @endif

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

$('#keeper-stats.table').DataTable({
    autoWidth: false,
    paging: false,
    searching: false,
    info: false,
    order: [[2, 'desc']]
});
</script>
@endsection
