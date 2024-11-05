@extends('layouts.main')

@section('body-id', 'player')

@section('content')
    <div class="container main-content">
        <div class="rounded rounded-3 bg-white p-4 mb-4">

            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('players.index') }}">Players</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('players.show', ['player' => $player]) }}">{{ $player->name }}</a></li>
                    <li class="breadcrumb-item active">Season</li>
                </ol>
            </nav>

            <table class="table table-hover table-borderless">
                <thead class="small">
                    <tr class="text-center">
                        <th class="border-end border-secondary-subtle" colspan="4">Game</th>
                        <th class="border-end border-secondary-subtle" colspan="3">Playing Time</th>
                        <th class="border-end border-secondary-subtle" colspan="4">Performance</th>
                        <th></th>
                    </tr>
                    <tr class="text-end">
                        <th class="text-start">Date</th>
                        <th class="text-start">Competition</th>
                        <th class="text-start">Score</th>
                        <th class="text-start border-end border-secondary-subtle">Opponent</th>
                        <th>Start</th>
                        <th class="text-center" data-bs-toggle="tooltip" data-bs-title="Most common starting position">Pos</th>
                        <th class="border-end border-secondary-subtle" data-bs-toggle="tooltip" data-bs-title="Percentage of playing time">Time</th>
                        <th data-bs-toggle="tooltip" data-bs-title="Total Shots">Sh</th>
                        <th data-bs-toggle="tooltip" data-bs-title="Total Shots on Target">Sot</th>
                        <th data-bs-toggle="tooltip" data-bs-title="Total Goals">Gls</th>
                        <th class="border-end border-secondary-subtle" data-bs-toggle="tooltip" data-bs-title="Total Assists">Ast</th>
                        <th class="text-start">Details</th>
                    </tr>
                </thead>
                <tbody class="border-top border-secondary">
                @foreach($stats['games'] as $id => $s)
                    <tr class="text-end">
                        <td class="text-start">
                            {{ $stats['_result_data_lkup'][$id]->date->inUserTimezone()->format('Y-m-d') }}
                        </td>
                        <td class="text-start">
                            <a class="link-dark link-underline-opacity-0 link-underline-opacity-100-hover link-offset-2-hover"
                                href="{{ route('competitions.show', ['competition' => $stats['_result_data_lkup'][$id]->competition_id]) }}">
                                {{ $stats['_result_data_lkup'][$id]->competition_name }}
                            </a>
                        </td>
                        <td class="text-start">
                            <span @class([
                                'text-success' => $stats['_result_data_lkup'][$id]->win_draw_loss == 'W',
                                'text-muted' => $stats['_result_data_lkup'][$id]->win_draw_loss == 'D',
                                'text-danger' => $stats['_result_data_lkup'][$id]->win_draw_loss == 'L',
                            ])>
                                {{ $stats['_result_data_lkup'][$id]->home_team_score }} - {{ $stats['_result_data_lkup'][$id]->away_team_score }}
                            </span>
                        </td>
                        <td class="text-start border-end border-secondary-subtle">
                        @if($stats['_result_data_lkup'][$id]->homeTeam->managed)
                            <span data-bs-toggle="tooltip" data-bs-title="{{ $stats['_result_data_lkup'][$id]->awayTeam->name }}">
                                {{ $stats['_result_data_lkup'][$id]->awayTeam->club->name }}
                            </span>
                        @else
                            <span data-bs-toggle="tooltip" data-bs-title="{{ $stats['_result_data_lkup'][$id]->homeTeam->name }}">
                                {{ $stats['_result_data_lkup'][$id]->homeTeam->club->name }}
                            </span>
                        @endif
                        </td>
                        <td>
                        @if($s['events'])
                            @if($s['starts'])
                                <span class="bi bi-check-square-fill text-success"></span>
                            @else
                                <span class="bi bi-x-lg text-danger"></span>
                            @endif
                        @endif
                        </td>
                        <td>
                            {{-- $s['position'] --}}
                        </td>
                        <td class="border-end border-secondary-subtle">
                        @if($s['playingTime']['possible_secs'])
                            <span data-bs-toggle="tooltip" 
                                data-bs-title="{{ $s['playingTime']['minutes'] }} out of {{ $s['playingTime']['possible_mins'] }} mins">
                                {{ number_format(($s['playingTime']['minutes'] / $s['playingTime']['possible_mins']) * 100) }}&percnt;
                            </span>
                        @endif
                        </td>
                        <td>@if($s['events']){{ $s['shots'] }}@endif</td>
                        <td>@if($s['events']){{ $s['shots_on'] }}@endif</td>
                        <td class="text-info">@if($s['events']){{ $s['goals'] }}@endif</td>
                        <td class="border-end border-secondary-subtle">@if($s['events']){{ $s['assists'] }}@endif</td>
                        <td class="text-start"><a href="{{ route('games.show', ['id' => $id]) }}">Details</a></td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-top border-secondary fs-5 text-end">
                        <td class="text-start border-end border-secondary-subtle" colspan="4">Total</td>
                        <td>
                        @if($stats['totals']['all']['games'])
                            <span data-bs-toggle="tooltip" data-bs-title="{{ $stats['totals']['all']['starts'] }} out of {{ $stats['totals']['all']['games'] }} games">
                                {{ number_format(($stats['totals']['all']['starts'] / $stats['totals']['all']['games']) * 100) }}&percnt;
                            </span>
                        @endif
                        </td>
                        <td>{{-- $stats['totals']['all']['position'] --}}</td>
                        <td class="border-end border-secondary-subtle">
                        @if($stats['totals']['all']['playingTime']['possible_secs'])
                            <span data-bs-toggle="tooltip" 
                                data-bs-title="{{ $stats['totals']['all']['playingTime']['minutes'] }} out of {{ $stats['totals']['all']['playingTime']['possible_mins'] }} mins">
                                {{ number_format(($stats['totals']['all']['playingTime']['minutes'] / $stats['totals']['all']['playingTime']['possible_mins']) * 100) }}&percnt;
                            </span>
                        @endif
                        <td>{{ $stats['totals']['all']['shots'] }}</td>
                        <td>{{ $stats['totals']['all']['shots_on'] }}</td>
                        <td class="text-info">{{ $stats['totals']['all']['goals'] }}</td>
                        <td class="border-end border-secondary-subtle">{{ $stats['totals']['all']['assists'] }}</td>
                        <td></td>
                    </tr>
                </tfoot>
        @foreach($stats['totals'] as $type => $s)
            @if($s['games'] && $type != 'all')
                <tfoot>
                    <tr class="border-top border-secondary fst-italic text-end">
                        <td class="text-start border-end border-secondary-subtle" colspan="4">{{ $type }}</td>
                        <td>
                        @if($s['games'])
                            <span data-bs-toggle="tooltip" data-bs-title="{{ $s['starts'] }} out of {{ $s['games'] }} games">
                                {{ number_format(($s['starts'] / $s['games']) * 100) }}&percnt;
                            </span>
                        @endif
                        </td>
                        <td>{{-- $s['position'] --}}</td>
                        <td class="border-end border-secondary-subtle">
                        @if($s['playingTime']['possible_secs'])
                            <span data-bs-toggle="tooltip" 
                                data-bs-title="{{ $s['playingTime']['minutes'] }} out of {{ $s['playingTime']['possible_mins'] }} mins">
                                {{ number_format(($s['playingTime']['minutes'] / $s['playingTime']['possible_mins']) * 100) }}&percnt;
                            </span>
                        @endif
                        </td>
                        <td>{{ $s['shots'] }}</td>
                        <td>{{ $s['shots_on'] }}</td>
                        <td class="text-info">{{ $s['goals'] }}</td>
                        <td class="border-end border-secondary-subtle">{{ $s['assists'] }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            @endif
        @endforeach
            </table>
        </div>

    </div>
@endsection
