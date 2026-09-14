<div class="rounded rounded-3 bg-white p-4 mb-3">
    <div class="d-flex justify-content-between align-items-start mb-3">
        <h3 class="mb-0">Momentum</h3>
        <div class="text-end small text-muted">
            <div><span class="momentum-key" data-side="home"></span> {{ $result->homeTeam->short_name }}</div>
            <div><span class="momentum-key" data-side="away"></span> {{ $result->awayTeam->short_name }}</div>
        </div>
    </div>
    <div id="momentum-chart-wrap">
        <canvas id="momentum-chart"></canvas>
    </div>
</div>
<script>
$(document).ready(function() {
    let momentumChart = null;

    let homeColor = @json($teamColors['home']);
    let awayColor = @json($teamColors['away']);
    let homeName  = @json($result->homeTeam->short_name);
    let awayName  = @json($result->awayTeam->short_name);

    let goalFont = '26px "Material Symbols Outlined"';

    // Softer version of a club colour, so the filled areas stay readable.
    let translucent = function(hex) {
        let n = parseInt(hex.replace('#', ''), 16);
        return 'rgba(' + [(n >> 16) & 255, (n >> 8) & 255, n & 255].join(',') + ',0.55)';
    };

    $('.momentum-key[data-side=home]').css('background-color', homeColor);
    $('.momentum-key[data-side=away]').css('background-color', awayColor);

    $('#momentum-tab').on('click', function() {
        if (momentumChart) {
            return; // already drawn
        }

        $.ajax({
            url : '{{ route('ajax.results.events.momentum', ['result' => $result->id]) }}',
        }).done((response) => {
            let data = response.data;

            // Goals and cards, drawn on the line at the minute they happened.
            // A minute can hold more than one, so they get laid out side by side.
            let markerAt = {};

            data.markers.forEach((m) => {
                markerAt[m.minute] = markerAt[m.minute] || [];
                markerAt[m.minute].push(m);
            });

            // Drawn here rather than as dataset points so the goal keeps the
            // same soccer ball the rest of the game pages use, and so a marker
            // on the peak can overflow the plot instead of being clipped.
            let drawMarkers = {
                id: 'momentumMarkers',
                afterDatasetsDraw(chart) {
                    let meta = chart.getDatasetMeta(0);
                    let ctx  = chart.ctx;

                    ctx.save();
                    ctx.textAlign    = 'center';
                    ctx.textBaseline = 'middle';

                    let zeroY = chart.scales.y.getPixelForValue(0);

                    data.minutes.forEach((minute, i) => {
                        let list = markerAt[minute];

                        if (!list || !meta.data[i]) {
                            return;
                        }

                        let point = meta.data[i];

                        // A marker belongs to the team it happened to, which is
                        // not always the team with the momentum - a booking is
                        // the other side's swing.  So each side's markers stay
                        // in that side's half, clear of the curve where the
                        // curve is on their side, and just off the centre line
                        // where it is not.
                        ['home', 'away'].forEach((side) => {
                            let group = list.filter((m) => m.side == side);

                            if (!group.length) {
                                return;
                            }

                            let y = side == 'home'
                                ? Math.min(point.y, zeroY) - 17
                                : Math.max(point.y, zeroY) + 17;

                            // Never past the edge of the canvas - a marker on
                            // the game's peak swing sits hard against the top.
                            y = Math.min(Math.max(y, 15), chart.height - 15);

                            group.forEach((m, n) => {
                                let x = point.x + (n * 15) - ((group.length - 1) * 7.5);

                                if (m.type == 'goal') {
                                    ctx.font      = goalFont;
                                    ctx.fillStyle = '#212529';
                                    ctx.fillText('sports_soccer', x, y);

                                    return;
                                }

                                ctx.fillStyle   = m.type == 'yellow' ? '#ffc107' : '#dc3545';
                                ctx.strokeStyle = '#212529';
                                ctx.lineWidth   = 1.2;

                                ctx.beginPath();
                                if (ctx.roundRect) {
                                    ctx.roundRect(x - 5.5, y - 7.5, 11, 15, 2);
                                } else {
                                    ctx.rect(x - 5.5, y - 7.5, 11, 15);
                                }
                                ctx.fill();
                                ctx.stroke();
                            });
                        });
                    });

                    ctx.restore();
                },
            };

            momentumChart = new Chart(document.getElementById('momentum-chart'), {
                type: 'line',
                plugins: [drawMarkers],
                data: {
                    labels: data.minutes,
                    datasets: [{
                        data: data.values,
                        fill: {
                            target: 'origin',
                            above: translucent(homeColor),
                            below: translucent(awayColor),
                        },
                        borderColor: 'rgba(0,0,0,0.25)',
                        borderWidth: 1,
                        tension: 0.35,
                        pointRadius: 0,
                        pointHoverRadius: 0,
                    }],
                },
                options: {
                    animation: false,
                    maintainAspectRatio: false,
                    // Room outside the plot for a marker sitting on a peak.
                    layout: { padding: { top: 22, bottom: 22 } },
                    interaction: { intersect: false, mode: 'index' },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            displayColors: false,
                            callbacks: {
                                title: (items) => items[0].label + "'",
                                // Only the minute, and any goal or card in it.
                                label: (item) => (markerAt[item.label] || []).map((m) => {
                                    let who  = m.player || (m.side == 'home' ? homeName : awayName);
                                    let what = { goal: 'Goal', yellow: 'Yellow card', red: 'Red card' }[m.type];

                                    return what + ' — ' + who;
                                }),
                            },
                        },
                    },
                    scales: {
                        x: {
                            title: { display: true, text: 'Minute' },
                            ticks: { maxTicksLimit: 10, autoSkip: true },
                            grid: { display: false },
                        },
                        y: {
                            min: -1.25,
                            max: 1.25,
                            title: { display: false },
                            ticks: { display: false },
                            grid: { color: (ctx) => ctx.tick.value === 0 ? 'rgba(0,0,0,0.35)' : 'rgba(0,0,0,0.05)' },
                        },
                    },
                },
            });

            // The ball is a webfont glyph, so redraw once it has actually landed.
            if (document.fonts && document.fonts.load) {
                document.fonts.load(goalFont).then(() => momentumChart.update('none'));
            }
        }).fail(() => {
            $('#momentum-chart-wrap').before('<p class="alert alert-danger mt-2">Something went wrong, couldn\'t get momentum data.</p>');
        });
    });
});
</script>
