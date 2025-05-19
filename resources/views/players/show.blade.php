@extends('layouts.main')

@section('body-id', 'player')

@section('content')
    <script>
const getOrCreateLegendList = (chart, id) => {
  const legendContainer = document.getElementById(id);
  let listContainer = legendContainer.querySelector('ul');

  if (!listContainer) {
    listContainer = document.createElement('ul');
    listContainer.classList.add('list-unstyled');

    legendContainer.appendChild(listContainer);
  }

  return listContainer;
};

const htmlLegendPlugin = {
  id: 'htmlLegend',
  afterUpdate(chart, args, options) {
    const ul = getOrCreateLegendList(chart, options.containerID);

    // Remove old legend items
    while (ul.firstChild) {
      ul.firstChild.remove();
    }

    // Reuse the built-in legendItems generator
    const items = chart.options.plugins.legend.labels.generateLabels(chart);

    items.forEach(item => {
      const li = document.createElement('li');
      li.classList.add('position-relative');
      li.classList.add('my-2');
      li.style.paddingLeft = '40px';

      // Color box
      const box = document.createElement('div');
      box.classList.add('position-absolute');
      box.classList.add('top-0');
      box.classList.add('start-0');
      box.style.border = '8px solid ' + item.fillStyle;
      box.style.borderRadius = '25px';
      box.style.height = '25px';
      box.style.width = '25px';

      // Text
      const textContainer = document.createElement('div');

      const b = document.createElement('b');
      b.classList.add('pe-2');

      const text = document.createTextNode(item.text);

      b.appendChild(text);
      textContainer.appendChild(b);

      if (chart.data.datasets[0].data[item.index] > 0) {
          const span = document.createElement('span');
          span.classList.add('small');
          span.classList.add('text-muted');

          const val = document.createTextNode(chart.data.datasets[0].data[item.index]);

          span.appendChild(val);
          textContainer.appendChild(span);
      }

      li.appendChild(box);
      li.appendChild(textContainer);
      ul.appendChild(li);
    });
  }
};
    </script>
    <div class="container main-content">

        <div class="rounded rounded-3 bg-white p-4 mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('players.index') }}">Players</a></li>
                    <li class="breadcrumb-item active">{{ $player->name }}</li>
                </ol>
            </nav>
        </div>

        {{-- Chart Row --}}
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="rounded rounded-3 bg-white p-4 mb-4">
                    <h3>Goals</h3>
                    <div class="row">
                        <div class="col-12 col-md-7">
                            <canvas id="goals-chart"></canvas>
                        </div>
                        <div class="col-12 col-md-5">
                            <div id="goals-legend"></div>
                            <script>
                            let goalChart = document.getElementById('goals-chart');
                            new Chart(goalChart, {
                                type: 'doughnut',
                                data: {
                                    labels: [{!! $charts['goals']['labels'] !!}],
                                    datasets: [{
                                        data: [{!! $charts['goals']['data'] !!}],
                                        backgroundColor: $chartColors,
                                    }]
                                },
                                options: {
                                    plugins: {
                                        htmlLegend: {
                                            containerID: 'goals-legend'
                                        },
                                        legend: { display: false }
                                    }
                                },
                                plugins: [htmlLegendPlugin]
                            });
                            </script>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="rounded rounded-3 bg-white p-4 mb-4">
                    <h3>Assists</h3>
                    <div class="row">
                        <div class="col-12 col-md-7">
                            <canvas id="assists-chart"></canvas>
                        </div>
                        <div class="col-12 col-md-5">
                            <div id="assists-legend"></div>
                            <script>
                            let assistChart = document.getElementById('assists-chart');
                            new Chart(assistChart, {
                                type: 'doughnut',
                                data: {
                                    labels: [{!! $charts['assists']['labels'] !!}],
                                    datasets: [{
                                        data: [{!! $charts['assists']['data'] !!}],
                                        backgroundColor: $chartColors,
                                    }]
                                },
                                options: {
                                    plugins: {
                                        htmlLegend: {
                                            containerID: 'assists-legend'
                                        },
                                        legend: { display: false }
                                    }
                                },
                                plugins: [htmlLegendPlugin]
                            });
                            </script>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="rounded rounded-3 bg-white p-4 mb-4">
            <div class="table-responsive">
                <table class="table table-hover table-borderless">
                    <thead class="small">
                        <tr class="text-center">
                            <th class="border-end border-secondary-subtle"></th>
                            <th class="border-end border-secondary-subtle" colspan="4">Playing Time</th>
                            <th class="border-end border-secondary-subtle" colspan="4">Performance</th>
                            <th class="border-end border-secondary-subtle d-none d-lg-table-cell" colspan="4">Per Game</th>
                            <th></th>
                        </tr>
                        <tr class="text-end">
                            <th class="text-start border-end border-secondary-subtle">Season</th>
                            <th >Games</th>
                            <th data-bs-toggle="tooltip" data-bs-title="Percentage of games started">Starts</th>
                            <th class="text-center" data-bs-toggle="tooltip" data-bs-title="Most common starting position">Pos</th>
                            <th class="border-end border-secondary-subtle" data-bs-toggle="tooltip" data-bs-title="Percentage of playing time">Time</th>
                            <th data-bs-toggle="tooltip" data-bs-title="Total Shots">Sh</th>
                            <th data-bs-toggle="tooltip" data-bs-title="Total Shots on Target">Sot</th>
                            <th data-bs-toggle="tooltip" data-bs-title="Total Goals">Gls</th>
                            <th class="border-end border-secondary-subtle" data-bs-toggle="tooltip" data-bs-title="Total Assists">Ast</th>
                            <th class="d-none d-lg-table-cell" data-bs-toggle="tooltip" data-bs-title="Shots Per Game">Sh</th>
                            <th class="d-none d-lg-table-cell" data-bs-toggle="tooltip" data-bs-title="Shots on Target Per Game">Sot</th>
                            <th class="d-none d-lg-table-cell" data-bs-toggle="tooltip" data-bs-title="Goals Per Game">Gls</th>
                            <th class="d-none d-lg-table-cell border-end border-secondary-subtle" data-bs-toggle="tooltip" data-bs-title="Assists Per Game">Ast</th>
                            <th class="text-start">Details</th>
                        </tr>
                    </thead>
                    <tbody class="border-top border-secondary">
                    @foreach($stats['seasons'] as $season => $s)
                        <tr class="text-end">
                            <td class="text-start border-end border-secondary-subtle">{{ $season }}</td>
                            <td>{{ $s['games'] }}</td>
                            <td>
                            @if($s['games'] && $s['events'])
                                <span data-bs-toggle="tooltip" data-bs-title="{{ $s['starts'] }} out of {{ $s['games'] }} games">
                                    {{ number_format(($s['starts'] / $s['games']) * 100) }}&percnt;
                                </span>
                            @endif
                            </td>
                            <td>
                            @if($s['position']['total'])
                                @php arsort($s['position']['positions']); reset($s['position']['positions']); @endphp
                                <a tabindex="0" class="link-secondary link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover" 
                                    data-bs-toggle="popover" data-trigger="focus" data-bs-title="Positions" 
                                    data-bs-content=" @foreach($s['position']['positions'] as $pos => $count) {{ $pos }} {{ number_format(($count / $s['position']['total']) * 100) }}&percnt;<br/> @endforeach ">
                                    {{ key($s['position']['positions']) }}
                                </a>
                            @endif
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
                            <td class="d-none d-lg-table-cell">@if($s['events']){{ number_format($s['shots'] / $s['games'], 2) }}@endif</td>
                            <td class="d-none d-lg-table-cell">@if($s['events']){{ number_format($s['shots_on'] / $s['games'], 2)}}@endif</td>
                            <td class="d-none d-lg-table-cell">@if($s['events']){{ number_format($s['goals'] / $s['games'], 2) }}@endif</td>
                            <td class="d-none d-lg-table-cell border-end border-secondary-subtle">@if($s['events']){{ number_format($s['assists'] / $s['games'], 2) }}@endif</td>
                            <td class="text-start">
                                <a href="{{ route('players.seasons.show', ['player' => $stats['_player_id'], 'season' => $s['_id']]) }}">Details</a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-top border-secondary fs-5 text-end">
                            <td class="text-start border-end border-secondary-subtle">Total</td>
                            <td>{{ $stats['totals']['all']['games'] }}</td>
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
                            <td class="d-none d-lg-table-cell">
                            @if($stats['totals']['all']['games'])
                                {{ number_format($stats['totals']['all']['shots'] / $stats['totals']['all']['games'], 2) }}
                            @endif
                            </td>
                            <td class="d-none d-lg-table-cell">
                            @if($stats['totals']['all']['games'])
                                {{ number_format($stats['totals']['all']['shots_on'] / $stats['totals']['all']['games'], 2)}}
                            @endif
                            </td>
                            <td class="d-none d-lg-table-cell">
                            @if($stats['totals']['all']['games'])
                                {{ number_format($stats['totals']['all']['goals'] / $stats['totals']['all']['games'], 2) }}
                            @endif
                            </td>
                            <td class="d-none d-lg-table-cell border-end border-secondary-subtle">
                            @if($stats['totals']['all']['games'])
                                {{ number_format($stats['totals']['all']['assists'] / $stats['totals']['all']['games'], 2) }}
                            @endif
                            </td>
                            <td class="text-start">Details</td>
                        </tr>
                    </tfoot>
            @foreach($stats['totals'] as $type => $s)
                @if($s['games'] && $type != 'all')
                    <tfoot>
                        <tr class="border-top border-secondary fst-italic text-end">
                            <td class="text-start border-end border-secondary-subtle">{{ $type }}</td>
                            <td>{{ $s['games'] }}</td>
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
                            <td class="border-secondary-subtle">{{ $s['assists'] }}</td>
                            <td class="d-none d-lg-table-cell">{{ number_format($s['shots'] / $s['games'], 2) }}</td>
                            <td class="d-none d-lg-table-cell">{{ number_format($s['shots_on'] / $s['games'], 2)}}</td>
                            <td class="d-none d-lg-table-cell">{{ number_format($s['goals'] / $s['games'], 2) }}</td>
                            <td class="d-none d-lg-table-cell border-end border-end border-secondary-subtle">{{ number_format($s['assists'] / $s['games'], 2) }}</td>
                            <td class="text-start">Details</td>
                        </tr>
                    </tfoot>
                @endif
            @endforeach
                </table>
            </div>
        </div>

    </div>

    <script>
    $(document).ready(function() {
        var options = { html: true };
        const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]')
        const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl, options))
    });
    </script>
@endsection
