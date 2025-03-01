<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/charts.css/dist/charts.min.css">
<div class="rounded rounded-3 bg-white p-4 mb-3">
    <h3 class="mb-3">Momentum</h3>
    <div id="home-momentum-chart">
        <table class="charts-css area show-data-on-hover">
            <tbody></tbody>
        </table>
    </div>
    <div id="away-momentum-chart">
        <table class="charts-css area reverse show-data-on-hover">
            <tbody></tbody>
        </table>
    </div>
</div>
<script>
$(document).ready(function() {
    $('#momentum-tab').click(function() {
        $.ajax({
            url  : '{{ route('ajax.results.events.momentum', ['result' => $result->id]) }}',
        }).done((data) => {
            console.log(data);

            let prev = '0.0';

            for (let time in data.data['home'])
            {
                let tr = document.createElement('tr');

                let td = document.createElement('td');
                td.style.setProperty('--start', prev);
                td.style.setProperty('--end', data.data['home'][time]['total']);

                let span = document.createElement('span');
                span.className = 'data';
                span.textContent = time;

                td.append(span);
                tr.append(td)
                $('#home-momentum-chart > table > tbody').append(tr);

                prev = data.data['home'][time]['total'];
            }

            prev = '0.0';

            for (let time in data.data['away'])
            {
                let tr = document.createElement('tr');

                let td = document.createElement('td');
                td.style.setProperty('--start', prev);
                td.style.setProperty('--end', data.data['away'][time]['total']);

                let span = document.createElement('span');
                span.className = 'data';
                span.textContent = time;

                td.append(span);
                tr.append(td)
                $('#away-momentum-chart > table > tbody').append(tr);

                prev = data.data['away'][time]['total'];
            }
        }).fail(() => {
            $('#home-momentum-chart').before('<p class="alert alert-danger mt-2">Something went wrong, couldn\'t get momentum data.</p>');
        });
    });
});
</script>
