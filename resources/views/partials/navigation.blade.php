<nav class="navbar navbar-expand-lg sticky-top bg-light">
    <div class="container">
        <a class="navbar-brand me-4" href="{{ route('home') }}">
            <span class="bi bi-gear-wide-connected"></span> STM
        </a>

        {{-- Primary context: the selected team --}}
        <div class="dropdown me-auto">
            <button class="btn bg-white rounded-5 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                {{ auth()->user()->selectedTeam->name ?? 'Select Team' }}
            </button>
            <ul class="dropdown-menu">
            @foreach($navManagedTeams as $team)
                <li>
                    <form action="{{ route('pickTeam', ['teamId' => $team->id]) }}" method="post">
                        @csrf
                        <button type="submit" class="dropdown-item">{{ $team->club->name }}: {{ $team->name }}</button>
                    </form>
                </li>
            @endforeach
            </ul>
        </div>

        {{-- Account (+ interim home for the deferred favorite-player lens) --}}
        <div class="dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"><i class="bi bi-person-circle fs-5"></i></a>
            <div class="dropdown-menu dropdown-menu-end">
                <div class="p-3 text-center">
                    <i class="bi bi-person-circle display-1 mb-1"></i>
                    <b>{{ auth()->user()->email }}</b>
                </div>
                <div class="dropdown-divider"></div>
            @if($navPlayers->isNotEmpty())
                <h6 class="dropdown-header">My Players</h6>
                @foreach($navPlayers as $p)
                <a class="dropdown-item" href="{{ route('players.show', ['player' => $p->player->id]) }}">{{ $p->player->name }}</a>
                @endforeach
            @endif
                <a class="dropdown-item link-secondary" href="{{ route('managed-players.create') }}">Add Player</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="{{ route('settings') }}">Settings</a>
                <a class="dropdown-item link-danger" href="{{ route('logout') }}">Logout</a>
            </div>
        </div>
    </div>
</nav>

<nav class="navbar navbar-expand-lg sticky-top-2 border-bottom border-secondary-subtle py-0 bg-light">
    <div class="container">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-links">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbar-links">

            {{-- The selected team's sections --}}
            <ul class="navbar-nav nav-underline me-auto mb-2 mb-lg-0">
                <li class="nav-item me-2">
                    <a @class(['nav-link', 'active' => request()->routeIs('home')]) href="{{ route('home') }}">Overview</a>
                </li>
                <li class="nav-item me-2">
                    <a @class(['nav-link', 'active' => request()->routeIs('games.*')]) href="{{ route('games.index') }}">Games</a>
                </li>
                <li class="nav-item me-2">
                    <a @class(['nav-link', 'active' => request()->routeIs('rosters.*', 'players.*')]) href="{{ route('rosters.index') }}">Roster</a>
                </li>
                <li class="nav-item me-2 dropdown">
                    <a @class(['nav-link dropdown-toggle', 'active' => request()->routeIs('stats.*')]) href="#" role="button" data-bs-toggle="dropdown">Stats</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('stats.teams.index') }}">Team</a></li>
                        <li><a class="dropdown-item" href="{{ route('stats.lineups.index') }}">Lineups</a></li>
                        <li><a class="dropdown-item" href="{{ route('stats.locations.index') }}">Locations</a></li>
                    </ul>
                </li>
            </ul>

            {{-- Non-team-scoped management + admin --}}
            <ul class="navbar-nav nav-underline mb-2 mb-lg-0">
                <li class="nav-item me-2 dropdown">
                    <a @class(['nav-link dropdown-toggle', 'active' => request()->routeIs('teams.*', 'clubs.*', 'competitions.*', 'locations.*', 'formations.*')]) href="#" role="button" data-bs-toggle="dropdown">Manage</a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('teams.index') }}">Teams &amp; Clubs</a></li>
                        <li><a class="dropdown-item" href="{{ route('competitions.index') }}">Competitions</a></li>
                        <li><a class="dropdown-item" href="{{ route('locations.index') }}">Locations</a></li>
                        <li><a class="dropdown-item" href="{{ route('formations.index') }}">Formations</a></li>
                    </ul>
                </li>
            @role('admin')
                <li class="nav-item me-2">
                    <a @class(['nav-link', 'active' => request()->routeIs('admin.*')]) href="{{ route('admin.index') }}">Admin</a>
                </li>
            @endrole
            </ul>

        </div>
    </div>
</nav>
