@extends('layouts.main')

@section('body-id', 'me')

@section('content')
    <div class="container main-content">
        <div class="rounded rounded-3 bg-white p-3 mb-2">
            <div class="row">
                <div class="col-2">
                    <div id="list-nav" class="list-group">
                        <a class="list-group-item list-group-item-action" href="{{ route('settings') }}">Settings</a>
                        <a class="list-group-item list-group-item-action active" href="{{ route('managed-players.index') }}">Managed Players</a>
                    </div>
                </div>
                <div class="col-10">
                    <h4 class="border-bottom border-light mb-4 pt-4 pb-2 d-flex flex-wrap justify-content-between">
                        <div class="pe-3">Managed Players</div>
                        <a href="{{ route('managed-players.create') }}" class="btn btn-sm btn-primary text-white">
                            <i class="bi-plus-lg pe-2"></i>Add
                        </a>
                    </h4>

                @if($players)
                    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-4 mb-4">
                    @foreach($players as $p)
                        <div class="col">
                            <div class="card text-center">
                                <div class="card-body">
                                    <div><img src="/{{ $p->player->photo }}" class="img-fluid rounded-circle" style="width:50px"/></div>
                                    <h6 class="card-title mt-3">{{ $p->player->name }}</h6>
                                    <div class="card-subtitle mb-2 small text-body-secondary">{{ $p->player->birth_year }}</div>
                                </div>
                                <div class="card-body border-top small">
                                    <a href="{{ route('players.show', ['player' => $p->player_id]) }}" class="card-link">Stats</a>
                                    <a href="{{ route('players.edit', ['player' => $p->player_id]) }}" class="card-link text-decoration-none">
                                        <span class="bi bi-pencil"></span>
                                    </a>
                                    <a href="{{ route('managed-players.destroy', ['id' => $p->id]) }}" class="card-link">
                                        <span class="bi bi-x-lg"></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    </div>
                @else
                    <div class="alert alert-info mb-4">
                        <p><b>No Managed Players</b></b>
                        <p>Adding a player as a Managed Player allows you to keep track of statistics for that player.</p>
                    </div>
                @endif

                </div>
            </div>
        </div>
    </div>
@endsection

