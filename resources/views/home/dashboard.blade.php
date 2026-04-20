{{-- 1. Form Streak --}}
@if(count($dashboard['formStreak']))
<div class="rounded rounded-3 bg-white p-3 p-lg-4 mb-3">
    <div class="fw-bold text-secondary mb-2">Recent Form</div>
    <div class="d-flex gap-2 flex-wrap">
    @foreach($dashboard['formStreak'] as $wdl)
        <span @class([
            'badge',
            'rounded-circle',
            'd-inline-flex',
            'align-items-center',
            'justify-content-center',
            'text-white',
            'bg-success' => $wdl == 'W',
            'bg-warning' => $wdl == 'D',
            'bg-danger'  => $wdl == 'L',
        ]) style="width:32px;height:32px;">{{ $wdl }}</span>
    @endforeach
    </div>
</div>
@endif

{{-- 2. Season Record Summary --}}
@php
    $games = max($dashboard['seasonRecord']['games'], 1);
    $cleanSheetPct = round(($dashboard['seasonRecord']['clean_sheets'] / $games) * 100);
@endphp
<div class="row">
    <div class="col-6 col-md-2 mb-3">
        <div class="rounded rounded-3 bg-white p-3 p-lg-4 text-center">
            <div class="text-uppercase text-secondary small">Record</div>
            <div class="fw-bold fs-4 text-primary">{{ $dashboard['seasonRecord']['wins'] }}-{{ $dashboard['seasonRecord']['draws'] }}-{{ $dashboard['seasonRecord']['losses'] }}</div>
        </div>
    </div>
    <div class="col-6 col-md-2 mb-3">
        <div class="rounded rounded-3 bg-white p-3 p-lg-4 text-center">
            <div class="text-uppercase text-secondary small">Win %</div>
            <div class="fw-bold fs-4 text-primary">{{ $dashboard['seasonRecord']['win_percent'] }}%</div>
        </div>
    </div>
    <div class="col-6 col-md-2 mb-3">
        <div class="rounded rounded-3 bg-white p-3 p-lg-4 text-center">
            <div class="text-uppercase text-secondary small">Goal Diff</div>
            <div class="fw-bold fs-4 text-primary">{{ sprintf("%+d", $dashboard['seasonRecord']['goals'] - $dashboard['seasonRecord']['goals_against']) }}</div>
        </div>
    </div>
    <div class="col-6 col-md-2 mb-3">
        <div class="rounded rounded-3 bg-white p-3 p-lg-4 text-center" data-bs-toggle="tooltip" data-bs-title="Total goals scored: {{ $dashboard['seasonRecord']['goals'] }}">
            <div class="text-uppercase text-secondary small">Goals / Game</div>
            <div class="fw-bold fs-4 text-primary">{{ number_format($dashboard['seasonRecord']['goals'] / $games, 2) }}</div>
        </div>
    </div>
    <div class="col-6 col-md-2 mb-3">
        <div class="rounded rounded-3 bg-white p-3 p-lg-4 text-center" data-bs-toggle="tooltip" data-bs-title="Total goals conceded: {{ $dashboard['seasonRecord']['goals_against'] }}">
            <div class="text-uppercase text-secondary small">Conceded / Game</div>
            <div class="fw-bold fs-4 text-primary">{{ number_format($dashboard['seasonRecord']['goals_against'] / $games, 2) }}</div>
        </div>
    </div>
    <div class="col-6 col-md-2 mb-3">
        <div class="rounded rounded-3 bg-white p-3 p-lg-4 text-center" data-bs-toggle="tooltip" data-bs-title="Clean sheet %: {{ $cleanSheetPct }}%">
            <div class="text-uppercase text-secondary small">Clean Sheets</div>
            <div class="fw-bold fs-4 text-primary">{{ $dashboard['seasonRecord']['clean_sheets'] }}</div>
        </div>
    </div>
</div>

<div class="row">

    {{-- 6. Top Scorers --}}
    @if(count($dashboard['topScorers']))
    <div class="col-12 col-lg-6 mb-3">
        <div class="rounded rounded-3 bg-white p-3 p-lg-4">
            <div class="fw-bold text-secondary mb-3">Top Scorers</div>
            @foreach($dashboard['topScorers'] as $name => $goals)
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>{{ $name }}</div>
                <span class="badge bg-primary rounded-pill">{{ $goals }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- 6b. Top Assisters --}}
    @if(count($dashboard['topAssisters']))
    <div class="col-12 col-lg-6 mb-3">
        <div class="rounded rounded-3 bg-white p-3 p-lg-4">
            <div class="fw-bold text-secondary mb-3">Top Assists</div>
            @foreach($dashboard['topAssisters'] as $name => $assists)
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>{{ $name }}</div>
                <span class="badge bg-primary rounded-pill">{{ $assists }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif


</div>

<div class="row">

    {{-- 7. Home vs Away --}}
    <div class="col-12 col-lg-6 mb-3">
        <div class="rounded rounded-3 bg-white p-3 p-lg-4">
            <div class="fw-bold text-secondary mb-3">Home vs Away</div>
            <table class="table table-sm mb-0">
                <thead>
                    <tr class="text-center">
                        <th></th>
                        <th>Games</th>
                        <th>W-D-L</th>
                        <th>GF</th>
                        <th>GA</th>
                        <th>GD</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="text-center">
                        <td class="fw-bold text-start">Home</td>
                        <td>{{ $dashboard['homeAway']['home']['games'] }}</td>
                        <td>{{ $dashboard['homeAway']['home']['wins'] }}-{{ $dashboard['homeAway']['home']['draws'] }}-{{ $dashboard['homeAway']['home']['losses'] }}</td>
                        <td>{{ $dashboard['homeAway']['home']['goals'] }}</td>
                        <td>{{ $dashboard['homeAway']['home']['goals_against'] }}</td>
                        <td>{{ sprintf("%+d", $dashboard['homeAway']['home']['goals'] - $dashboard['homeAway']['home']['goals_against']) }}</td>
                    </tr>
                    <tr class="text-center">
                        <td class="fw-bold text-start">Away</td>
                        <td>{{ $dashboard['homeAway']['away']['games'] }}</td>
                        <td>{{ $dashboard['homeAway']['away']['wins'] }}-{{ $dashboard['homeAway']['away']['draws'] }}-{{ $dashboard['homeAway']['away']['losses'] }}</td>
                        <td>{{ $dashboard['homeAway']['away']['goals'] }}</td>
                        <td>{{ $dashboard['homeAway']['away']['goals_against'] }}</td>
                        <td>{{ sprintf("%+d", $dashboard['homeAway']['away']['goals'] - $dashboard['homeAway']['away']['goals_against']) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- 12. Formation Effectiveness --}}
    @if(count($dashboard['formations']))
    <div class="col-12 col-lg-6 mb-3">
        <div class="rounded rounded-3 bg-white p-3 p-lg-4">
            <div class="fw-bold text-secondary mb-3">Formation Effectiveness</div>
            <table class="table table-sm mb-0">
                <thead>
                    <tr class="text-center">
                        <th class="text-start">Formation</th>
                        <th>Games</th>
                        <th>W-D-L</th>
                        <th>GF</th>
                        <th>GA</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($dashboard['formations'] as $formName => $formStats)
                    <tr class="text-center">
                        <td class="fw-bold text-start">{{ $formName }}</td>
                        <td>{{ $formStats['games'] }}</td>
                        <td>{{ $formStats['wins'] }}-{{ $formStats['draws'] }}-{{ $formStats['losses'] }}</td>
                        <td>{{ $formStats['goals'] }}</td>
                        <td>{{ $formStats['goals_against'] }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>

{{-- 14. Goal Timing Distribution --}}
@if(collect($dashboard['goalTiming'])->sum('for') + collect($dashboard['goalTiming'])->sum('against') > 0)
<div class="row">
    <div class="col-12 col-lg-6 mb-3">
        <div class="rounded rounded-3 bg-white p-3 p-lg-4">
            <div class="fw-bold text-secondary mb-2">When Goals Are Scored</div>
            <canvas id="goal-timing-chart" style="max-height:250px"></canvas>
            <script>
            document.addEventListener("DOMContentLoaded", function() {
                new Chart(document.getElementById('goal-timing-chart'), {
                    type: 'bar',
                    data: {
                        labels: ['0-15', '15-30', '30-45', '45-60', '60-75', '75-90'],
                        datasets: [{
                            label: 'Scored',
                            data: [{{ collect($dashboard['goalTiming'])->pluck('for')->implode(',') }}],
                            backgroundColor: $winColor,
                        }, {
                            label: 'Conceded',
                            data: [{{ collect($dashboard['goalTiming'])->pluck('against')->implode(',') }}],
                            backgroundColor: $lossColor,
                        }]
                    },
                    options: {
                        animation: false,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom' } },
                        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
                    }
                });
            });
            </script>
        </div>
    </div>


</div>
@endif
