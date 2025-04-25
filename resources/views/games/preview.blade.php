@extends('layouts.main')

@section('body-id', 'game-preview')
@section('page-title', 'Game Preview')
@section('page-desc', 'Game details')

@section('content')
    <div class="container main-content">

        <div class="rounded rounded-3 bg-white p-4 mb-3 position-relative">

            <div class="dropdown position-absolute top-0 end-0 me-2">
                <button class="btn btn-light dropdown-toggle mt-2 mb-3" data-bs-toggle="dropdown">Options</button>
                <ul class="dropdown-menu">
                    <li>
                        <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#add-guest-player">
                            <span class="bi bi-person-add pe-2"></span>Add Guest Player
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('games.edit', ['id' => $result->id]) }}">
                            <span class="bi bi-pencil pe-2"></span>Edit
                        </a>
                    </li>
                </ul>
            </div>


            {{-- Competition & Date/time --}}
            <div class="text-center mb-5">
                <div class="competition text-uppercase">
                    <a class="link-dark link-underline-opacity-0 link-underline-opacity-100-hover link-offset-2-hover"
                        href="{{ route('competitions.show', ['competition' => $result->competition->id]) }}">
                        {{ $result->competition->name }}
                    </a>
                </div>
                <div class="date fw-bold fs-4">{{ $result->date->inUserTimezone()->format('M. jS, Y') }}</div>
                <div class="time">{{ $result->date->inUserTimezone()->format('g:i a') }}</div>
            </div>

            {{-- Game Details --}}
            <div class="row scheduled-game mb-2 mx-3">
                <div class="col-12 col-lg-4 mb-3">
                    <div class="home-team d-flex align-items-center">
                        <img class="logo img-fluid" data-bs-toggle="tooltip" data-bs-title="{{ $result->{$goodGuys . 'Team'}->club->name }}"
                            src="{{ asset($result->{$goodGuys . 'Team'}->club->logo) }}" onerror="this.onerror=null;this.src='{{ asset('img/logo_none.png') }}';"/>
                        <div class="ms-4">
                            <div class="fs-4">{{ $result->{$goodGuys . 'Team'}->name }}</div>
                        @if($last5Results->count())
                            <div class="last-5-form">
                            @foreach($last5Results as $r)
                                <span @class([
                                    'text-white',
                                    'bg-success'   => ($r->win_draw_loss == 'W'),
                                    'bg-secondary' => ($r->win_draw_loss == 'D'),
                                    'bg-danger' => ($r->win_draw_loss == 'L'),
                                    ]) data-bs-toggle="tooltip" 
                                    data-bs-title="{{ $r->date->inUserTimezone()->format('M. jS, Y') }} [{{ $r->home_team_score }} - {{ $r->away_team_score }}]">{{ $r->win_draw_loss }}</span>
                            @endforeach
                            </div>
                        @endif
                        </div>
                    </div>
                </div>
                <div class="col-4 col-lg-1 mb-3 text-center">
                    <div class="mb-1">Wins</div>
                    <span class="bg-light px-4 py-2">{{ $counts['W'] }}</span>
                </div>
                <div class="col-4 col-lg-2 mb-3 text-center">
                    <div class="mb-1">Draws</div>
                    <span class="bg-light px-4 py-2">{{ $counts['D'] }}</span>
                </div>
                <div class="col-4 col-lg-1 mb-3 text-center">
                    <div class="mb-1">Loses</div>
                    <span class="bg-light px-4 py-2">{{ $counts['L'] }}</span>
                </div>
                <div class="col-12 col-lg-4 mb-3">
                    <div class="away-team d-flex align-items-center justify-content-end">
                        <div class="me-4">
                            <div class="fs-4 mb-3">{{ $result->{$badGuys . 'Team'}->name }}</div>
                        </div>
                        <img class="logo img-fluid" data-bs-toggle="tooltip" data-bs-title="{{ $result->{$badGuys . 'Team'}->club->name }}"
                            src="{{ asset($result->{$badGuys . 'Team'}->club->logo) }}" onerror="this.onerror=null;this.src='{{ asset('img/logo_none.png') }}';"/>
                    </div>
                </div>
            </div>

        </div>

        <div class="rounded rounded-3 bg-white p-4 mb-3 text-center">
            <a class="link-secondary link-underline-opacity-0 link-underline-opacity-100-hover link-offset-2-hover" href="#collapsePlayers" data-bs-toggle="collapse">
                Roster<i class="bi bi-caret-down ms-2"></i>
            </a>
            <div id="collapsePlayers" class="row mt-3 collapse small">
            @foreach($currentPlayers as $p)
                <div class="col-4">
                    <div class="d-flex align-items-center p-2">
                        <div><img src="/{{ $p->photo }}" class="img-fluid rounded-circle" style="width:30px"/></div>
                        <div class="ps-2">{{ $p->name }}</div>
                    </div>
                </div>
            @endforeach
            @foreach($guestPlayers as $p)
                <div class="col-4">
                    <div class="d-flex align-items-center p-2">
                        <div><img src="/{{ $p->photo }}" class="img-fluid rounded-circle" style="width:30px"/></div>
                        <div class="ps-2">{{ $p->name }}<span class="text-danger ps-1 fs-6">*</span></div>
                    </div>
                </div>
            @endforeach
            <p class="mt-3 text-secondary fst-italic"><span class="pe-1 fs-6">*</span>Guest Player</p>
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
                                src="{{ asset($r->homeTeam->club->logo) }}" onerror="this.onerror=null;this.src='{{ asset('img/logo_none.png') }}';"/>
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
                                src="{{ asset($r->awayTeam->club->logo) }}" onerror="this.onerror=null;this.src='{{ asset('img/logo_none.png') }}';"/>
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

    <div id="add-guest-player" class="modal fade" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content py-4 px-2">
                <div class="modal-header">
                    <h5 class="modal-title">Add Guest Player</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form class="" action="{{ route('rosters.guest.store') }}" method="post">
                        @csrf
                        <input type="hidden" name="club_team_season_id" value="{{ $result->club_team_season_id }}">
                        <input type="hidden" name="result_id" value="{{ $result->id }}">
                        <div class="mb-3">
                            <select class="form-select" name="player_id">
                                <option>Add Player</option>
                            @foreach($availablePlayers as $p)
                                <option value="{{ $p['id'] }}">{{ $p['name'] }}</option>
                            @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Add</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

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
