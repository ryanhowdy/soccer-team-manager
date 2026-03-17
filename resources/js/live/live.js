export default class Live
{
    constructor()
    {
        this.us   = $('#live-main').attr('data-good-guys');
        this.them = this.us == 'home' ? 'away' : 'home';

        this.timer;
        this.gameStarted = false;
        this.syncTimer   = null;

        String.prototype.pad = function(padString, length) {
            var str = this;
            while (str.length < length)
                str = padString + str;
            return str;
        }

        // Close/Leave page
        addEventListener('beforeunload', (e) => {
            this.confirmExit(e);
        });

        // Click Start Game
        $('.main-content').on('click', '#start-game', (e) => {
            this.clickStartGame(e);
        });

        // Click End Half
        $('.main-content').on('click', '#end-half', (e) => {
            this.clickEndHalf(e);
        });

        // Click Start 2nd Half
        $('.main-content').on('click', '#start-second-half', (e) => {
            this.clickStartSecondHalf(e);
        });

        // Click Timer
        $('.main-content').on('click', '#timer', (e) => {
            this.clickPauseTimer(e);
        });

        // Click End Game
        $('.main-content').on('click', '#end-game', (e) => {
            this.clickEndGame(e);
        });

        // Start polling for sync across windows
        this.startSyncPolling();
    }

    setCurrentFormation(selectedFormationId)
    {
        let formation     = this.formations[selectedFormationId];
        let formationName = this.formations[selectedFormationId].name;

        let dashed = formationName.split('').join('-');

        $('#current-formation > span.badge').text(dashed);

        let state = 'formation';
        let period = $('#live-main').attr('data-period');

        if (period)
        {
            state = period == 'half' ? 'half'
                  : period == '2'    ? 'second'
                  : 'first';
        }

        $('#game-controls').removeClass();
        $('#game-controls').addClass(state + ' row text-center mb-3');

        // hide the formation select box
        $('#formation-form').hide();
        // show the current formation badge
        $('#current-formation').show();

        // draw the formation on the field
        this.drawer.drawFormation(formation);

        // Save this formation id
        this.savedFormationId = selectedFormationId;

        $('.alert').remove();
    }

    /**
     * saveLiveState
     *
     * Save the live timer state to the server.
     *
     * @param {string} period
     * @param {boolean} timerRunning
     * @param {number|null} offsetOverride - if provided, use this offset instead of calculating from timer
     * return null
     */
    saveLiveState(period, timerRunning, offsetOverride)
    {
        let offset = offsetOverride;

        if (offset === undefined)
        {
            offset = this.getCurrentElapsedSeconds();
        }

        $.ajax({
            url  : $('#live-main').attr('data-live-state-route'),
            type : 'POST',
            data : {
                live_period       : period,
                live_timer_offset : offset,
                timer_running     : timerRunning ? 1 : 0,
            },
        }).fail(() => {
            $('#live-main').before('<p class="alert alert-danger mt-2">Something went wrong, couldn\'t save live state.</p>');
        });
    }

    /**
     * getCurrentElapsedSeconds
     *
     * Parse the current timer display and return total elapsed seconds.
     *
     * return {number}
     */
    getCurrentElapsedSeconds()
    {
        let display = $('#timer > span').text();
        let parts   = display.split(':');

        return (parseInt(parts[0]) * 60) + parseInt(parts[1]);
    }

    /**
     * setTimerDisplay
     *
     * Set the timer display from total elapsed seconds.
     *
     * @param {number} totalSeconds
     * return null
     */
    setTimerDisplay(totalSeconds)
    {
        let min = Math.floor(totalSeconds / 60).toString().pad('0', 2);
        let sec = (totalSeconds % 60).toString().pad('0', 2);

        $('#timer > span').empty().append(min + ':' + sec);
    }

    /**
     * startSyncPolling
     *
     * Poll the server every 5 seconds to sync live state across windows.
     *
     * return null
     */
    startSyncPolling()
    {
        if (this.syncTimer !== null)
        {
            return;
        }

        this.syncTimer = setInterval(() => {
            $.ajax({
                url     : $('#live-main').attr('data-live-state-route'),
                type    : 'GET',
                timeout : 3000,
            }).done((data) => {
                this.handleSyncUpdate(data.data);
            });
        }, 5000);
    }

    /**
     * stopSyncPolling
     *
     * return null
     */
    stopSyncPolling()
    {
        clearInterval(this.syncTimer);
        this.syncTimer = null;
    }

    /**
     * handleSyncUpdate
     *
     * Apply server state to the current window if it differs.
     *
     * @param {Object} state
     * return null
     */
    handleSyncUpdate(state)
    {
        let currentPeriod = $('#live-main').attr('data-period');

        // Game just started on another window
        if (state.started && !this.gameStarted)
        {
            this.onSyncGameStarted(state);
            return;
        }

        // Game not started yet
        if (!state.started)
        {
            return;
        }

        // Period changed
        if (state.period !== currentPeriod)
        {
            this.onSyncPeriodChanged(state);
        }
        else
        {
            // Timer running state changed
            let isPaused = $('#timer').hasClass('paused');

            if (state.timerRunning && isPaused)
            {
                // Timer was unpaused on another window
                $('#timer').removeClass('paused');
                $('#live-main').removeClass('paused');
                this.setTimerDisplay(state.timerSeconds);
                this.resumeTimer();
            }
            else if (!state.timerRunning && !isPaused && state.period !== 'half')
            {
                // Timer was paused on another window
                clearInterval(this.timer);
                $('#timer').addClass('paused');
                $('#live-main').addClass('paused');
                this.setTimerDisplay(state.timerSeconds);
            }
        }

        // Sync game data (events, summary, players) — overridden in subclasses
        this.onSyncGameData(state);
    }

    /**
     * onSyncGameData
     *
     * Called on each poll to sync game data (events, summary, players).
     * Override in subclasses for mode-specific behavior.
     *
     * @param {Object} state
     * return null
     */
    onSyncGameData(state)
    {
        // no-op in base class
    }

    /**
     * onSyncGameStarted
     *
     * Called when polling detects the game was started on another window.
     * Override in subclasses for mode-specific behavior.
     *
     * @param {Object} state
     * return null
     */
    onSyncGameStarted(state)
    {
        this.startGame();
        $('#live-main').attr('data-period', state.period);
        this.setTimerDisplay(state.timerSeconds);

        if (state.timerRunning)
        {
            this.resumeTimer();
        }
    }

    /**
     * onSyncPeriodChanged
     *
     * Called when polling detects a period change from another window.
     *
     * @param {Object} state
     * return null
     */
    onSyncPeriodChanged(state)
    {
        $('#live-main').attr('data-period', state.period);
        this.setTimerDisplay(state.timerSeconds);

        $('#game-controls').removeClass();

        if (state.period == 'half')
        {
            clearInterval(this.timer);
            $('#game-controls').addClass('half row text-center mb-3');
        }
        else if (state.period == '2')
        {
            clearInterval(this.timer);
            if (state.timerRunning)
            {
                this.resumeTimer();
            }
            $('#game-controls').addClass('second row text-center mb-3');
        }
        else
        {
            clearInterval(this.timer);
            if (state.timerRunning)
            {
                this.resumeTimer();
            }
            $('#game-controls').addClass('first row text-center mb-3');
        }
    }

    /**
     * clickStartSecondHalf
     *
     * @param {Object} event
     * return null
     */
    clickStartSecondHalf(event)
    {
        $('.alert').remove();

        let time = $('#time').val();

        if (!time)
        {
            $('#live-main').before('<p class="alert alert-danger mt-2">You must enter a 2nd half time.</p>');
            return;
        }
        if (!/^\d\d$/.test(time))
        {
            $('#live-main').before('<p class="alert alert-danger mt-2">Time must be in minutes.</p>');
            return;
        }

        $('#game-controls').removeClass();

        // hide 2nd half form
        // show the end game button
        $('#game-controls').addClass('second row text-center mb-3');

        // Set the timer time and resume
        this.setTimerDisplay(parseInt(time) * 60);
        this.resumeTimer();

        // Save live state: period 2, timer running, offset = entered minutes
        this.saveLiveState('2', true, parseInt(time) * 60);
    }

    /**
     * clickPauseTimer
     *
     * @param {Object} event
     * return null
     */
    clickPauseTimer(event)
    {
        if ($('#timer').hasClass('paused'))
        {
            $('#timer').removeClass('paused');
            $('#live-main').removeClass('paused');
            this.resumeTimer();

            // Save live state: timer resumed
            this.saveLiveState($('#live-main').attr('data-period'), true);
        }
        else
        {
            $('#timer').addClass('paused');
            $('#live-main').addClass('paused');
            clearInterval(this.timer);

            // Save live state: timer paused
            this.saveLiveState($('#live-main').attr('data-period'), false);
        }
    }

    /**
     * clickEndHalf
     *
     * Stop the timer and set the period to 'half'.
     *
     * @param {Object} event
     * return null
     */
    clickEndHalf(event)
    {
        // stop timer
        clearInterval(this.timer);

        $('#game-controls').removeClass();

        // hide half button
        // show half time form
        $('#game-controls').addClass('half row text-center mb-3');

        // Save live state: halftime, timer stopped
        this.saveLiveState('half', false);
    }

    /**
     * clickEndGame
     *
     * Save the final score.
     *
     * @param {Object} event
     * return null
     */
    clickEndGame(event)
    {
        // Save the final score
        $.ajax({
            url  : $('#live-main').attr('data-end-game-route'),
            type : 'POST',
            data : {
                resultId  : $('#live-main').attr('data-result-id'),
                time      : $('#timer > span').text(),
                homeScore : $('#home-score > .score').text(),
                awayScore : $('#away-score > .score').text(),
            },
        }).done((data) => {
            this.gameStarted = false;
            window.location.href = data.data.redirect;
        }).fail(() => {
            $('#live-main').before('<p class="alert alert-danger mt-2">Something went wrong, couldn\'t save game.</p>');
        });
    }

    /**
     * startGame
     *
     * Hide the start game button, show the scores and timer.
     *
     * return null
     */
    startGame()
    {
        // set ready class allows events to be chosen
        $('#live-main').addClass('ready');

        this.gameStarted = true;

        $('#game-controls').removeClass();

        // hide start game button
        // show the scores
        // show the timer
        $('#game-controls').addClass('first row text-center mb-3');
    }

    /**
     * confirmExit
     *
     * If the game has already started, will prevent accidental exiting
     *
     * @param {Object} event
     * return null
     */
    confirmExit(event)
    {
        // If the game is still going on, make sure we realy want to exit
        if (this.gameStarted)
        {
            event.preventDefault();
            event.returnValue = true;
        }
    }

    /**
     * startTimer
     *
     * @param {number} offsetSeconds - seconds already elapsed (default 0)
     * return null
     */
    startTimer(offsetSeconds)
    {
        offsetSeconds = offsetSeconds || 0;

        let start = new Date(Date.now() - (offsetSeconds * 1000));

        this.timer = setInterval(() => {
            let now  = new Date();
            let diff = now.getTime() - start.getTime();

            let min = '00';
            let sec = '00';

            if (diff > 60000)
            {
                min = Math.floor(diff / 1000 / 60);
                min = min.toString().pad('0', 2);
            }
            sec = Math.floor((diff / 1000) % 60);
            sec = sec.toString().pad('0', 2);

            // Update the visual timer
            $('#timer > span').empty().append(min + ':' + sec);
        }, 1000);
    }

    /**
     * resumeTimer
     *
     * return null
     */
    resumeTimer()
    {
        let existing = $('#timer > span').text();
        existing     = existing.split(':');

        //  1000 = 1 second
        // 60000 = 1 minute

        let start = new Date(Date.now() - ((existing[0] * 60000) + (existing[1] * 1000)));

        this.timer = setInterval(() => {
            let now  = new Date();
            let diff = now.getTime() - start.getTime();

            let min = '00';
            let sec = '00';

            if (diff > 60000)
            {
                min = Math.floor(diff / 1000 / 60);
                min = min.toString().pad('0', 2);
            }
            sec = Math.floor((diff / 1000) % 60);
            sec = sec.toString().pad('0', 2);

            // Update the visual timer
            $('#timer > span').empty().append(min + ':' + sec);
        }, 1000);
    }
}
