@extends('layouts.main')

@section('body-id', 'pk-live')

@section('content')
    <div class="container main-content">

        <div id="data" class="d-none" data-result-id="{{ $result->id }}" 
            data-start-route="{{ route('ajax.results.pk.start', $result->id) }}"
            data-event-route="{{ route('ajax.results.pk.store', $result->id) }}"></div>

        <div class="rounded rounded-3 bg-white position-relative mb-3 p-2 p-lg-5">
            <div class="d-flex mb-3">
                <div class="teams flex-fill pe-3">
                    <div class="small">&nbsp;</div>
                    <div class="c position-relative pe-3">
                        <img class="d-none d-lg-inline-block logo img-fluid" src="{{ asset($result->homeTeam->club->logo) }}" 
                            onerror="this.onerror=null;this.src='{{ asset('img/logo_none.png') }}';"/>
                        <span class="text-muted pe-1">{{ $result->homeTeam->club->name }}</span>{{ $result->homeTeam->name }}
                        <span class="score font-monospace fw-bold position-absolute end-0 {{ $result->homeTeam->managed ? 'us' : 'them' }}">0</span>
                    </div>
                    <div class="c position-relative pe-3">
                        <img class="d-none d-lg-inline-block logo img-fluid" src="{{ asset($result->awayTeam->club->logo) }}" 
                            onerror="this.onerror=null;this.src='{{ asset('img/logo_none.png') }}';"/>
                        <span class="text-muted pe-1">{{ $result->awayTeam->club->name }}</span>{{ $result->awayTeam->name }}
                        <span class="score font-monospace fw-bold position-absolute end-0 {{ $result->awayTeam->managed ? 'us' : 'them' }}">0</span>
                    </div>
                </div><!--/.teams-->
                <div class="pks h-100 flex-fill">
    @if(!is_null($existingData))
        @if($existingData->penalties->count())
            @for($r = 1; $r <= 5; $r++)
                    <div class="d-inline-block text-center me-1 round-{{ $r }}">
                        <div class="small">{{ $r }}</div>
                @foreach([1,2] as $i)
                        <div @class([
                            'c',
                            'us' => ($i == 1 && $result->homeTeam->managed) || ($i == 2 && $result->awayTeam->managed),
                            'them' => ($i == 1 && $result->awayTeam->managed) || ($i == 2 && $result->homeTeam->managed),
                        ])>
                    @if(isset($existingData->penalties[$key]))
                        @if($existingData->penalties[$key]->event_id == \App\Enums\Event::penalty_goal->value)
                            <span class="fs-3 text-success bi-check-circle-fill"></span>
                        @else
                            <span class="fs-3 text-danger bi-x-circle-fill"></span>
                        @endif
                    @else
                            <span class="fs-3 bi-circle text-light"></span>
                    @endif
                    @php($key++)
                        </div>
                @endforeach
                    </div>
            @endfor
        @endif
    @else
        @for($r = 1; $r <= 5; $r++)
                    <div class="d-inline-block text-center me-1 round-{{ $r }}">
                        <div class="small">{{ $r }}</div>
                        <div class="c {{ $result->homeTeam->managed ? 'us' : 'them' }}"><span class="fs-3 bi-circle text-light"></span></div>
                        <div class="c {{ $result->awayTeam->managed ? 'us' : 'them' }}"><span class="fs-3 bi-circle text-light"></span></div>
                    </div>
        @endfor
    @endif
                    <div id="template" class="d-none">
                        <div class="d-inline-block text-center me-1">
                            <div class="small"></div>
                            <div class="c {{ $result->homeTeam->managed ? 'us' : 'them' }}"><span class="fs-3 bi-circle text-light"></span></div>
                            <div class="c {{ $result->awayTeam->managed ? 'us' : 'them' }}"><span class="fs-3 bi-circle text-light"></span></div>
                        </div>
                    </div>
                </div><!--/.pks-->
            </div><!--/.d-flex-->

            <div id="who-first" class="text-center mb-3">
                <div class="fs-5 mb-2">Who is shooting first?</div>
                <div class="mb-3">
                    <input type="radio" class="btn-check" data-who="{{ $result->homeTeam->managed ? 'us' : 'them' }}" 
                        name="first" id="home" data-who-id="{{ $result->homeTeam->id }}" value="{{ $result->homeTeam->id }}" autocomplete="off">
                    <label class="btn btn-outline-primary" for="home">Home</label>
                    <input type="radio" class="btn-check" data-who="{{ $result->awayTeam->managed ? 'us' : 'them' }}"
                        name="first" id="away" data-who-id="{{ $result->awayTeam->id }}" value="{{ $result->awayTeam->id }}" autocomplete="off">
                    <label class="btn btn-outline-primary" for="away">Away</label>
                </div>
                <div class="d-none">
                    <button id="start" type="button" class="btn btn-primary text-white">
                        Begin Shootout<i class="bi bi-arrow-right-circle-fill ms-2"></i>
                    </button>
                </div>
            </div>

            <div id="controls" class="d-none">
                <div class="fs-6 mb-2">Round: <span id="round"></span></div>
                <div class="us text-center mb-3 d-none">
                    <div id="player" class="mb-3">
                        <label for="player_id" class="form-label">Shooter</label>
                        <select id="player_id" name="player_id" class="form-select mx-auto" style="max-width:300px">
                            <option></option>
                        @foreach ($players as $id => $p)
                            <option value="{{ $id }}">{{ $p['name'] }}</option>
                        @endforeach
                        </select>
                    </div>
                    <button type="button" data-event="pk_goal" data-event-id="19" class="btn btn-success text-white">
                        <span class="material-symbols-outlined align-top pe-1">sports_soccer</span>Goal
                    </button>
                    <button type="button" data-event="pk_on_target" data-event-id="20" class="btn btn-danger text-white">
                        <span class="material-symbols-outlined align-top pe-1">target</span>Saved
                    </button>
                    <button type="button" data-event="pk_off_target" data-event-id="20" class="btn btn-danger text-white">
                        <span class="material-symbols-outlined align-top pe-1">block</span>Missed
                    </button>
                </div>
                <div class="them text-center mb-3 d-none">
                    <button type="button" data-event="pk_goal" data-event-id="19" class="btn btn-danger text-white">
                        <span class="material-symbols-outlined align-top pe-1">sports_soccer</span>Goal
                    </button>
                    <button type="button" data-event="save" data-event-id="10" class="btn btn-success text-white">
                        <span class="material-symbols-outlined align-top pe-1">pan_tool</span>Saved
                    </button>
                    <button type="button" data-event="pk_off_target" data-event-id="20" class="btn btn-success text-white">
                        <span class="material-symbols-outlined align-top pe-1">block</span>Missed
                    </button>
                </div>
            </div>

            <div id="end-game" class="d-none text-center mt-5 mb-3">
                <a href="{{ route('games.show', $result->id) }}" class="btn btn-primary text-white">End Game</a>
            </div>

        </div><!--/.rounded-->
    </div><!--/container-->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
<script>
let pk = new Pk();
@if(!is_null($existingData))
    // update the curRound
    let totalPreviousShots = {{ $existingData->penalties->count() }};
    let penalties          = {{ Js::from($existingData->penalties) }};
    let goalId             = {{ \App\Enums\Event::penalty_goal->value }};

    pk.shootoutId = {{ $existingData->id }};

    // set who shoots first
    $('input[data-who-id="{{ $existingData->first_team_id }}"]').prop('checked', true);
    $('#who-first').addClass('d-none');
    pk.curTeam = $('input[name="first"]:checked').attr('data-who');

    // update curTeam
    //   even = first team, stay the same
    //   odd = other team, flipt it
    if (totalPreviousShots % 2 !== 0) {
        pk.curTeam = pk.curTeam == 'us' ? 'them' : 'us';
    }

    // update curRound
    pk.curRound = Math.floor(totalPreviousShots / 2) + 1;

    // loop through all pks
    for (let i = 0; i < penalties.length; i++) {
        // update score
        if (penalties[i].event_id == goalId) {
            let usThem = 'us'
            if (penalties[i].against) {
                usThem = 'them'
            }
            let score = parseInt($('.score.' + usThem).text());
            score++;
            $('.score.' + usThem).text(score);
        }

        // remove users that have already shot
        if (penalties[i].player_id !== null) {
            $('#player_id').find('option[value="' + penalties[i].player_id + '"]').prop('disabled', true);
        }
    }

    // show the pk controls
    $('#controls').removeClass('d-none');
    $('#controls > div.' + pk.curTeam).removeClass('d-none');

    // set the current shot
    $('.round-' + pk.curRound + ' > .' + pk.curTeam + ' > span').removeClass('text-light');

    // set the current round
    $('#round').text(pk.curRound);
@endif
</script>
@endsection
