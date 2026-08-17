{{--
    One club's full team list. Expects $club (with teams loaded).

    Shared by Manage -> Teams (managed teams) and Manage -> Opponents, so a club
    that has both managed and unmanaged teams still shows every team somewhere.
--}}
<div id="club-{{ $club->id }}" class="modal modal-lg fade" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content py-4 px-2">
            <div class="modal-header">
                <img class="logo img-fluid ms-2 me-3" src="{{ asset($club->logo) }}" onerror="this.onerror=null;this.src='{{ asset('img/logo_none.png') }}';"/>
                {{ $club->name }}
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>{{ $club->notes }}</p>
                <p><a href="{{ route('games.index', ['filter-seasons' => '', 'filter-clubs' => $club->id]) }}">See all games</a></p>
            </div>
            <div class="modal-body">
                <table class="table align-middle">
                    <thead class="">
                        <tr>
                            <th>Team</th>
                            <th>Year</th>
                            <th>Rank</th>
                            <th>Notes</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody class="table-group-divider">
                    @foreach($club->teams as $team)
                        <tr>
                            <td>
                                {{ $team->name }}
                            @if($team->managed)
                                <div class="fw-bold fst-italic small">* Managed</div>
                            @endif
                            </td>
                            <td>{{ $team->birth_year }}</td>
                            <td>
                                <span @class([
                                    'badge',
                                    'text-bg-success text-white' => $team->rank == 'A',
                                    'text-bg-info' => $team->rank == 'B',
                                    'text-bg-warning' => $team->rank == 'C',
                                    'text-bg-danger' => $team->rank == 'D',
                                    'text-bg-secondary' => $team->rank == null,
                                ])>{{ $team->rank ?: '?' }}</span>
                            </td>
                            <td>{{ $team->notes }}</td>
                            <td>
                                <a href="{{ route('teams.edit', ['id' => $team->id]) }}"><span class="bi-pencil"></span></a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <script>
                $('#club-{{ $club->id }}').on('shown.bs.modal', function (e) {
                    $(this).find('table').DataTable({
                        autoWidth: false,
                        paging: false,
                        searching: false,
                        info: false
                    });
                });
                </script>
            </div>
        </div>
    </div>
</div><!--/.modal-->
