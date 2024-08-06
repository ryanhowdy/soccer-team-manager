<nav class="navbar navbar-expand-lg sticky-top bg-white">
    <div class="container">
        <a class="navbar-brand me-5" href="{{ route('home') }}">STM</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-links">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbar-links">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item me-2">
                    <a class="nav-link active" href="{{ route('home') }}">Home</a>
                </li>
                <li class="nav-item me-2">
                    <a class="nav-link" href="{{ route('games.index') }}">Games</a>
                </li>
                <li class="nav-item me-2">
                    <a class="nav-link" href="{{ route('teams.index') }}">Teams</a>
                </li>
                <li class="nav-item me-2 dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Stats</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('stats.teams.index') }}">Teams</a></li>
                        <li><a class="dropdown-item" href="{{ route('stats.competitions.index') }}">Competitions</a></li>
                        <li><a class="dropdown-item" href="{{ route('stats.players.index') }}">Players</a></li>
                    </ul>
                </li>
                <li class="nav-item me-2 dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Players</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('rosters.index') }}">Rosters</a></li>
                        <li><a class="dropdown-item" href="{{ route('players.index') }}">Players</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Locations</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
