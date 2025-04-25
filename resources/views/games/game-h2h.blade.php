<div class="rounded rounded-3 bg-white p-4 mb-3">
    <h3 class="mb-3">Head-to-head</h3>

    <div class="row">
        <div class="col-12 col-md-6">
            <div class="rounded rounded-3 bg-white p-4 mb-1">
                <h3>Results</h3>
            @isset($chartData['wdl'])
                <canvas id="wdl-chart" class="p-3 mb-2"></canvas>
                <script>
                let wdlChart = document.getElementById('wdl-chart');
                new Chart(wdlChart, {
                    type: 'doughnut',
                    data: {
                        labels: ['Win', 'Draw', 'Loss'],
                        datasets: [{
                            data: [{{ $chartData['wdl']['w'] }}, {{ $chartData['wdl']['d'] }}, {{ $chartData['wdl']['l'] }}],
                            backgroundColor: [$winColor, $drawColor, $lossColor],
                        }]
                    },
                    options: {
                        plugins: {
                            legend: { display: false }
                        }
                    }
                });
                </script>
                <div class="d-flex text-center justify-content-center">
                    <div>
                        <span class="d-inline-block border-top border-5 border-success p-2 pb-0 mx-2">Win</span>
                        <div class="text-secondary">{{ $chartData['wdl']['w'] }}</div>
                    </div>
                    <div>
                        <span class="d-inline-block border-top border-5 border-primary-dark p-2 pb-0 mx-2">Draw</span>
                        <div class="text-secondary">{{ $chartData['wdl']['d'] }}</div>
                    </div>
                    <div>
                        <span class="d-inline-block border-top border-5 border-danger p-2 pb-0 mx-2">Loss</span>
                        <div class="text-secondary">{{ $chartData['wdl']['l'] }}</div>
                    </div>
                </div>
            @endisset
            </div><!--/.rounded-->
        </div><!--/.col-->

        {{-- head 2 head results --}}
        <div class="col-12 col-md-6">
            <div class="game-listing-small rounded rounded-3 bg-white p-4 mb-3">
            @foreach($results as $result)
                <a href="{{ route('games.show', ['id' => $result->id]) }}" 
                    class="home-v-away d-grid align-items-center justify-content-center mb-3 text-decoration-none rounded rounded-2 text-dark">
                    <div class="home-team d-flex align-items-center justify-content-end">
                        <div class="me-2">{{ $result->homeTeam->name }}</div>
                        <img class="logo img-fluid" data-bs-toggle="tooltip" data-bs-title="{{ $result->homeTeam->club->name }}" 
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
                        <img class="logo img-fluid" data-bs-toggle="tooltip" data-bs-title="{{ $result->awayTeam->club->name }}"
                            src="{{ asset($result->awayTeam->club->logo) }}" onerror="this.onerror=null;this.src='{{ asset('img/logo_none.png') }}';"/>
                        <div class="ms-2">{{ $result->awayTeam->name }}</div>
                    </div>
                </a>
            @endforeach
            </div>
        </div>
    </div>

</div>
