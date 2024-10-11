
        <div class="row">

            <div class="col-12 col-md-6 col-lg-4 mb-3">
                <div class="rounded rounded-3 bg-white p-4 mb-1">
                    <h3>Results</h3>
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
                </div><!--/.rounded-->
            </div><!--/.col-->

            <div class="col-12 col-md-6 col-lg-4 mb-3">
                <div class="rounded rounded-3 bg-white p-4 mb-1">
                    <h3>Goals</h3>
                    <canvas id="player-goals-chart" class="p-3 mb-2"></canvas>
                    <script>
                    let playerGoalsChart = document.getElementById('player-goals-chart');
                    new Chart(playerGoalsChart, {
                        type: 'doughnut',
                        data: {
                            labels: [{!! $chartData['goals']['labels'] !!}],
                            datasets: [{
                                data: [{!! $chartData['goals']['data'] !!}],
                                backgroundColor: $chartColors,
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
                    @foreach($chartData['goals']['players'] as $player => $goals)
                        <div>
                            <span class="d-inline-block border-top border-5 border-chart{{ $loop->index+1 }} p-2 pb-0 mx-2">{{ $player }}</span>
                            <div class="text-secondary">{{ $goals }}</div>
                        </div>
                        @break($loop->index >= 2)
                    @endforeach
                    </div>
                </div><!--/.rounded-->
            </div><!--/.col-->

            <div class="col-12 d-md-none d-lg-block col-lg-4 mb-3">
                <div class="rounded rounded-3 bg-white p-4 mb-1">
                    <h3>Assists</h3>
                    <canvas id="player-assists-chart" class="p-3 mb-2"></canvas>
                    <script>
                    let playerAssistsChart = document.getElementById('player-assists-chart');
                    new Chart(playerAssistsChart, {
                        type: 'doughnut',
                        data: {
                            labels: [{!! $chartData['assists']['labels'] !!}],
                            datasets: [{
                                data: [{!! $chartData['assists']['data'] !!}],
                                backgroundColor: $chartColors,
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
                    @foreach($chartData['assists']['players'] as $player => $assists)
                        <div>
                            <span class="d-inline-block border-top border-5 border-chart{{ $loop->index+1 }} p-2 pb-0 mx-2">{{ $player }}</span>
                            <div class="text-secondary">{{ $assists }}</div>
                        </div>
                        @break($loop->index >= 2)
                    @endforeach
                    </div>
                </div><!--/.rounded-->
            </div><!--/.col-->

        </div><!--/.row-->

        <div class="row">

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

            <div class="col-12 col-md-6">
                <div class="row">
                    <div class="col-6">
                        <div class="rounded rounded-3 bg-white p-4 mb-3">
                            <h5>Goals Per Game</h5>
                            <h1>{{ $chartData['gpg']['gpg'] }}</h1>
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="rounded rounded-3 bg-white p-4 mb-3">
                            <h5>Goals Allowed Per Game</h5>
                            <h1>{{ $chartData['gapg']['gapg'] }}</h1>
                        </div>
                    </div>
                </div>

            @isset($chartData['homeaway'])
                <div class="row">
                    <div class="col">
                        <div class="rounded rounded-3 bg-white p-4 mb-3">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th class="text-center">
                                            <span data-bs-toggle="tooltip" data-bs-title="Win-Draw-Loss">Results</span>
                                        </th>
                                        <th class="text-center"><span data-bs-toggle="tooltip" data-bs-title="Goals Per Game">GPG</span></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="fw-bold">Overall</td>
                                        <td class="text-center">
                                            {{ $chartData['homeaway']['overall']['wins'] }}-{{ $chartData['homeaway']['overall']['draws'] }}-{{ $chartData['homeaway']['overall']['losses'] }}
                                        </td>
                                        <td class="text-center">
                                            <span @class([
                                                "badge bg-success",
                                                "opacity-100" => $chartData['homeaway']['overall']['gpg'] >= 2.5,
                                                "opacity-75" => $chartData['homeaway']['overall']['gpg'] >= 1.5,
                                                "opacity-50" => $chartData['homeaway']['overall']['gpg'] >= .5,
                                                "opacity-25",
                                                ])>{{ number_format($chartData['homeaway']['overall']['gpg'], 2) }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Home</td>
                                        <td class="text-center">
                                            {{ $chartData['homeaway']['home']['wins'] }}-{{ $chartData['homeaway']['home']['draws'] }}-{{ $chartData['homeaway']['home']['losses'] }}
                                        </td>
                                        <td class="text-center">
                                            <span @class([
                                                "badge bg-success",
                                                "opacity-100" => $chartData['homeaway']['home']['gpg'] >= 2.5,
                                                "opacity-75" => $chartData['homeaway']['home']['gpg'] >= 1.5,
                                                "opacity-50" => $chartData['homeaway']['home']['gpg'] >= .5,
                                                "opacity-25",
                                                ])>{{ number_format($chartData['homeaway']['home']['gpg'], 2) }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Away</td>
                                        <td class="text-center">
                                            {{ $chartData['homeaway']['away']['wins'] }}-{{ $chartData['homeaway']['away']['draws'] }}-{{ $chartData['homeaway']['away']['losses'] }}
                                        </td>
                                        <td class="text-center">
                                            <span @class([
                                                "badge bg-success",
                                                "opacity-100" => $chartData['homeaway']['away']['gpg'] >= 2.5,
                                                "opacity-75" => $chartData['homeaway']['away']['gpg'] >= 1.5,
                                                "opacity-50" => $chartData['homeaway']['away']['gpg'] >= .5,
                                                "opacity-25",
                                                ])>{{ number_format($chartData['homeaway']['away']['gpg'], 2) }}</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="fw-bold text-muted">Stats</th>
                                        <th class="text-center table-success">Overall</th>
                                        <th class="text-center">Home</th>
                                        <th class="text-center">Away</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="fw-bold">Win %</td>
                                        <td class="text-center table-success">{{ $chartData['homeaway']['overall']['win_percent'] }}&#37;</td>
                                        <td class="text-center">{{ $chartData['homeaway']['home']['win_percent'] }}&#37;</td>
                                        <td class="text-center">{{ $chartData['homeaway']['away']['win_percent'] }}&#37;</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Goals</td>
                                        <td class="text-center table-success">{{ $chartData['homeaway']['overall']['goals'] }}</td>
                                        <td class="text-center">{{ $chartData['homeaway']['home']['goals'] }}</td>
                                        <td class="text-center">{{ $chartData['homeaway']['away']['goals'] }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Goals Against</td>
                                        <td class="text-center table-success">{{ $chartData['homeaway']['overall']['goals_against'] }}</td>
                                        <td class="text-center">{{ $chartData['homeaway']['home']['goals_against'] }}</td>
                                        <td class="text-center">{{ $chartData['homeaway']['away']['goals_against'] }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">xG</td>
                                        <td class="text-center table-success"></td>
                                        <td class="text-center"></td>
                                        <td class="text-center"></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">xG Against</td>
                                        <td class="table-success"></td>
                                        <td class="text-center"></td>
                                        <td class="text-center"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endisset
            </div>

        </div><!--/.row-->
