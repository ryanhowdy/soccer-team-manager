@extends('layouts.main')

@section('body-id', 'live')

@section('content')
    <div class="container main-content">

        <div class="rounded rounded-3 bg-white p-4 mb-1">
            <div id="game-controls" class="initial row text-center mb-3">
                <div class="d-none d-lg-block col-lg-1"></div>
                <div class="col-4 col-lg-3">
                    <div class="d-flex">
                        <div class="mx-auto">
                            <img class="logo img-fluid" src="{{ asset($result->homeTeam->club->logo) }}" onerror="this.onerror=null;this.src='{{ asset('img/logo_none.png') }}';"/>
                            <div @class([
                                    'team-name pt-2 pb-1',
                                    'good-guys' => $result->homeTeam->managed,
                                    'bad-guys' => $result->awayTeam->managed,
                                ])>{{ $result->homeTeam->name }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <a id="start-game" class="btn btn-success btn-lg mt-2 mb-3 text-white">Start Game</a>

                    <div id="timer" class="mb-3"><span class="badge fs-2 text-bg-dark">00:00</span></div>

                    <a id="end-half" class="btn btn-info btn mt-2 mb-3 text-white">End Half</a>

                    <form id="second-half-form" class="mb-3">
                        <input type="number" class="form-control d-inline-block w-50" id="time" name="time" placeholder="45">
                        <button type="button" id="start-second-half" class="btn btn-secondary">Start 2nd Half</button>
                    </form>

                    <a id="end-game" class="btn btn-danger btn mt-2 mb-3 text-white">End Game</a>
                </div>
                <div class="col-4 col-lg-3">
                    <div class="d-flex">
                        <div class="mx-auto">
                            <img class="logo img-fluid" src="{{ asset($result->awayTeam->club->logo) }}" onerror="this.onerror=null;this.src='{{ asset('img/logo_none.png') }}';"/>
                            <div @class([
                                    'team-name pt-2 pb-1',
                                    'good-guys' => $result->awayTeam->managed,
                                    'bad-guys' => $result->homeTeam->managed,
                                ])>{{ $result->awayTeam->name }}</div>
                        </div>
                    </div>
                </div>
                <div class="d-none d-lg-block col-lg-1"></div>
            </div><!--/#game-controls-->

            <div id="live-main" class="row"
                data-result-id="{{ $result->id }}"
                data-fulltime-event-id="{{ \App\Enums\Event::fulltime }}"
                data-create-event-route="{{ route('ajax.results.events.store', ['result' => $result->id]) }}"
                data-possession-route="{{ route('ajax.results.events.possession', ['result' => $result->id]) }}"
                data-result-update-route="{{ route('ajax.results.update', ['result' => $result->id]) }}"
                data-show-route="{{ route('games.show', ['id' => $result->id]) }}"
                >
                <div class="col-12 col-lg-7 mb-4 text-center">
                    <div class="btn-group" role="group">
                        <input type="radio" class="btn-check" name="possession" id="home-possession" value="home" autocomplete="off"
                            data-event-id="@if($result->homeTeam->managed){{ \App\Enums\Event::gain_possession }}@else{{ \App\Enums\Event::lose_possession }}@endif"
                            >
                        <label class="btn btn-outline-primary p-5 w-50" for="home-possession">
                            <div class="text-nowrap">{{ $result->homeTeam->name }}</div>Has the Ball
                        </label>
                        <input type="radio" class="btn-check" name="possession" id="away-possession" value="away" autocomplete="off"
                            data-event-id="@if($result->awayTeam->managed){{ \App\Enums\Event::gain_possession }}@else{{ \App\Enums\Event::lose_possession }}@endif"
                            >
                        <label class="btn btn-outline-primary-dark p-5 w-50" for="away-possession">
                            <div class="text-nowrap">{{ $result->awayTeam->name }}</div>Has the Ball
                        </label>
                    </div>
                </div>
                <div class="sidebar col-12 col-lg-5">
                    <div class="d-flex justify-content-between pb-1 mb-2 border-bottom small">
                        <div class="pe-3 text-secondary">{{ $result->homeTeam->name }}</div>
                        <div class="ps-3 text-secondary text-end">{{ $result->awayTeam->name }}</div>
                    </div>
                    <div id="possession-bar" class="progress bg-primary-dark rounded-0 mb-4"><div style="width: 50%;" class="progress-bar border-end border-5"></div></div>
                </div>
        </div><!--/.rounded-->

    </div><!--/container-->
<script>
let live = new LivePossession();
</script>
@endsection
