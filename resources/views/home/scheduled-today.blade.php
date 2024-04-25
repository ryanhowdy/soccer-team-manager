    <div class="rounded rounded-3 bg-white p-4 mb-3">
        <div class="mb-4">
            <div class="position-relative d-inline-block me-3" style="width:3rem; height:3rem;">
                <div class="rounded-circle d-flex align-items-center justify-content-center w-100 h-100 bg-primary text-white">
                    <i class="bi bi-calendar-day-fill"></i>
                </div>
            </div>
            <h4 class="d-inline-block align-middle">Today</h4>
        </div>

        <div class="row">
    @foreach ($scheduled as $sched)
            <div class="col-6">
                <div class="rounded rounded-3 border p-3 mb-4">
                    <div class="small mb-3">
                        <a class="link-secondary link-offset-2 link-underline-opacity-0 link-underline-opacity-100-hover" 
                            target="_blank" href="{{ $sched->competition->website }}">{{ $sched->competition->name }}</a>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="">
                            <div class="home mb-2">
                                <img class="me-2" style="width:30px" src="{{ asset($sched->homeTeam->club->logo) }}"/>
                                {{ $sched->homeTeam->name }}
                            </div>
                            <div class="away">
                                <img class="me-2" style="width:30px" src="{{ asset($sched->awayTeam->club->logo) }}"/>
                                {{ $sched->awayTeam->name }}
                            </div>
                        </div>
                        <div class="small">
                            <div>{{ $sched->date->inUserTimezone()->format('g:i a') }}</div>
                            <a href="{{ route('games.live', $sched->id) }}" class="btn btn-success btn-sm text-white">Start Game</a>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between border-top pt-3 mb-1 small">
                        <div>
                            <a class="align-middle link-secondary link-offset-2 link-underline-opacity-0 link-underline-opacity-100-hover"
                                href="https://www.google.com/maps/place/{{ urlencode($sched->location->address) }}">
                                {{ $sched->location->name }}
                                <i class="bi bi-geo-alt"></i>
                            </a>
                        </div>
                    @php
                        $thisTeamId = $sched->homeTeam->managed ? $sched->away_team_id : $sched->home_team_id;
                    @endphp
                    @if (isset($lastResultsByTeam[$thisTeamId]))
                        <div class="last-5-form pull-right">
                        @foreach($lastResultsByTeam[$thisTeamId] as $r)
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
            </div><!--/.col-->
    @endforeach
        </div><!--/.row-->

    </div><!--/.rounded-->
