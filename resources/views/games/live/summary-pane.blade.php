    <div class="d-flex justify-content-between pb-1 mb-2 border-bottom">
        <div class="pe-3 text-secondary">{{ $result->homeTeam->name }}</div>
        <div class="ps-3 text-secondary text-end">{{ $result->awayTeam->name }}</div>
    </div>
    <div class="d-flex justify-content-between">
        <div id="game-goals-home">0</div>
        <div>Goals</div>
        <div id="game-goals-away">0</div>
    </div>
    <div class="progress game-goals-progress rounded-0 mb-2"><div style="width:50%" class="progress-bar"></div></div>
    <div class="d-flex justify-content-between">
        <div id="game-shots-home">0</div>
        <div>Shots</div>
        <div id="game-shots-away">0</div>
    </div>
    <div class="progress game-shots-progress rounded-0 mb-2"><div style="width:50%" class="progress-bar"></div></div>
    <div class="d-flex justify-content-between">
        <div id="game-shots-on-home">0</div>
        <div>(On Target)</div>
        <div id="game-shots-on-away">0</div>
    </div>
    <div class="progress game-shots-on-progress rounded-0 mb-2"><div style="width:50%" class="progress-bar"></div></div>
    <div class="d-flex justify-content-between">
        <div id="game-shots-off-home">0</div>
        <div>(Off Target)</div>
        <div id="game-shots-off-away">0</div>
    </div>
    <div class="progress game-shots-off-progress rounded-0 mb-2"><div style="width:50%" class="progress-bar"></div></div>
    <div class="d-flex justify-content-between">
        <div id="game-corners-home">0</div>
        <div>Corners</div>
        <div id="game-corners-away">0</div>
    </div>
    <div class="progress game-corners-progress rounded-0 mb-2"><div style="width:50%" class="progress-bar"></div></div>
    <div class="d-flex justify-content-between">
        <div id="game-offsides-home">0</div>
        <div>Offsides</div>
        <div id="game-offsides-away">0</div>
    </div>
    <div class="progress game-offsides-progress rounded-0 mb-2"><div style="width:50%" class="progress-bar"></div></div>
    <div class="d-flex justify-content-between">
        <div id="game-fouls-home">0</div>
        <div>Fouls</div>
        <div id="game-fouls-away">0</div>
    </div>
    <div class="progress game-fouls-progress rounded-0 mb-2"><div style="width:50%" class="progress-bar"></div></div>
