    <div class="rounded rounded-3 bg-white p-4 mb-3 d-none d-lg-block">
        <div class="mb-4">
            <div class="position-relative d-inline-block me-3" style="width:3rem; height:3rem;">
                <div class="rounded-circle d-flex align-items-center justify-content-center w-100 h-100 bg-info text-white">
                    <i class="bi bi-calendar-day-fill"></i>
                </div>
            </div>
            <h4 class="d-inline-block align-middle">Upcoming Games</h4>
        </div>
    @php($prevMonth = 0)
    @foreach($scheduled as $result)
        @if($result->date->inUserTimezone()->format('m') != $prevMonth)
            <div class="fw-bold fs-4 border-bottom pb-2 mb-3 text-secondary">
                {{ $result->date->inUserTimezone()->format('F') }}
            </div>
        @endif
        <div class="row game-listing-details mb-5">
            <div class="col-4">
                <div class="competition text-uppercase small">
                    <a class="link-dark link-underline-opacity-0 link-underline-opacity-100-hover link-offset-2-hover"
                        href="{{ route('competitions.show', ['competition' => $result->competition->id]) }}">
                        {{ $result->competition->name }}
                    </a>
                </div>
                <div class="date fw-bold">{{ $result->date->inUserTimezone()->format('M. jS, Y') }}</div>
                <div class="time small">{{ $result->date->inUserTimezone()->format('g:i a') }}</div>
            </div>
            <div class="col-5 text-center">
                <div class="home-v-away d-grid align-items-center justify-content-center mb-3 text-decoration-none rounded rounded-2 text-dark">
                    <div class="home-team d-flex align-items-center justify-content-end">
                        <div class="me-4">{{ $result->homeTeam->name }}</div>
                        <img class="logo img-fluid" data-bs-toggle="tooltip" data-bs-title="{{ $result->homeTeam->club->name }}"
                            src="{{ asset($result->homeTeam->club->logo) }}" onerror="this.onerror=null;this.src='{{ asset('img/logo_none.png') }}';"/>
                    </div>
                    <div class="score text-center">VS</div>
                    <div class="away-team d-flex align-items-center">
                        <img class="logo img-fluid" data-bs-toggle="tooltip" data-bs-title="{{ $result->awayTeam->club->name }}"
                            src="{{ asset($result->awayTeam->club->logo) }}" onerror="this.onerror=null;this.src='{{ asset('img/logo_none.png') }}';"/>
                        <div class="ms-4">{{ $result->awayTeam->name }}</div>
                    </div>
                </div>
            </div>
            <div class="col-3 d-flex align-items-center justify-content-end">
                <a class="btn btn-primary text-white btn-sm py-1" href="{{ route('games.preview', ['id' => $result->id]) }}">
                    Game Preview
                    <span class="bi-arrow-right-short"></span>
                </a>
            </div>
        </div>
        @php($prevMonth = $result->date->inUserTimezone()->format('m'))
    @endforeach
    </div>
