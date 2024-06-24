@extends('layouts.main')

@section('body-id', 'games')
@section('page-title', 'Games')
@section('page-desc', "See the scores of past games")

@section('content')
    <div class="container main-content">

        <div class="rounded rounded-3 bg-white position-relative p-4 mb-3">

            <ul id="managed-teams" class="nav nav-underline">
            @foreach($managedTeams as $team)
                <li class="nav-item">
                    <a @class([
                        'nav-link',
                        'active' => $loop->first
                        ]) id="{{ Str::of($team->name)->slug('-') }}-tab" data-bs-toggle="tab" 
                        data-bs-target="#{{ Str::of($team->name)->slug('-') }}-pane" href="#">{{ $team->name }}</a>
                </li>
            @endforeach
            </ul>

            <div class="btn-group btn-group-sm my-3" role="group">
                <input type="radio" checked class="btn-check" id="active" name="status" autocomplete="off">
                <label class="btn btn-outline-light" for="active">Active</label>
                <input type="radio" class="btn-check" id="inactive" name="status" autocomplete="off">
                <label class="btn btn-outline-light" for="inactive">Inactive</label>
            </div>

            <div id="players-content" class="tab-content">
            @foreach($managedTeams as $team)
                <div @class([
                    'tab-pane fade',
                    'show active' => $loop->first,
                    ]) id="{{ Str::of($team->name)->slug('-') }}-pane" tabindex="0">
                    <table class="table table-bordered">
                        <thead>
                            <th>Name</th>
                            <th>Positions</th>
                            <th></th>
                        </thead>
                        <tbody>
                    @isset($activePlayers[$team->id])
                        @foreach($activePlayers[$team->id] as $p)
                            <tr class="active">
                                <td>
                                    <a class="d-block text-decoration-none" href="{{ route('players.show', ['id' => $p->player->id]) }}">
                                        <img src="/{{ $p->player->photo }}" class="img-fluid rounded-circle" style="width:50px"/>
                                        {{ $p->player->name }}
                                    </a>
                                </td>
                                <td>
                                @foreach($p->player->positions as $pos)
                                    <span class="pe-2 fw-bold">{{ $pos->position->position }}</span>
                                @endforeach
                                    <select class="position form-select w-auto" data-id="{{ $p->player->id }}">
                                        <option></option>
                                @foreach($positions as $pos)
                                        <option value="{{ $pos->id }}">{{ $pos->position }}</span>
                                @endforeach
                                    </select>
                                </td>
                                <td>
                                    <a href="#{{ $p->player->id }}"><span class="bi-pencil"></span></a>
                                </td>
                            </tr>
                        @endforeach
                    @endisset
                    @isset($inactivePlayers[$team->id])
                        @foreach($inactivePlayers[$team->id] as $p)
                            <tr class="inactive">
                                <td>
                                    <a class="d-block text-decoration-none" href="{{ route('players.show', ['id' => $p->id]) }}">
                                        <img src="/{{ $p->photo }}" class="img-fluid rounded-circle" style="width:50px"/>
                                        {{ $p->name }}
                                    </a>
                                </td>
                                <td>n/a</td>
                                <td>
                                    <a href="#{{ $p->id }}"><span class="bi-pencil"></span></a>
                                </td>
                            </tr>
                        @endforeach
                    @endisset
                        </tbody>
                    </table>
                </div>
            @endforeach
            </div>
        </div>

    </div><!--/container-->

<script>
showHideStatuses();

$('.table').DataTable({
    autoWidth: false,
    paging: false,
    searching: false,
    info: false,
    order: [[0, 'asc']]
});

$('select.position').on('change', function(e) {
    let $input     = $(this);
    let playerId   = $input.data('id');
    let positionId = $input.val();
    let position   = $input.find('option:selected').text();

    $.ajax({
        url: '{{ route('ajax-create-player-position') }}',
        type : 'POST',
        data : {
            player_id   : playerId,
            position_id : positionId
        },
    }).done(function(ret) {
        let $td = $input.parent('td');

        $td.prepend('<span class="pe-2 fw-bold">' + position + '</span>');

        $input.find('option:selected').prop('selected', false);
    });
});

$('.btn-group > input').on('click', function() {
    showHideStatuses();
});

function showHideStatuses()
{
    // hide every table row
    $('table tbody > tr').hide();

    // show just the checked statuses
    $('input[name=status]:checked').each(function(idx) {
        let status = $(this).attr('id');
        $('tbody > tr.' + status).show();
    });
}
</script>
@endsection
