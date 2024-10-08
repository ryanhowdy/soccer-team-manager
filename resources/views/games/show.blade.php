@extends('layouts.main')

@section('body-id', 'game')

@section('content')
    <div class="container main-content">

        <div class="rounded rounded-3 bg-white p-4 mb-3 position-relative">

            {{-- Location --}}
            <div class="position-absolute top-0 start-0 small p-4">
                <a class="link-dark link-underline-opacity-0 link-underline-opacity-100-hover link-offset-2-hover"
                    href="https://www.google.com/maps/place/{{ urlencode($result->location->address) }}">
                    {{ $result->location->name }}
                    <i class="bi bi-geo-alt"></i>
                </a>
            </div>

            {{-- Competition --}}
            <div class="position-absolute top-0 end-0 small p-4">
                <a class="link-dark link-underline-opacity-0 link-underline-opacity-100-hover link-offset-2-hover" href="{{ route('competitions.show', $result->competition->id) }}">
                    <i class="bi bi-tag"></i>
                    {{ $result->competition->name }}
                </a>
            </div>

            {{-- Date/time --}}
            <div class="text-center mb-2">
                <div class="date fw-bold fs-4">{{ $result->date->inUserTimezone()->format('M. jS, Y') }}</div>
                <div class="time">{{ $result->date->inUserTimezone()->format('g:i a') }}</div>
            </div>

            {{-- Game Score --}}
            <div class="row">
                <div class="col-4 col-lg-5">
                    <div class="d-flex justify-content-end align-items-center">
                        <div class="fs-4 d-none d-lg-block">{{ $result->homeTeam->name }}</div>
                        <div class="mx-3">
                            <img class="logo img-fluid" data-bs-toggle="tooltip" data-bs-title="{{ $result->homeTeam->club->name }}"
                                src="{{ asset($result->homeTeam->club->logo) }}"/>
                        </div>
                    </div>
                </div>
                <div class="col-4 col-lg-2">
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
                </div>
                <div class="col-4 col-lg-5">
                    <div class="d-flex justify-content-star align-items-center">
                        <div class="mx-3">
                            <img class="logo img-fluid" data-bs-toggle="tooltip" data-bs-title="{{ $result->awayTeam->club->name }}"
                                src="{{ asset($result->awayTeam->club->logo) }}"/>
                        </div>
                        <div class="fs-4 d-none d-lg-block">{{ $result->awayTeam->name }}</div>
                    </div>
                </div>
            </div>{{-- /.row --}}

            {{-- Game Summary --}}
            <div class="border-top pt-5 mt-5">
                <div class="fs-4">Summary</div>
                <div class="rounded p-3 text-bg-light">
                    <div class="row g-4">
                        <div class="col-12 col-lg-6">
                            <div class="pb-2 small text-muted">Player Summary</div>
                        @foreach($managedPlayerIds as $id => $name)
                            <div class="pb-2">
                                {{ $name }} 
                            @isset($starters[$id])
                                started at {{ $starters[$id] }}
                            @else
                                was a substitute
                            @endisset
                            @if($havePlayingTimeStats)
                                and played {{ $playingTime[$id]['minutes'] }} minutes.
                            @endisset
                            </div>
                            @isset($stats['players'][$id])
                                <div>{{ $stats['players'][$id]['goals'] }} goals</div>
                                <div>{{ $stats['players'][$id]['assists'] }} assists</div>
                                <div>{{ $stats['players'][$id]['shots'] }} shots</div>
                                <div>{{ $stats['players'][$id]['shots_on'] }} shots on target</div>
                                <div>{{ $stats['players'][$id]['offsides'] }} offsides</div>
                                <div>{{ $stats['players'][$id]['tackles'] }} tackles</div>
                            @endisset
                        @endforeach
                        </div>
                        {{-- Game Notes --}}
                        <div class="col-12 col-lg-6">
                            <div class="pb-2 small text-muted">Game Notes</div>
                        @if($result->notes)
                            {{ $result->notes }}
                        @else
                            <a id="add-notes-link" href="#notes-form" class="link-primary link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover" data-bs-toggle="collapse">Add Notes</a>
                            <form id="notes-form" class="collapse mt-2">
                                <div class="mb-3">
                                    <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                                </div>
                                <button type="submit" class="btn btn-sm btn-primary text-white">Submit</button>
                            </form>
                        @endif
                        </div>
                    </div>{{-- /.row --}}
                </div>{{-- /.rounded --}}
            </div>

            @if(!$result->live)
                <div class="alert alert-danger mt-3" role="alert">
                    These result were not recorded live.
                </div>
            @endif

        @if($result->live)
            <div class="nav nav-underline mt-5">
                <li class="nav-item">
                    <a class="nav-link active" href="#" id="stats-tab" data-bs-toggle="tab" data-bs-target="#stats-pane">Stats</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" id="timeline-tab" data-bs-toggle="tab" data-bs-target="#timeline-pane">Timeline</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" id="lineup-tab" data-bs-toggle="tab" data-bs-target="#lineup-pane">Lineup</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" id="h2h-tab" data-bs-toggle="tab" data-bs-target="#h2h-pane">Head-to-head</a>
                </li>
            </div>
        @endif
        </div>

    @if($result->live)
        <div class="tab-content">

            <div class="tab-pane fade show active" id="stats-pane">
        @include('games.game-stats')
            </div><!--/#stats-pane-->

            <div class="tab-pane fade" id="timeline-pane">
                <div class="rounded rounded-3 bg-white p-4 mb-3">
                    <h3 class="mb-3">Timeline</h3>
                    <div id="game-timeline" class="event-timeline small"></div>
                </div>
            </div><!--/#timeline-pane-->

            <div class="tab-pane fade" id="lineup-pane">
                <div class="rounded rounded-3 bg-white p-4 mb-3">
                    <h3 class="mb-3">Lineup</h3>
                    <div id="field" class="field field-view mx-auto text-center position-relative">
                        <img class="position-absolute start-0 top-0" src="{{ asset('img/field.svg') }}" />
                    </div><!--/#field-->
                </div>
            </div><!--/#lineup-pane-->

            <div class="tab-pane fade" id="h2h-pane">
        @include('games.game-h2h')
            </div><!--/#h2h-pane-->

        </div><!--/.tab-content-->
    @endif

    </div><!--/container-->

<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
<script>
$(document).ready(function() {
    $('#team-stats > .progress').each((index, progress) => {
        let $parent = $(progress).prev();

        let goodCount  = parseFloat($parent.find('div').first().text());
        let badCount   = parseFloat($parent.find('div').eq(2).text());
        let totalCount = goodCount + badCount;

        if (totalCount > 0) {
            let percentage = (goodCount / totalCount) * 100;

            $(progress).find('.progress-bar').css('width', percentage + '%');
        }
    });

    $('#player-stats-pane > .table').DataTable({
        autoWidth: false,
        paging: false,
        searching: false,
        info: false,
        order: [[1, 'desc']]
    });
    $('#playing-time-pane > .table').DataTable({
        autoWidth: false,
        paging: false,
        searching: false,
        info: false,
        order: [[1, 'desc']]
    });

    $('#notes-form').on('submit', function(e) {
        e.preventDefault();
        let notes = $('#notes').val();
        $.ajax({
            url: '{{ route('ajax.results.update', ['result' => $result->id]) }}',
            type : 'POST',
            data : {
                id: {{ $result->id }},
                notes: notes
            },
        }).done(function(ret) {
            $('#add-notes-link').after(notes).remove();
            $('#notes-form').remove();
        });
    });

@if($resultEvents->isNotEmpty())
    let resultEvents = {{ Js::from($resultEvents) }};
    let players = {{ Js::from($players) }};
    let formation = {{ Js::from($result->formation) }};
    let starters  = {{ Js::from($starters) }};

    let goodGuys = '{{ $goodGuys }}';
    let badGuys  = '{{ $badGuys }}';

    let drawer   = new FormationDrawer(players, {});
    drawer.drawFormation(formation);
    drawer.addPlayerStarters(starters);

    let timeline = new EventTimeline('#game-timeline');

    for (let [i, data] of Object.entries(resultEvents))
    {
        let side = data.against ? badGuys : goodGuys;

        timeline.addEvent(data, side);
    }
@endif
});

</script>
@endsection
