<nav class="navbar navbar-expand-lg sticky-top border-5 border-top border-dark bg-white {{ config('app.env') }}">
    <div class="container">
        <a class="navbar-brand me-5" href="{{ route('home') }}">STM</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-links">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbar-links">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item me-2">
                    <a class="nav-link" href="{{ route('games.index') }}">Games</a>
                </li>
                <li class="nav-item me-2">
                    <a class="nav-link" href="{{ route('competitions.index') }}">Competitions</a>
                </li>
                <li class="nav-item me-2">
                    <a class="nav-link" href="{{ route('teams.index') }}">Teams</a>
                </li>
                <li class="nav-item me-2 dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Stats</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('stats.teams.index') }}">Teams</a></li>
                        <li><a class="dropdown-item" href="{{ route('stats.locations.index') }}">Locations</a></li>
                        <li><a class="dropdown-item" href="{{ route('stats.lineups.index') }}">Lineups</a></li>
                        {{--<li><a class="dropdown-item" href="{{ route('stats.players.index') }}">Players</a></li>--}}
                    </ul>
                </li>
                <li class="nav-item me-2 dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Players</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('rosters.index') }}">Rosters</a></li>
                        <li><a class="dropdown-item" href="{{ route('players.index') }}">Players</a></li>
                @if($navPlayers)
                        <li><hr class="dropdown-divider"></li>
                    @foreach($navPlayers as $p)
                        <li><a class="dropdown-item" href="{{ route('players.show', ['player' => $p->player->id]) }}">{{ $p->player->name }}</a></li>
                    @endforeach
                @endif
                        <li><a class="dropdown-item link-secondary" href="{{ route('managed-players.create') }}">Add Player</a></li>
                    </ul>
                </li>
                <li class="nav-item me-2 dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">More</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('locations.index') }}">Locations</a></li>
                        <li><a class="dropdown-item" href="{{ route('formations.index') }}">Formations</a></li>
                    </ul>
                </li>
            @role('admin')
                <li class="nav-item me-2">
                    <a class="nav-link text-secondary" href="{{ route('admin.index') }}">Admin</a>
                </li>
            @endrole
            </ul>
            <div class="dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"><i class="bi bi-person-circle fs-5 me-1"></i></a>
                <div class="dropdown-menu dropdown-menu-end">
                    <div class="p-3 text-center">
                        <i class="bi bi-person-circle display-1 mb-1"></i>
                        <b>{{ Auth()->user()->email }}</b>
                    </div>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{{ route('settings') }}">Settings</a>
                    <a class="dropdown-item link-danger" href="{{ route('logout') }}">Logout</a>
                </div>
            </div>
        </div>
    </div>
</nav>
