@extends('layouts.main')

@section('body-id', 'stats')
@section('page-title', 'Team Statistics')
@section('page-desc', "Learn all about a team")

@section('content')
    <div class="container main-content">

        <div class="fw-bold text-secondary fs-5 ps-1 pb-2">Location Stats</div>

        <div class="row">

        @foreach($resultsByTeamLocation as $teamId => $resultsByLocation)
            <div class="col-12 col-md-6">
                @php
                    $firstKey = array_key_first($resultsByLocation);
                    $goodGuys = $resultsByLocation[$firstKey][0]->homeTeam->managed ? 'homeTeam' : 'awayTeam';
                @endphp
                <div class="rounded rounded-3 bg-white p-4 mb-3">
                    <h5>{{ $resultsByLocation[$firstKey][0]->{$goodGuys}->name }} </h5>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Location</th>
                                <th>Games</th>
                                <th>Win %</th>
                                <th>Wins</th>
                                <th>Draws</th>
                                <th>Losses</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($resultsByLocation as $locationId => $results)
                            <tr>
                                <td>{{ $results[0]['location']['name'] }}</td>
                                <td>{{ count($results) }}</td>
                            @php
                                $wdl = [
                                    'W' => 0,
                                    'D' => 0,
                                    'L' => 0,
                                ];
                            @endphp
                            @foreach($results as $k => $result)
                                @php
                                    $wdl[ $result['win_draw_loss'] ]++;
                                @endphp
                            @endforeach
                                <td>{{ round($wdl['W'] / count($results) * 100) }}</td>
                                <td>{{ $wdl['W'] }}</td>
                                <td>{{ $wdl['D'] }}</td>
                                <td>{{ $wdl['L'] }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
{{--
            <div class="col-12 col-md-6">
                <div class="rounded rounded-3 bg-white p-4 mb-3">
                    <table class="table">
                        <thead>
                            <tr class="text-center">
                                <th>Location</th>
                                <th>Games</th>
                                <th>Win</th>
                                <th>Draw</th>
                                <th>Loss</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($gamesByLocation as $locationId => $games)
                            <tr>
                                <td>{{ $games[0]['location']['name'] }}</td>
                                <td>{{ count($games) }}</td>
                            @php
                                $wdl = [
                                    'W' => 0,
                                    'D' => 0,
                                    'L' => 0,
                                ];
                            @endphp
                            @foreach($games as $k => $game)
                                @php
                                    $wdl[ $game['win_draw_loss'] ]++;
                                @endphp
                            @endforeach
                                <td>{{ $wdl['W'] }}</td>
                                <td>{{ $wdl['D'] }}</td>
                                <td>{{ $wdl['L'] }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
--}}

        </div>

    </div><!--/container-->

<script>
$('.table').DataTable({
    autoWidth: false,
    paging: false,
    searching: false,
    info: false,
    order: [[2, 'desc'], [3, 'desc']]
});
</script>
@endsection
