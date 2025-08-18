<div class="rounded rounded-3 bg-white p-4 mb-3">
    <h3 class="mb-3">Possession</h3>

    <div class="row">
        <div class="col-12 col-md-6">
            <canvas id="pos-chart" class="p-3 mb-2"></canvas>
        </div>
        <div class="col-12 col-md-6">
            <div class="p-5">
                <div class="d-flex justify-content-between p-2">
                    <div>
                        <span class="badge" style="background: {{ $teamColors['home'] }}">&nbsp;</span>
                        {{ $result->homeTeam->name }}
                    </div>
                    <div class="text-muted">{{ secondsToMinutes($possession['home']['seconds']) }} mins</div>
                </div>
                <div class="d-flex justify-content-between p-2">
                    <div>
                        <span class="badge" style="background: {{ $teamColors['away'] }}">&nbsp;</span>
                        {{ $result->awayTeam->name }}
                    </div>
                    <div class="text-muted">{{ secondsToMinutes($possession['away']['seconds']) }} mins</div>
                </div>
            </div>
        </div>
    </div><!--/.row-->
</div>
<script>
let posChart = document.getElementById('pos-chart');
new Chart(posChart, {
    type: 'doughnut',
    data: {
        labels: ['{{ $result->homeTeam->name }}', '{{ $result->awayTeam->name }}'],
        datasets: [{
            data: [{{ $possession['home']['seconds'] }}, {{ $possession['away']['seconds'] }}],
            backgroundColor: ["{{ $teamColors['home'] }}", "{{ $teamColors['away'] }}"],
        }]
    },
    options: {
        plugins: {
            legend: { display: false }
        }
    }
});
</script>
