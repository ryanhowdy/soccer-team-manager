<div class="row">
    {{-- Team Stats --}}
    <div class="col-12 col-md-6">
        <div id="team-stats" class="rounded rounded-3 bg-white p-4 mb-3">
            <h3 class="mb-3">Team Stats</h3>
            <div class="d-flex justify-content-between pb-1 mb-2 border-bottom">
                <div class="pe-3 text-secondary">{{ $result->homeTeam->name }}</div>
                <div class="ps-3 text-secondary text-end">{{ $result->awayTeam->name }}</div>
            </div>
            <div class="d-flex justify-content-between">
                <div id="game-goals-good-guys">{{ $stats['home']['goals'] }}</div>
                <div>Goals</div>
                <div id="game-goals-bad-guys">{{ $stats['away']['goals'] }}</div>
            </div>
            <div class="progress game-goals-progress bg-primary-dark rounded-0 mb-4"><div style="width:50%" class="progress-bar border-end border-5"></div></div>
            <div class="d-flex justify-content-between">
                <div id="game-xg-good-guys" title="{{ $stats['home']['xgs'] }}">{{ $stats['home']['xg'] }}</div>
                <div>Expected Goals</div>
                <div id="game-xg-bad-guys" title="{{ $stats['away']['xgs'] }}">{{ $stats['away']['xg'] }}</div>
            </div>
            <div class="progress game-xg-progress bg-primary-dark rounded-0 mb-4"><div style="width:50%" class="progress-bar border-end border-5"></div></div>
            <div class="d-flex justify-content-between">
                <div id="game-shots-good-guys">{{ $stats['home']['shots'] }}</div>
                <div>Shots</div>
                <div id="game-shots-bad-guys">{{ $stats['away']['shots'] }}</div>
            </div>
            <div class="progress game-shots-progress bg-primary-dark rounded-0 mb-4"><div style="width:50%" class="progress-bar border-end border-5"></div></div>
            <div class="d-flex justify-content-between">
                <div id="game-shots-on-good-guys">{{ $stats['home']['shots_on'] }}</div>
                <div>(On Target)</div>
                <div id="game-shots-on-bad-guys">{{ $stats['away']['shots_on'] }}</div>
            </div>
            <div class="progress game-shots-on-progress bg-primary-dark rounded-0 mb-4"><div style="width:50%" class="progress-bar border-end border-5"></div></div>
            <div class="d-flex justify-content-between">
                <div id="game-shots-off-good-guys">{{ $stats['home']['shots_off'] }}</div>
                <div>(Off Target)</div>
                <div id="game-shots-off-bad-guys">{{ $stats['away']['shots_off'] }}</div>
            </div>
            <div class="progress game-shots-off-progress bg-primary-dark rounded-0 mb-4"><div style="width:50%" class="progress-bar border-end border-5"></div></div>
            <div class="d-flex justify-content-between">
                <div id="game-corners-good-guys">{{ $stats['home']['corners'] }}</div>
                <div>Corners</div>
                <div id="game-corners-bad-guys">{{ $stats['away']['corners'] }}</div>
            </div>
            <div class="progress game-corners-progress bg-primary-dark rounded-0 mb-4"><div style="width:50%" class="progress-bar border-end border-5"></div></div>
            <div class="d-flex justify-content-between">
                <div id="game-offsides-good-guys">{{ $stats['home']['offsides'] }}</div>
                <div>Offsides</div>
                <div id="game-offsides-bad-guys">{{ $stats['away']['offsides'] }}</div>
            </div>
            <div class="progress game-offsides-progress bg-primary-dark rounded-0 mb-4"><div style="width:50%" class="progress-bar border-end border-5"></div></div>
            <div class="d-flex justify-content-between">
                <div id="game-fouls-good-guys">{{ $stats['home']['fouls'] }}</div>
                <div>Fouls</div>
                <div id="game-fouls-bad-guys">{{ $stats['away']['fouls'] }}</div>
            </div>
            <div class="progress game-fouls-progress bg-primary-dark rounded-0 mb-4"><div style="width:50%" class="progress-bar border-end border-5"></div></div>
            <div class="d-flex justify-content-between">
                <div id="game-fouls-good-guys">{{ $stats['home']['yellow_cards'] }}</div>
                <div>Yellow Cards</div>
                <div id="game-fouls-bad-guys">{{ $stats['away']['yellow_cards'] }}</div>
            </div>
            <div class="progress game-yellow-cards-progress bg-primary-dark rounded-0 mb-4"><div style="width:50%" class="progress-bar border-end border-5"></div></div>
            <div class="d-flex justify-content-between">
                <div id="game-fouls-good-guys">{{ $stats['home']['red_cards'] }}</div>
                <div>Red Cards</div>
                <div id="game-fouls-bad-guys">{{ $stats['away']['red_cards'] }}</div>
            </div>
            <div class="progress game-red-cards-progress bg-primary-dark rounded-0 mb-4"><div style="width:50%" class="progress-bar border-end border-5"></div></div>
        </div>
    </div>

    {{-- Player Stats --}}
    <div class="col-12 col-md-6">
        <div class="rounded rounded-3 bg-white p-4 mb-3">
            <h3 class="mb-3">Player Stats</h3>

            <ul class="nav nav-tabs mb-3">
                <li class="nav-item">
                    <a class="nav-link active" id="stats-tab" data-bs-toggle="tab" data-bs-target="#player-stats-pane" href="#">Stats</a>
                </li>
            @if($havePlayingTimeStats)
                <li class="nav-item">
                    <a class="nav-link" id="playing-time-tab" data-bs-toggle="tab" data-bs-target="#playing-time-pane" href="#">Playing Time</a>
                </li>
            @endif
            </ul>

            <div class="tab-content">

                {{-- Stats --}}
                <div class="tab-pane fade show active" id="player-stats-pane" tabindex="0">
                    <table class="table">
                        <thead>
                            <tr>
                                <th title="Player Name">Name</th>
                                <th title="Goals">Gls</th>
                                <th title="Assists">Ast</th>
                                <th title="Total Shots">Shot</th>
                                <th title="Shots on Target">SOT</th>
                                <th title="Offsides">Off</th>
                                <th title="Tackles">Tkl</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($stats['players'] as $playerId => $s)
                            <tr>
                                <td>@if($s['player']){{ $s['player']->name }}@endif</td>
                                <td>{{ $s['goals'] }}</td>
                                <td>{{ $s['assists'] }}</td>
                                <td>{{ $s['shots'] }}</td>
                                <td>{{ $s['shots_on'] }}</td>
                                <td>{{ $s['offsides'] }}</td>
                                <td>{{ $s['tackles'] }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Playing Time --}}
                <div class="tab-pane fade" id="playing-time-pane" tabindex="0">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($playingTime as $playerId => $time)
                            <tr>
                                <td>
                                @if($time['starter'])
                                    <span class="" data-bs-toggle="tooltip" data-bs-title="Starter">*</span>
                                @endif
                                    {{ $time['player']->name }}
                                </td>
                                <td>{{ $time['minutes'] }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <i>* Indicates a Starter</i>
                </div>

            </div><!--/.tab-content-->
        </div>
    </div>

</div><!--/.row-->

