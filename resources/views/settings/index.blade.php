@extends('layouts.main')

@section('body-id', 'me')

@section('content')
    <div class="container main-content">
        <div class="rounded rounded-3 bg-white p-3 mb-2">
            <div class="row">
                <div class="col-2">
                    <div id="list-nav" class="list-group">
                        <a class="list-group-item list-group-item-action active" href="{{ route('settings') }}">Settings</a>
                        <a class="list-group-item list-group-item-action" href="{{ route('managed-players.index') }}">Managed Players</a>
                    </div>
                </div>
                <div class="col-10">
                    <h4 class="border-bottom border-light mb-4 pt-4 pb-2 d-flex flex-wrap justify-content-between">
                        <div class="pe-3">Live Stat Tracking</div>
                    </h4>
                    <div class="d-flex flex-column p-4 align-items-center justify-content-center">
                        <div class="list-group list-group-radio d-grid gap-2 border-0 mb-3">
                            <div class="position-relative">
                                <i class="bi bi-question-lg fs-2 position-absolute top-50 start-0 ms-3"></i>
                                <input class="form-check-input position-absolute top-50 end-0 me-3 fs-5" type="radio" name="live-type" id="type-none" value="none" checked>
                                <label class="list-group-item py-3 pe-5" for="type-none">
                                    <strong class="fw-semibold">Let me choose each Game</strong>
                                    <span class="d-block small opacity-75">Will give you the choice of stat tracking for each game.</span>
                                </label>
                            </div>
                        </div>
                        <p>-- or --</p>
                        <div class="list-group list-group-radio d-grid gap-2 border-0 mb-3">
                            <div class="position-relative">
                                <i class="bi bi-grid-3x3-gap-fill fs-2 position-absolute top-50 start-0 ms-3"></i>
                                <input class="form-check-input position-absolute top-50 end-0 me-3 fs-5" type="radio" name="live-type" id="type-all" value="all">
                                <label class="list-group-item py-3 pe-5" for="type-all">
                                    <strong class="fw-semibold">All *</strong>
                                    <span class="d-block small opacity-75">Capture all live events, shots, goals, subs, tackles, etc.</span>
                                </label>
                            </div>
                            <div class="position-relative">
                                <i class="bi bi-border-middle fs-2 position-absolute top-50 start-0 ms-3"></i>
                                <input class="form-check-input position-absolute top-50 end-0 me-3 fs-5" type="radio" name="live-type" id="type-possession" value="possession">
                                <label class="list-group-item py-3 pe-5" for="type-possession">
                                    <strong class="fw-semibold">Possession *</strong>
                                    <span class="d-block small opacity-75">Capture only team possession.</span>
                                </label>
                            </div>
                        </div>
                        <p class="small opacity-75">* Choosing this option will change the stat tracking for all games.</p>
                        <button id="type-submit" type="button" class="btn btn-primary text-white">Continue</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

