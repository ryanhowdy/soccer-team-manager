@extends('layouts.main')

@section('body-id', 'game-preview')
@section('page-title', 'Game Preview')
@section('page-desc', 'Game details')

@section('content')
    <div class="container main-content">

        <div class="rounded rounded-3 bg-white p-4 mb-3">

            <a class="float-end btn btn-sm btn-light" href="{{ route('games.edit', ['id' => $result->id]) }}">
                <span class="bi bi-pencil pe-2"></span>Edit
            </a>

            {{-- Competition & Date/time --}}
            <div class="text-center mb-5">
                <div class="competition text-uppercase">{{ $result->competition->name }}</div>
                <div class="date fw-bold fs-4">{{ $result->date->inUserTimezone()->format('M. jS, Y') }}</div>
                <div class="time">{{ $result->date->inUserTimezone()->format('g:i a') }}</div>
            </div>

            {{-- Game Details --}}
            <div class="row scheduled-game mb-5 mx-3">
                <div class="col-4">
                    <div class="home-team d-flex align-items-center">
                        <img class="logo img-fluid" data-bs-toggle="tooltip" data-bs-title="{{ $result->{$goodGuys . 'Team'}->club->name }}"
                            src="{{ asset($result->{$goodGuys . 'Team'}->club->logo) }}"/>
                        <div class="ms-4">
                            <div class="fs-4">{{ $result->{$goodGuys . 'Team'}->name }}</div>
                        @if($last5Results->count())
                            <div class="last-5-form" data-bs-toggle="tooltip" data-bs-title="Most Recent Form">
                            @foreach($last5Results as $r)
                                <span @class([
                                    'text-white',
                                    'bg-success'   => ($r->win_draw_loss == 'W'),
                                    'bg-secondary' => ($r->win_draw_loss == 'D'),
                                    'bg-danger' => ($r->win_draw_loss == 'L'),
                                ])>{{ $r->win_draw_loss }}</span>
                            @endforeach
                            </div>
                        @endif
                        </div>
                    </div>
                </div>
                <div class="col-1 text-center">
                    <div class="mb-1">Wins</div>
                    <span class="bg-light px-4 py-2">{{ $counts['W'] }}</span>
                </div>
                <div class="col-2 text-center">
                    <div class="mb-1">Draws</div>
                    <span class="bg-light px-4 py-2">{{ $counts['D'] }}</span>
                </div>
                <div class="col-1 text-center">
                    <div class="mb-1">Loses</div>
                    <span class="bg-light px-4 py-2">{{ $counts['L'] }}</span>
                </div>
                <div class="col-4">
                    <div class="away-team d-flex align-items-center justify-content-end">
                        <div class="me-4">
                            <div class="fs-4 mb-3">{{ $result->{$badGuys . 'Team'}->name }}</div>
                        </div>
                        <img class="logo img-fluid" data-bs-toggle="tooltip" data-bs-title="{{ $result->{$badGuys . 'Team'}->club->name }}"
                            src="{{ asset($result->{$badGuys . 'Team'}->club->logo) }}"/>
                    </div>
                </div>
            </div>

        </div>

        <div class="row">

            {{-- Head 2 Head Results --}}
            <div class="col-12 col-md-6">
                <div class="game-listing rounded rounded-3 bg-white p-4 mb-3">
                    <h3>Previous Games</h3>
                @foreach($head2HeadResults as $r)
                    <div class="home-v-away position-relative d-grid align-items-center justify-content-center mb-3 border pt-5 p-3 rounded rounded-2">
                        <div class="position-absolute top-0 start-0 small p-2">
                            {{ $r->date->inUserTimezone()->format('M j, Y') }}
                            <i class="bi bi-clock"></i>
                        </div>
                        <div class="position-absolute top-0 end-0 small p-2">
                            <i class="bi bi-tag"></i>
                            {{ $r->competition->name }}
                        </div>
                        <div class="home-team d-flex align-items-center justify-content-end">
                            <div class="me-2">{{ $r->homeTeam->name }}</div>
                            <img class="logo img-fluid" data-bs-toggle="tooltip" data-bs-title="{{ $r->homeTeam->club->name }}" 
                                src="{{ asset($r->homeTeam->club->logo) }}"/>
                        </div>
                        <div class="score text-center">
                            <a href="{{ route('games.show', ['id' => $r->id]) }}">
                                <span @class([
                                    'badge',
                                    'rounded-pill',
                                    'text-white',
                                    'bg-success'   => ($r->win_draw_loss == 'W'),
                                    'bg-secondary' => ($r->win_draw_loss == 'D'),
                                    'bg-danger' => ($r->win_draw_loss == 'L'),
                                ])>{{ $r->home_team_score }} - {{ $r->away_team_score }}</span>
                            </a>
                        </div>
                        <div class="away-team d-flex align-items-center">
                            <img class="logo img-fluid" data-bs-toggle="tooltip" data-bs-title="{{ $r->awayTeam->club->name }}"
                                src="{{ asset($r->awayTeam->club->logo) }}"/>
                            <div class="ms-2">{{ $r->awayTeam->name }}</div>
                        </div>
                    </div>
                @endforeach
                </div>
            </div>

            {{-- Head 2 Head Results --}}
            <div class="col-12 col-md-6">
                <div id="previous-stats" class="rounded rounded-3 bg-white p-4 mb-3">
                    <h3>Previous Stats</h3>
                    <div class="d-flex justify-content-between pb-1 mb-2 border-bottom">
                        <div class="pe-3 text-secondary">{{ $result->{$goodGuys . 'Team'}->name }}</div>
                        <div class="ps-3 text-secondary text-end">{{ $result->{$badGuys . 'Team'}->name }}</div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <div id="game-goals-good-guys">{{ $stats['good']['goals'] }}</div>
                        <div>Goals</div>
                        <div id="game-goals-bad-guys">{{ $stats['bad']['goals'] }}</div>
                    </div>
                    <div class="progress game-goals-progress rounded-0 mb-4"><div style="width:50%" class="progress-bar"></div></div>
                    <div class="d-flex justify-content-between">
                        <div id="game-shots-good-guys">{{ $stats['good']['shots'] }}</div>
                        <div>Shots</div>
                        <div id="game-shots-bad-guys">{{ $stats['bad']['shots'] }}</div>
                    </div>
                    <div class="progress game-shots-progress rounded-0 mb-4"><div style="width:50%" class="progress-bar"></div></div>
                    <div class="d-flex justify-content-between">
                        <div id="game-shots-on-good-guys">{{ $stats['good']['shots_on'] }}</div>
                        <div>(On Target)</div>
                        <div id="game-shots-on-bad-guys">{{ $stats['bad']['shots_on'] }}</div>
                    </div>
                    <div class="progress game-shots-on-progress rounded-0 mb-4"><div style="width:50%" class="progress-bar"></div></div>
                    <div class="d-flex justify-content-between">
                        <div id="game-shots-off-good-guys">{{ $stats['good']['shots_off'] }}</div>
                        <div>(Off Target)</div>
                        <div id="game-shots-off-bad-guys">{{ $stats['bad']['shots_off'] }}</div>
                    </div>
                    <div class="progress game-shots-off-progress rounded-0 mb-4"><div style="width:50%" class="progress-bar"></div></div>
                    <div class="d-flex justify-content-between">
                        <div id="game-corners-good-guys">{{ $stats['good']['corners'] }}</div>
                        <div>Corners</div>
                        <div id="game-corners-bad-guys">{{ $stats['bad']['corners'] }}</div>
                    </div>
                    <div class="progress game-corners-progress rounded-0 mb-4"><div style="width:50%" class="progress-bar"></div></div>
                    <div class="d-flex justify-content-between">
                        <div id="game-fouls-good-guys">{{ $stats['good']['fouls'] }}</div>
                        <div>Fouls</div>
                        <div id="game-fouls-bad-guys">{{ $stats['bad']['fouls'] }}</div>
                    </div>
                    <div class="progress game-fouls-progress rounded-0 mb-4"><div style="width:50%" class="progress-bar"></div></div>
                </div>
            </div>

        </div><!--/.row-->

    </div><!--/container-->
<script>
$('#previous-stats > .progress').each((index, progress) => {
    let $parent = $(progress).prev();

    let goodCount  = parseInt($parent.find('div').first().text());
    let badCount   = parseInt($parent.find('div').eq(2).text());
    let totalCount = goodCount + badCount;
    let percentage = (goodCount / totalCount) * 100;

    $(progress).find('.progress-bar').css('width', percentage + '%');
});
</script>
@endsection
