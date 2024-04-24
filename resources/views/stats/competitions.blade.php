@extends('layouts.main')

@section('body-id', 'stats')
@section('page-title', 'Competitions')
@section('page-desc', "Choose a competition to see statistics")

@section('content')
    <div class="container main-content">

        <div class="rounded rounded-3 bg-white position-relative p-4 mb-3">

            <ul id="comp-types" class="nav nav-underline">
                <li class="nav-item">
                    <a class="nav-link active" id="league-tab" data-bs-toggle="tab" data-bs-target="#league-pane" href="#">League</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="cup-tab" data-bs-toggle="tab" data-bs-target="#cup-pane" href="#">Cup</a>
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

            <div id="comp-content" class="tab-content">

            @foreach(['league', 'cup'] as $compType)
                <div @class([
                    'tab-pane fade',
                    'show active' => $loop->first,
                    ]) id="{{ $compType }}-pane" tabindex="0">
                    <table id="{{ $compType }}-table" class="table table-bordered">
                        <thead>
                            <th>Name</th>
                            <th>Place</th>
                            <th>Level</th>
                            <th>Start Date</th>
                            <th>End Date</th>
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
                                            ]) href="{{ route('stats.competitions.show', ['id' => $comp->id]) }}">{{ $comp->name }}</a>
                                    </div>
                                    <span class="smaller fw-bold text-primary">{{ $comp->division }}</span>
                                </td>
                                <td></td>
                                <td>
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
                                <td>{{ $comp->started_at->format('Y-m-d') }}</td>
                                <td>{{ $comp->ended_at->format('Y-m-d') }}</td>
                                <td>
                                @if($comp->website)
                                    <a class="fs-3" href="{{ $comp->website }}" target="_blank"><span class="bi-link"></span></a>
                                @endif
                                </td>
                                <td>
                                @if($comp->notes)
                                    <span class="bi-file-text" data-bs-toggle="popover" data-bs-title="Notes"
                                        data-bs-content="{{ $comp->notes }}"></span>
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
<script>
showHideStatuses();
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
