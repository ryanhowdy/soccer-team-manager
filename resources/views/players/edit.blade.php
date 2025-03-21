@extends('layouts.main')

@section('body-id', 'games')
@section('page-title', 'Games')
@section('page-desc', "See the scores of past games")

@section('content')
    <div class="container main-content">

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

        <div class="rounded rounded-3 bg-white p-4 mb-3">
            <div class="row">
                <div class="col-12 col-lg-6">
                    <form method="post" style="max-width:500px" enctype="multipart/form-data" action="{{ route('players.update', ['player' => $player->id]) }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="name">Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ $player->name }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="name">Nickname</label>
                            <input type="text" class="form-control" id="nickname" name="nickname" value="{{ $player->nickname }}">
                        </div>
                        <label class="form-label" for="photo">Photo</label>
                        <div class="d-flex mb-3">
                            <div class="me-3">
                                <img src="/{{ $player->photo }}" class="img-fluid rounded-circle" style="width:50px"/>
                            </div>
                            <div>
                                <input type="file" class="form-control" id="photo" name="photo">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="birth_year">Birth Year</label>
                            <input type="number" class="form-control" id="birth_year" name="birth_year" value="{{ $player->birth_year }}">
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" value="1" @checked($isManaged) name="managed" id="managed">
                                <label class="form-check-label small" for="managed">Managed</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary text-white">Submit</button>
                        <a href="{{ route('players.index') }}" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
                <div class="col-12 col-lg-6">
                    <h4>Teams</h4>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Season</th>
                                <th>Team</th>
                                <th>Number</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($teams as $t)
                            <tr>
                                <td>{{ $t->year }} {{ $t->season }}</td>
                                <td>{{ $t->name }}</td>
                                <td>{{ $t->number }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>{{-- /.rounded --}}

    </div>{{-- /.container --}}
@endsection
