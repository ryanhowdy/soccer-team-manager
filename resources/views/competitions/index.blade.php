@extends('layouts.main')

@section('body-id', 'stats')

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

        <div class="rounded rounded-3 bg-white py-2 px-3 mb-2">
            <a href="#" class="float-end btn btn-sm btn-primary text-white" data-bs-toggle="modal" data-bs-target="#create-competition">
                <span class="bi-plus-lg pe-2"></span>Add Competition
            </a>

            <ul id="comp-types" class="nav nav-underline">
                <li class="nav-item">
                    <a class="nav-link active" id="league-tab" data-bs-toggle="tab" data-bs-target="#league-pane" href="#">League</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="cup-tab" data-bs-toggle="tab" data-bs-target="#cup-pane" href="#">Cup</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="friendly-tab" data-bs-toggle="tab" data-bs-target="#friendly-pane" href="#">Friendly</a>
                </li>
            </ul>

            <div class="btn-group btn-group-sm my-3" role="group">
                <input type="checkbox" checked class="btn-check" id="status-a" name="status" autocomplete="off">
                <label class="btn btn-outline-light" for="status-a">Active</label>
                <input type="checkbox" class="btn-check" id="status-d" name="status" autocomplete="off">
                <label class="btn btn-outline-light" for="status-d">Done</label>
                <input type="checkbox" class="btn-check" id="status-c" name="status" autocomplete="off">
                <label class="btn btn-outline-light" for="status-c">Cancelled</label>
            </div>
        </div>

        <div class="rounded rounded-3 bg-white position-relative p-4 mb-3">

            <div id="comp-content" class="tab-content">

            @foreach(['league', 'cup', 'friendly'] as $compType)
                <div @class([
                    'tab-pane fade',
                    'show active' => $loop->first,
                    ]) id="{{ $compType }}-pane" tabindex="0">
                    <table id="{{ $compType }}-table" class="table table-bordered">
                        <thead>
                            <th>Name</th>
                            <th class="d-none d-md-table-cell">Place</th>
                            <th class="d-none d-md-table-cell">Level</th>
                            <th class="d-none d-md-table-cell">Start Date</th>
                            <th class="d-none d-md-table-cell">End Date</th>
                            <th>Website</th>
                            <th>Notes</th>
                        </thead>
                        <tbody>
                        @foreach(${$compType . 's'} as $comp)
                            <tr class="status-{{ strtolower($comp->status) }}">
                                <td>
                                    <div>
                                        <a @class([
                                            'link-underline link-underline-opacity-0 link-underline-opacity-100-hover',
                                            'fw-bold link-dark' => $comp->status == 'A',
                                            'fst-italic link-dark' => $comp->status == 'D',
                                            'fst-italic link-secondary text-decoration-line-through' => $comp->status == 'C',
                                            ]) href="{{ route('competitions.show', ['competition' => $comp->id]) }}">{{ $comp->name }}</a>
                                    </div>
                                    <span class="smaller fw-bold text-primary">{{ $comp->division }}</span>
                                </td>
                                <td class="d-none d-md-table-cell text-center">
                                @isset($comp->place)
                                    <div class="fs-1">{{ $comp->place }}</div>
                                @else
                                    <div class="row g-1">
                                        <div class="col-auto">
                                            <input class="form-control place" type="number" data-id="{{ $comp->id }}" min="1" max="99"/>
                                        </div>
                                        <div class="col-auto">
                                            <button class="save-place btn btn-outline-light"
                                                    data-url="{{ route('ajax.competitions.update', ['competition' => $comp->id]) }}">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endisset
                                </td>
                                <td class="d-none d-md-table-cell">
                                @if($comp->total_levels)
                                    <div class="progress bg-light mb-1" style="max-width: 75px;" title="{{ $comp->level }} out of {{ $comp->total_levels }}">
                                        <div @class([
                                            'progress-bar progress-bar-striped',
                                            'text-white bg-success' => $comp->level_percentage >= 99,
                                            'text-bg-info' => ($comp->level_percentage >= 80 && $comp->level_percentage < 99),
                                            'text-bg-dark' => ($comp->level_percentage >= 60 && $comp->level_percentage < 80),
                                            'text-bg-warning' => ($comp->level_percentage >= 40 && $comp->level_percentage < 60),
                                            'bg-danger' => $comp->level_percentage < 40,
                                            ]) role="progressbar" style="width:{{ $comp->level_percentage }}%">
                                            {{ $comp->level }}
                                        </div>
                                    </div>
                                @endif
                                </td>
                                <td class="d-none d-md-table-cell">{{ $comp->started_at->format('Y-m-d') }}</td>
                                <td class="d-none d-md-table-cell">{{ $comp->ended_at->format('Y-m-d') }}</td>
                                <td class="text-center">
                                @if($comp->website)
                                    <a class="fs-3" href="{{ $comp->website }}" target="_blank">
                                        <span class="bi-link"></span>
                                    </a>
                                @endif
                                </td>
                                <td class="text-center">
                                @if($comp->notes)
                                    <a href="#" class="d-inline-block pt-2" data-bs-toggle="popover" data-bs-title="Notes" data-bs-content="{{ $comp->notes }}">
                                        <span class="bi-file-text"></span>
                                    </a>
                                @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <script>
                    $('#{{ $compType }}-table').DataTable({
                        autoWidth: false,
                        paging: false,
                        searching: false,
                        info: false,
                        order: [[3, 'desc']]
                    });
                    </script>
                </div>
            @endforeach

            </div>

        </div>

    </div><!--/container-->

    <div id="create-competition" class="modal fade" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content py-4 px-2">
                <div class="modal-header">
                    <h5 class="modal-title">New Competition</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
@include('competitions.create-form')
                </div>
            </div>
        </div>
    </div>

<script>
$(document).ready(function() {

    showHideStatuses();

    $('.btn-group > input').on('click', function() {
        showHideStatuses();
    });

    $('td button.save-place').on('click', function (e) {
        let $btn   = $(this);
        let $row   = $btn.closest('.row');
        let $place = $row.find('.place');
        let url    = $btn.attr('data-url');

        $.ajax({
            url  : url,
            type : 'POST',
            data : {
                id    : $place.attr('data-id'),
                place : $place.val(),
            },
        }).done(function(ret) {
            $row.before('<div class="fs-1">' + $place.val() + '</div>');
            $row.remove();
        });
    });
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
