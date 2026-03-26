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

        <form class="keepAlive"></form>

        <div class="d-flex justify-content-between mb-3">
            <div><h2>Competitions</h2></div>
            <div class="d-flex gap-2 align-items-center justify-content-end">
                <div class="pe-2">
                    <div class="dropdown">
                        <button type="button" class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                            <span class="d-none d-lg-inline-block">Filter</span><span class="bi-filter ps-1"></span>
                        </button>
                        <div class="dropdown-menu p-3" style="width:300px">
                            <p><b>Type</b></p>
                            <div class="mb-3">
                                <input type="checkbox" checked class="btn-check" id="type-league" name="type" autocomplete="off">
                                <label class="btn btn-sm btn-outline-info rounded-pill" for="type-league">League</label>
                                <input type="checkbox" checked class="btn-check" id="type-cup" name="type" autocomplete="off">
                                <label class="btn btn-sm btn-outline-info rounded-pill" for="type-cup">Cup</label>
                                <input type="checkbox" checked class="btn-check" id="type-friendly" name="type" autocomplete="off">
                                <label class="btn btn-sm btn-outline-info rounded-pill" for="type-friendly">Friendly</label>
                            </div>
                            <p><b>Status</b></p>
                            <div class="mb-3">
                                <input type="checkbox" checked class="btn-check" id="status-a" name="status" autocomplete="off">
                                <label class="btn btn-sm btn-outline-info rounded-pill" for="status-a">Active</label>
                                <input type="checkbox" class="btn-check" id="status-d" name="status" autocomplete="off">
                                <label class="btn btn-sm btn-outline-info rounded-pill" for="status-d">Done</label>
                                <input type="checkbox" class="btn-check" id="status-c" name="status" autocomplete="off">
                                <label class="btn btn-sm btn-outline-info rounded-pill" for="status-c">Cancelled</label>
                            </div>
                        </div>
                    </div><!--/.dropdown-->
                </div>
            @can('edit things')
                <div class="">
                    <div class="vr"></div>
                </div>
                <div class="ps-2">
                    <a href="#" class="btn btn-sm btn-dark text-white rounded-pill py-2 px-3" data-bs-toggle="modal" data-bs-target="#create-competition">
                        <span class="bi-plus-lg pe-0 pe-lg-2"></span><span class="d-none d-lg-inline-block">Add Competition</span>
                    </a>
                </div>
            @endcan
            </div>
        </div>


        <div class="rounded rounded-3 bg-white position-relative p-4 mb-3">

            <table id="comp-table" class="table table-bordered">
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
            @foreach(['league', 'cup', 'friendly'] as $compType)
                @foreach(${$compType . 's'} as $comp)
                    <tr class="type-{{ $compType }} status-{{ strtolower($comp->status) }}">
                        <td>
                            <div class="text-muted small text-uppercase">
                                {{ $compType }}
                            </div>
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
            @endforeach
                </tbody>
            </table>

            <div id="empty-state" class="d-none p-5 text-center">
                <img class="opacity-50 w-25" src="{{ asset('img/empty-state.svg') }}">
                <div class="fs-3 fw-bold mt-5 pb-1">No Competitions</div>
                <small class="pb-3 d-block text-muted">No competitions found, please update the filters or click the button below to add a new competition.</small>
                <a href="#" class="btn btn-lg btn-primary text-white" data-bs-toggle="modal" data-bs-target="#create-competition">Add Competition</a>
            </div>

            <script>
            $('#comp-table').DataTable({
                autoWidth: false,
                paging: false,
                searching: false,
                info: false,
                order: [[3, 'desc']]
            });
            </script>

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

    showHideRows();

    $('input[name="type"],input[name="status"]').on('click', function() {
        showHideRows();
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

function showHideRows()
{
    let types = [];
    let statuses = [];

    $('input[name=type]:checked').each(function() {
        types.push('.' + $(this).attr('id'));
    });
    $('input[name=status]:checked').each(function() {
        statuses.push('.' + $(this).attr('id'));
    });

    $('table tbody > tr').each(function() {
        let $row = $(this);
        let matchesType = types.length === 0 || types.some(t => $row.is(t));
        let matchesStatus = statuses.length === 0 || statuses.some(s => $row.is(s));
        $row.toggle(matchesType && matchesStatus);
    });

    if ($('#comp-table tbody tr:visible').length)
    {
        $('#empty-state').addClass('d-none');
    }
    else
    {
        $('#empty-state').removeClass('d-none');
    }
}
</script>
@endsection
