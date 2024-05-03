@extends('layouts.main')

@section('body-id', 'game')
@section('page-title', 'Game')
@section('page-desc', 'Game details')

@section('content')
    <div class="container main-content">

        <div class="rounded rounded-3 bg-white p-4 mb-3">

            {{-- Competition & Date/time --}}
            <div class="text-center mb-2">
                <div class="competition text-uppercase">{{ $result->competition->name }}</div>
                <div class="date fw-bold fs-4">{{ $result->date->inUserTimezone()->format('M. jS, Y') }}</div>
                <div class="time">{{ $result->date->inUserTimezone()->format('g:i a') }}</div>
            </div>

            {{-- Game Details --}}
            <div class="d-flex justify-content-center align-items-center">
                <div class="fs-4">{{ $result->homeTeam->name }}</div>
                <div class="mx-5">
                    <img class="logo img-fluid" data-bs-toggle="tooltip" data-bs-title="{{ $result->homeTeam->club->name }}"
                        src="{{ asset($result->homeTeam->club->logo) }}"/>
                </div>
                <div @class([
                    'd-flex justify-content-center border border-3 py-1 px-3',
                    'border-success text-success' => ($result->win_draw_loss == 'W'),
                    'border-secondary-subtle text-secondary' => ($result->win_draw_loss == 'D'),
                    'border-danger text-danger' => ($result->win_draw_loss == 'L'),
                ])>
                    <div class="fs-1 me-3">{{ $result->home_team_score }}</div>
                    <div class="fs-1 ">-</div>
                    <div class="fs-1 ms-3">{{ $result->away_team_score }}</div>
                </div>
                <div class="mx-5">
                    <img class="logo img-fluid" data-bs-toggle="tooltip" data-bs-title="{{ $result->awayTeam->club->name }}"
                        src="{{ asset($result->awayTeam->club->logo) }}"/>
                </div>
                <div class="fs-4">{{ $result->awayTeam->name }}</div>
            </div>

        </div>

        <div class="row">

            {{-- Player Stats --}}
            <div class="col-12 col-md-6">
                <div class="rounded rounded-3 bg-white p-4 mb-3">
                    <h3 class="mb-3">Player Stats</h3>
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
                </div>
            </div>

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
                    <div class="progress game-goals-progress rounded-0 mb-4"><div style="width:50%" class="progress-bar"></div></div>
                    <div class="d-flex justify-content-between">
                        <div id="game-shots-good-guys">{{ $stats['home']['shots'] }}</div>
                        <div>Shots</div>
                        <div id="game-shots-bad-guys">{{ $stats['away']['shots'] }}</div>
                    </div>
                    <div class="progress game-shots-progress rounded-0 mb-4"><div style="width:50%" class="progress-bar"></div></div>
                    <div class="d-flex justify-content-between">
                        <div id="game-shots-on-good-guys">{{ $stats['home']['shots_on'] }}</div>
                        <div>(On Target)</div>
                        <div id="game-shots-on-bad-guys">{{ $stats['away']['shots_on'] }}</div>
                    </div>
                    <div class="progress game-shots-on-progress rounded-0 mb-4"><div style="width:50%" class="progress-bar"></div></div>
                    <div class="d-flex justify-content-between">
                        <div id="game-shots-off-good-guys">{{ $stats['home']['shots_off'] }}</div>
                        <div>(Off Target)</div>
                        <div id="game-shots-off-bad-guys">{{ $stats['away']['shots_off'] }}</div>
                    </div>
                    <div class="progress game-shots-off-progress rounded-0 mb-4"><div style="width:50%" class="progress-bar"></div></div>
                    <div class="d-flex justify-content-between">
                        <div id="game-corners-good-guys">{{ $stats['home']['corners'] }}</div>
                        <div>Corners</div>
                        <div id="game-corners-bad-guys">{{ $stats['away']['corners'] }}</div>
                    </div>
                    <div class="progress game-corners-progress rounded-0 mb-4"><div style="width:50%" class="progress-bar"></div></div>
                    <div class="d-flex justify-content-between">
                        <div id="game-fouls-good-guys">{{ $stats['home']['fouls'] }}</div>
                        <div>Fouls</div>
                        <div id="game-fouls-bad-guys">{{ $stats['away']['fouls'] }}</div>
                    </div>
                    <div class="progress game-fouls-progress rounded-0 mb-4"><div style="width:50%" class="progress-bar"></div></div>
                </div>
            </div>

        </div><!--/.row-->

    </div><!--/container-->
<script>
$('#team-stats > .progress').each((index, progress) => {
    let $parent = $(progress).prev();

    let goodCount  = parseInt($parent.find('div').first().text());
    let badCount   = parseInt($parent.find('div').eq(2).text());
    let totalCount = goodCount + badCount;
    let percentage = (goodCount / totalCount) * 100;

    $(progress).find('.progress-bar').css('width', percentage + '%');
});
</script>
@endsection
