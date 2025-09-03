@extends('layouts.main')

@section('body-id', 'player')

@section('content')
    <div class="container main-content">

        <div class="rounded rounded-3 bg-white p-4 mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('players.index') }}">Players</a></li>
                    <li class="breadcrumb-item active">{{ $player->name }}</li>
                </ol>
            </nav>
        </div>

        {{-- Chart Row --}}
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="rounded rounded-3 bg-white p-4 mb-4">
                    <h3>Goals</h3>
                    <div class="row">
                        <div class="col-12 col-md-7">
                            <canvas id="goals-chart"></canvas>
                        </div>
                        <div class="col-12 col-md-5">
                            <div id="goals-legend">
                                <ul class="list-unstyled">
                                @php $c = 0; @endphp
                                @foreach($charts['goals']['array'] as $arr)
                                    @php if($c == 10) $c = 1; else $c++; @endphp
                                    <li class="position-relative my-2 ps-5">
                                        <div class="position-absolute top-0 start-0" style="border:8px solid var(--bs-chart{{ $c }}); border-radius:25px; height:25px; width:25px"></div>
                                        <div class="">
                                            <b class="pe-2">{{ $arr['label'] }}</b>
                                            <span class="small text-muted">{{ $arr['data'] }}</span>
                                        </div>
                                    </li>
                                @endforeach
                                </ul>
                            </div>
                            <script>
                            let goalChart = document.getElementById('goals-chart');
                            new Chart(goalChart, {
                                type: 'doughnut',
                                data: {
                                    labels: [{!! $charts['goals']['labels'] !!}],
                                    datasets: [{
                                        data: [{!! $charts['goals']['data'] !!}],
                                        backgroundColor: $chartColors,
                                        borderRadius: 10,
                                        borderWidth: 5, 
                                        borderColor: '#ffffff',
                                    }]
                                },
                                options: {
                                    animation: false,
                                    cutout: '65%',
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: { display: false },
                                        tooltip: {
                                            displayColors: false,
                                            bodyFont: {size: 18},
                                            footerFont: {size: 18},
                                            callbacks: {
                                                footer: function(item) {
                                                    let sum = 0;
                                                    let arr = item[0].dataset.data;
                                                    arr.map(data => {
                                                        sum += Number(data);
                                                    });

                                                    let percentage = (item[0].parsed * 100 / sum).toFixed(2) + '%';
                                                    return percentage;
                                                }
                                            }
                                        }
                                    }
                                },
                            });
                            </script>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="rounded rounded-3 bg-white p-4 mb-4">
                    <h3>Assists</h3>
                    <div class="row">
                        <div class="col-12 col-md-7">
                            <canvas id="assists-chart"></canvas>
                        </div>
                        <div class="col-12 col-md-5">
                            <div id="assists-legend">
                                <ul class="list-unstyled">
                                @php $c = 0; @endphp
                                @foreach($charts['assists']['array'] as $arr)
                                    @php if($c == 10) $c = 1; else $c++; @endphp
                                    <li class="position-relative my-2 ps-5">
                                        <div class="position-absolute top-0 start-0" style="border:8px solid var(--bs-chart{{ $c }}); border-radius:25px; height:25px; width:25px"></div>
                                        <div class="">
                                            <b class="pe-2">{{ $arr['label'] }}</b>
                                            <span class="small text-muted">{{ $arr['data'] }}</span>
                                        </div>
                                    </li>
                                @endforeach
                                </ul>
                            </div>
                            <script>
                            let assistChart = document.getElementById('assists-chart');
                            new Chart(assistChart, {
                                type: 'doughnut',
                                data: {
                                    labels: [{!! $charts['assists']['labels'] !!}],
                                    datasets: [{
                                        data: [{!! $charts['assists']['data'] !!}],
                                        backgroundColor: $chartColors,
                                        borderRadius: 10,
                                        borderWidth: 5, 
                                        borderColor: '#ffffff',
                                    }]
                                },
                                options: {
                                    animation: false,
                                    cutout: '65%',
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: { display: false },
                                        tooltip: {
                                            displayColors: false,
                                            bodyFont: {size: 18},
                                            footerFont: {size: 18},
                                            callbacks: {
                                                footer: function(item) {
                                                    let sum = 0;
                                                    let arr = item[0].dataset.data;
                                                    arr.map(data => {
                                                        sum += Number(data);
                                                    });

                                                    let percentage = (item[0].parsed * 100 / sum).toFixed(2) + '%';
                                                    return percentage;
                                                }
                                            }
                                        }
                                    }
                                },
                            });
                            </script>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Chart Row --}}
        <div class="rounded rounded-3 bg-white p-4 mb-4">
            <h3>Over Time</h3>
            <canvas id="ga-time-chart" style="max-height:250px"></canvas>
            <script>
            let timeChart = document.getElementById('ga-time-chart');
            new Chart(timeChart, {
                type: 'line',
                data: {
                    labels: [{!! $charts['goals']['labels'] !!}],
                    datasets: [
                        {
                            label: 'Goals',
                            data: [{!! $charts['goals']['data'] !!}],
                            borderColor: $chartColors[0],
                            backgroundColor: $chartColors[0],
                            pointStyle: false,
                        },
                        {
                            label: 'Assists',
                            data: [{!! $charts['assists']['data'] !!}],
                            borderColor: $chartColors[5],
                            backgroundColor: $chartColors[5],
                            pointStyle: false,
                        }
                    ]
                },
                options: {
                    animation: false,
                    interaction: {
                        intersect: false,
                        mode: 'index',
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            bodyFont: {size: 18},
                            footerFont: {size: 18},
                        }
                    }
                },
            });
            </script>
        </div>

        {{-- Table --}}
        <div class="rounded rounded-3 bg-white p-4 mb-4">
            <div class="table-responsive">
                <table class="table table-hover table-borderless">
                    <thead class="small">
                        <tr class="text-center">
                            <th class="border-end border-secondary-subtle"></th>
                            <th class="border-end border-secondary-subtle" colspan="4">Playing Time</th>
                            <th class="border-end border-secondary-subtle" colspan="4">Performance</th>
                            <th class="border-end border-secondary-subtle d-none d-lg-table-cell" colspan="4">Per Game</th>
                            <th></th>
                        </tr>
                        <tr class="text-end">
                            <th class="text-start border-end border-secondary-subtle">Season</th>
                            <th >Games</th>
                            <th data-bs-toggle="tooltip" data-bs-title="Percentage of games started">Starts</th>
                            <th class="text-center" data-bs-toggle="tooltip" data-bs-title="Most common starting position">Pos</th>
                            <th class="border-end border-secondary-subtle" data-bs-toggle="tooltip" data-bs-title="Percentage of playing time">Time</th>
                            <th data-bs-toggle="tooltip" data-bs-title="Total Shots">Sh</th>
                            <th data-bs-toggle="tooltip" data-bs-title="Total Shots on Target">Sot</th>
                            <th data-bs-toggle="tooltip" data-bs-title="Total Goals">Gls</th>
                            <th class="border-end border-secondary-subtle" data-bs-toggle="tooltip" data-bs-title="Total Assists">Ast</th>
                            <th class="d-none d-lg-table-cell" data-bs-toggle="tooltip" data-bs-title="Shots Per Game">Sh</th>
                            <th class="d-none d-lg-table-cell" data-bs-toggle="tooltip" data-bs-title="Shots on Target Per Game">Sot</th>
                            <th class="d-none d-lg-table-cell" data-bs-toggle="tooltip" data-bs-title="Goals Per Game">Gls</th>
                            <th class="d-none d-lg-table-cell border-end border-secondary-subtle" data-bs-toggle="tooltip" data-bs-title="Assists Per Game">Ast</th>
                            <th class="text-start">Details</th>
                        </tr>
                    </thead>
                    <tbody class="border-top border-secondary">
                    @foreach($stats['seasons'] as $season => $s)
                        <tr class="text-end">
                            <td class="text-start border-end border-secondary-subtle">{{ $season }}</td>
                            <td>{{ $s['games'] }}</td>
                            <td>
                            @if($s['games'] && $s['events'])
                                <span data-bs-toggle="tooltip" data-bs-title="{{ $s['starts'] }} out of {{ $s['games'] }} games">
                                    {{ number_format(($s['starts'] / $s['games']) * 100) }}&percnt;
                                </span>
                            @endif
                            </td>
                            <td>
                            @if($s['position']['total'])
                                @php arsort($s['position']['positions']); reset($s['position']['positions']); @endphp
                                <a tabindex="0" class="link-secondary link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover" 
                                    data-bs-toggle="popover" data-trigger="focus" data-bs-title="Positions" 
                                    data-bs-content=" @foreach($s['position']['positions'] as $pos => $count) {{ $pos }} {{ number_format(($count / $s['position']['total']) * 100) }}&percnt;<br/> @endforeach ">
                                    {{ key($s['position']['positions']) }}
                                </a>
                            @endif
                            </td>
                            <td class="border-end border-secondary-subtle">
                            @if($s['playingTime']['possible_secs'])
                                <span data-bs-toggle="tooltip" 
                                    data-bs-title="{{ $s['playingTime']['minutes'] }} out of {{ $s['playingTime']['possible_mins'] }} mins">
                                    {{ number_format(($s['playingTime']['minutes'] / $s['playingTime']['possible_mins']) * 100) }}&percnt;
                                </span>
                            @endif
                            </td>
                            <td>@if($s['events']){{ $s['shots'] }}@endif</td>
                            <td>@if($s['events']){{ $s['shots_on'] }}@endif</td>
                            <td class="text-info">@if($s['events']){{ $s['goals'] }}@endif</td>
                            <td class="border-end border-secondary-subtle">@if($s['events']){{ $s['assists'] }}@endif</td>
                            <td class="d-none d-lg-table-cell">@if($s['events']){{ number_format($s['shots'] / $s['games'], 2) }}@endif</td>
                            <td class="d-none d-lg-table-cell">@if($s['events']){{ number_format($s['shots_on'] / $s['games'], 2)}}@endif</td>
                            <td class="d-none d-lg-table-cell">@if($s['events']){{ number_format($s['goals'] / $s['games'], 2) }}@endif</td>
                            <td class="d-none d-lg-table-cell border-end border-secondary-subtle">@if($s['events']){{ number_format($s['assists'] / $s['games'], 2) }}@endif</td>
                            <td class="text-start">
                                <a href="{{ route('players.seasons.show', ['player' => $stats['_player_id'], 'season' => $s['_id']]) }}">Details</a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-top border-secondary fs-5 text-end">
                            <td class="text-start border-end border-secondary-subtle">Total</td>
                            <td>{{ $stats['totals']['all']['games'] }}</td>
                            <td>
                            @if($stats['totals']['all']['games'])
                                <span data-bs-toggle="tooltip" data-bs-title="{{ $stats['totals']['all']['starts'] }} out of {{ $stats['totals']['all']['games'] }} games">
                                    {{ number_format(($stats['totals']['all']['starts'] / $stats['totals']['all']['games']) * 100) }}&percnt;
                                </span>
                            @endif
                            </td>
                            <td>{{-- $stats['totals']['all']['position'] --}}</td>
                            <td class="border-end border-secondary-subtle">
                            @if($stats['totals']['all']['playingTime']['possible_secs'])
                                <span data-bs-toggle="tooltip" 
                                    data-bs-title="{{ $stats['totals']['all']['playingTime']['minutes'] }} out of {{ $stats['totals']['all']['playingTime']['possible_mins'] }} mins">
                                    {{ number_format(($stats['totals']['all']['playingTime']['minutes'] / $stats['totals']['all']['playingTime']['possible_mins']) * 100) }}&percnt;
                                </span>
                            @endif
                            <td>{{ $stats['totals']['all']['shots'] }}</td>
                            <td>{{ $stats['totals']['all']['shots_on'] }}</td>
                            <td class="text-info">{{ $stats['totals']['all']['goals'] }}</td>
                            <td class="border-end border-secondary-subtle">{{ $stats['totals']['all']['assists'] }}</td>
                            <td class="d-none d-lg-table-cell">
                            @if($stats['totals']['all']['games'])
                                {{ number_format($stats['totals']['all']['shots'] / $stats['totals']['all']['games'], 2) }}
                            @endif
                            </td>
                            <td class="d-none d-lg-table-cell">
                            @if($stats['totals']['all']['games'])
                                {{ number_format($stats['totals']['all']['shots_on'] / $stats['totals']['all']['games'], 2)}}
                            @endif
                            </td>
                            <td class="d-none d-lg-table-cell">
                            @if($stats['totals']['all']['games'])
                                {{ number_format($stats['totals']['all']['goals'] / $stats['totals']['all']['games'], 2) }}
                            @endif
                            </td>
                            <td class="d-none d-lg-table-cell border-end border-secondary-subtle">
                            @if($stats['totals']['all']['games'])
                                {{ number_format($stats['totals']['all']['assists'] / $stats['totals']['all']['games'], 2) }}
                            @endif
                            </td>
                            <td class="text-start">Details</td>
                        </tr>
                    </tfoot>
            @foreach($stats['totals'] as $type => $s)
                @if($s['games'] && $type != 'all')
                    <tfoot>
                        <tr class="border-top border-secondary fst-italic text-end">
                            <td class="text-start border-end border-secondary-subtle">{{ $type }}</td>
                            <td>{{ $s['games'] }}</td>
                            <td>
                            @if($s['games'])
                                <span data-bs-toggle="tooltip" data-bs-title="{{ $s['starts'] }} out of {{ $s['games'] }} games">
                                    {{ number_format(($s['starts'] / $s['games']) * 100) }}&percnt;
                                </span>
                            @endif
                            </td>
                            <td>{{-- $s['position'] --}}</td>
                            <td class="border-end border-secondary-subtle">
                            @if($s['playingTime']['possible_secs'])
                                <span data-bs-toggle="tooltip" 
                                    data-bs-title="{{ $s['playingTime']['minutes'] }} out of {{ $s['playingTime']['possible_mins'] }} mins">
                                    {{ number_format(($s['playingTime']['minutes'] / $s['playingTime']['possible_mins']) * 100) }}&percnt;
                                </span>
                            @endif
                            </td>
                            <td>{{ $s['shots'] }}</td>
                            <td>{{ $s['shots_on'] }}</td>
                            <td class="text-info">{{ $s['goals'] }}</td>
                            <td class="border-secondary-subtle">{{ $s['assists'] }}</td>
                            <td class="d-none d-lg-table-cell">{{ number_format($s['shots'] / $s['games'], 2) }}</td>
                            <td class="d-none d-lg-table-cell">{{ number_format($s['shots_on'] / $s['games'], 2)}}</td>
                            <td class="d-none d-lg-table-cell">{{ number_format($s['goals'] / $s['games'], 2) }}</td>
                            <td class="d-none d-lg-table-cell border-end border-end border-secondary-subtle">{{ number_format($s['assists'] / $s['games'], 2) }}</td>
                            <td class="text-start">Details</td>
                        </tr>
                    </tfoot>
                @endif
            @endforeach
                </table>
            </div>
        </div>

    </div>

    <script>
    $(document).ready(function() {
        var options = { html: true };
        const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]')
        const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl, options))
    });
    </script>
@endsection
