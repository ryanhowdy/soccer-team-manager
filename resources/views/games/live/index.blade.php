@extends('layouts.main')

@section('body-id', 'live')

@section('content')
    <div class="container main-content">
        <div class="rounded rounded-3 bg-white p-4 mb-1">
            <div class="d-flex flex-column p-4 align-items-center justify-content-center">
                <div class="list-group list-group-radio d-grid gap-2 border-0 mb-3">
                    <div class="position-relative">
                        <i class="bi bi-grid-3x3-gap-fill fs-2 position-absolute top-50 start-0 ms-3"></i>
                        <input class="form-check-input position-absolute top-50 end-0 me-3 fs-5" type="radio" name="live-type" id="type-all" value="all" checked>
                        <label class="list-group-item py-3 pe-5" for="type-all">
                            <strong class="fw-semibold">All</strong>
                            <span class="d-block small opacity-75">Capture all live events, shots, goals, subs, tackles, etc.</span>
                        </label>
                    </div>
                    <div class="position-relative">
                        <i class="bi bi-border-middle fs-2 position-absolute top-50 start-0 ms-3"></i>
                        <input class="form-check-input position-absolute top-50 end-0 me-3 fs-5" type="radio" name="live-type" id="type-possession" value="possession">
                        <label class="list-group-item py-3 pe-5" for="type-possession">
                            <strong class="fw-semibold">Possession</strong>
                            <span class="d-block small opacity-75">Capture only team possession.</span>
                        </label>
                    </div>
                </div>
                <button id="type-submit" type="button" class="btn btn-primary text-white">Continue</button>
            </div>
        </div>
    </div><!--/container-->

<script>
$(document).ready(function() {
    $('#type-submit').click(function() {
        let type = $('input[name="live-type"]:checked').val();

        if (type == 'all') {
            document.location = '{{ route('games.live.all', ['id' => $id]) }}';
        }
        if (type == 'possession') {
            document.location = '{{ route('games.live.possession', ['id' => $id]) }}';
        }
    });
});
</script>
@endsection
