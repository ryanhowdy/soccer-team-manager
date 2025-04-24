export default class Live
{
    constructor()
    {
        this.us   = $('#live-main').attr('data-good-guys');
        this.them = this.us == 'home' ? 'away' : 'home';

        this.timer;

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

        // Do we have some saved game info?
        let savedResultId = localStorage.getItem('resultId');
        if (savedResultId !== null)
        {
            // Resume this game
            if (savedResultId == $('#live-main').attr('data-result-id'))
            {
                this.resumeExistingGame();
            }
            // Delete the saved game info, it was old from another game perhaps?
            else
            {
                localStorage.removeItem('resultId');
                localStorage.removeItem('time');
                localStorage.removeItem('formationId');
                localStorage.removeItem('starters');
                localStorage.removeItem('period');
            }
        }

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

        // Save the 2nd period
        localStorage.setItem('period', '2');

        $('#game-controls').removeClass();

        // hide 2nd half form
        // show the end game button
        $('#game-controls').addClass('second row text-center mb-3');

        // Set the timer time
        $('#timer > span').empty().append(time + ':00');
        this.resumeTimer();
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
        }
        else
        {
            $('#timer').addClass('paused');
            $('#live-main').addClass('paused');
            clearInterval(this.timer);
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

        // Save updated period
        localStorage.setItem('period', 'half');

        $('#game-controls').removeClass();

        // hide half button
        // show half time form
        $('#game-controls').addClass('half row text-center mb-3');
    }

    /**
     * clickEndGame
     *
     * Save the final score, reset all the localStorage data.
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
            localStorage.removeItem('resultId');
            localStorage.removeItem('time');
            localStorage.removeItem('formationId');
            localStorage.removeItem('starters');
            localStorage.removeItem('period');

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
        if (localStorage.getItem('resultId') !== null)
        {
            event.preventDefault();
            event.returnValue = true;
        }
    }

    /**
     * startTimer
     *
     * return null
     */
    startTimer()
    {
        let start = new Date();

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

            // Save the time
            localStorage.setItem('time', min + ':' + sec);

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

            // Save the time
            localStorage.setItem('time', min + ':' + sec);

            // Update the visual timer
            $('#timer > span').empty().append(min + ':' + sec);
        }, 1000);
    }
}

