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
                            <img class="logo img-fluid" src="{{ asset($result->homeTeam->club->logo) }}"/>
                            <div @class([
                                    'team-name pt-2 pb-1',
                                    'good-guys' => $result->homeTeam->managed,
                                    'bad-guys' => $result->awayTeam->managed,
                                ])>{{ $result->homeTeam->name }}</div>
                        </div>
                        <div id="home-score" class="ms-4 actions-against">
                            <div class="score display-4 fw-bold">0</div>
                        @if($result->awayTeam->managed)
                            <span class="goal_against material-symbols-outlined" data-event-id="1">sports_soccer</span>
                            <span class="more_against material-symbols-outlined">more_horiz</span>
                        @endif
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

                    <form id="formation-form" class="mb-3">
                        <select class="form-select d-inline-block w-50" id="formation" name="formation">
                            <option></option>
                    @foreach ($groupedFormations as $group => $fg)
                            <optgroup label="{{ $group . 'v' . $group }}">
                        @foreach ($groupedFormations[$group] as $formation)
                            <option value="{{ $formation['id'] }}">{{ $formation['name'] }}</option>
                        @endforeach
                    @endforeach
                        </select>
                        <button type="button" id="submit-formation" class="btn btn-secondary">Save</button>
                    </form>

                    <div id="current-formation"><span class="badge fs-6 text-bg-secondary"></span></div>
                </div>
                <div class="col-4 col-lg-3">
                    <div class="d-flex">
                        <div id="away-score" class="me-4 actions-against">
                            <div class="score display-4 fw-bold">0</div>
                        @if($result->homeTeam->managed)
                            <span class="goal_against material-symbols-outlined" data-event-id="1">sports_soccer</span>
                            <span class="more_against material-symbols-outlined">more_horiz</span>
                        @endif
                        </div>
                        <div class="mx-auto">
                            <img class="logo img-fluid" src="{{ asset($result->awayTeam->club->logo) }}"/>
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

            <div class="row">
                <div class="col-12 col-lg-7">
                    <div id="live-main" class="field mx-auto text-center position-relative" 
                        data-result-id="{{ $result->id }}" data-start-game-route="{{ route('ajax-start-game') }}"
                        data-create-event-route="{{ route('ajax-create-event') }}"
                        data-end-game-route="{{ route('ajax-end-game') }}"
                        @if($result->homeTeam->managed) data-good-guys="home" @else data-good-guys="away" @endif
                        >
                        <img class="position-absolute start-0 top-0" src="{{ asset('img/field.svg') }}" />
                    </div><!--/#live-main-->
                </div>
                <div class="sidebar col-12 col-lg-5">
                    <ul class="nav nav-underline" id="game-detail-links">
                        <li class="nav-item">
                            <a class="nav-link active" id="summary-tab" data-bs-toggle="tab" data-bs-target="#summary-pane" href="#">Summary</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="events-tab" data-bs-toggle="tab" data-bs-target="#events-pane" href="#">Events</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="players-tab" data-bs-toggle="tab" data-bs-target="#players-pane" href="#">Players</a>
                        </li>
                    </ul>
                    <div class="tab-content" id="game-details">
                        <div class="tab-pane pt-4 fade show active small" id="summary-pane" tabindex="0">
                            @include('games.live-summary-pane')
                        </div><!--/#summary-pane-->
                        <div class="tab-pane pt-4 fade" id="events-pane" tabindex="0">
                            <i id="no-events-yet">no events yet</i>
                            <div id="game-timeline" class="event-timeline small" style="display:none"> </div>
                        </div>
                        <div class="tab-pane pt-4 fade" id="players-pane" tabindex="0">
                            <table id="players-table" class="table table-striped table-sm small">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th title="Minutes">M</th>
                                        <th title="Goals">G</th>
                                        <th title="Assists">A</th>
                                        <th title="Shots">S</th>
                                        <th title="Tackles">T</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach ($players as $id => $p)
                                    <tr id="{{ 'player-' . $id }}">
                                        <td>{{ $p['name'] }}</td>
                                        <td class="mins">0</td>
                                        <td class="goals">0</td>
                                        <td class="assists">0</td>
                                        <td class="shots">0</td>
                                        <td class="tackles">0</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                            <script>
                            $('#players-table').DataTable({
                                autoWidth: false,
                                paging: false,
                                searching: false,
                                info: false
                            });
                            </script>
                        </div>
                    </div><!--/#game-details-->
                </div><!--/.sidebar-->
        </div><!--/.rounded-->

    </div><!--/container-->

    @include('games.live-event-modal')

    @include('games.live-additional-modal')

<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
<script>
let formations = {{ Js::from($formations) }};
let players = {{ Js::from($players) }};
let playersByPosition = {{ Js::from($groupedPlayers) }};

let live = new LiveAll(formations, players, playersByPosition);

@if($resultEvents->isNotEmpty())
    let resultEvents = {{ Js::from($resultEvents) }};
    live.addExistingEvents(resultEvents);
@endif
</script>
@endsection
