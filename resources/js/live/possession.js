import Live from './live';

export default class LivePossession extends Live
{
    constructor()
    {
        // Call Live constructor
        super();

        this.pollTimer = null;

        // Close/Leave page
        addEventListener('beforeunload', (e) => {
            this.confirmExit(e);
        });

        // Resume an existing game if available
        let savedResultId = localStorage.getItem('resultId');
        if (savedResultId !== null && savedResultId == $('#live-main').attr('data-result-id'))
        {
            this.resumeExistingGame();
        }

        // Click Possession
        $('.main-content').on('click', 'label', (e) => {
            this.clickPossession(e);
        });
    }

    /**
     * clickStartGame
     *
     * Validate that we have a formation and 1+ starters, then start timer and save starters.
     *
     * @param {Object} event
     * return null
     */
    clickStartGame(event)
    {
        let resultId      = $('#live-main').attr('data-result-id');
        let savedResultId = localStorage.getItem('resultId');
        let period        = localStorage.getItem('period');

        // bail if we already have a result id saved
        if (resultId == savedResultId && period != null)
        {
            // this prevents user from clicking start, before we had a chance to resume the game for them
            return;
        }

        // Save the result and period, and reset timer
        localStorage.setItem('resultId', resultId);
        localStorage.setItem('period', '1');
        localStorage.removeItem('time');

        // set ready class allows events to be chosen
        $('#live-main').addClass('ready');

        $('#game-controls').removeClass();

        // hide start game button
        // show the scores
        // show the timer
        $('#game-controls').addClass('first row text-center mb-3');

        this.startTimer();
        this.startPolling();
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
            // resumeTimer calls resumePolling
        }
        else
        {
            $('#timer').addClass('paused');
            $('#live-main').addClass('paused');
            clearInterval(this.timer);
            clearInterval(this.pollTimer);
            this.pollTimer = null;
        }
    }

    /**
     * clickEndHalf
     *
     * @param {Object} event
     * return null
     */
    clickEndHalf(event)
    {
        super.clickEndHalf(event);

        // stop polling
        clearInterval(this.pollTimer);
        this.pollTimer = null;
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
        // Save fulltime event
        $.ajax({
            url  : $('#live-main').attr('data-create-event-route'),
            type : 'POST',
            data : {
                result_id : $('#live-main').attr('data-result-id'),
                time      : $('#timer > span').text(),
                event_id  : $('#live-main').attr('data-fulltime-event-id'),
            }
        }).fail(() => {
            $('#live-main').before('<p class="alert alert-danger mt-2">Something went wrong, couldn\'t save fulltime.</p>');
            return;
        });

        // Set the result as done
        $.ajax({
            url  : $('#live-main').attr('data-result-update-route'),
            type : 'POST',
            data : {
                live   : 1,
                status : 'D',
            },
        }).done((data) => {
            localStorage.removeItem('resultId');
            localStorage.removeItem('time');
            localStorage.removeItem('formationId');
            localStorage.removeItem('starters');
            localStorage.removeItem('period');

            window.location.href = $('#live-main').attr('data-show-route');
        }).fail(() => {
            $('#live-main').before('<p class="alert alert-danger mt-2">Something went wrong, couldn\'t save game.</p>');
        });
    }

    /**
     * startTimer
     *
     * return null
     */
    startTimer()
    {
        super.startTimer();
        this.startPolling();
    }

    /**
     * resumeTimer
     *
     * return null
     */
    resumeTimer()
    {
        super.resumeTimer();
        this.startPolling();
    }

    /**
     * clickPossession
     *
     * Save the possession change events.
     *
     * @param {Object} event
     * return null
     */
    clickPossession(event)
    {
        // Don't do anything if the game hasn't started yet
        if (!$('#live-main').hasClass('ready'))
        {
            event.preventDefault();
            event.stopPropagation();
            return;
        }

        let $label = $(event.currentTarget);
        let $input = $label.prev('input');

        // Don't double gain/lose possession
        if ($input.is(':checked')) {
            console.log('already checked');
            return;
        }

        // Save the event
        let resultId = $('#live-main').attr('data-result-id');
        let time     = $('#timer > span').text();
        let eventId  = $input.attr('data-event-id');

        $.ajax({
            url  : $('#live-main').attr('data-create-event-route'),
            type : 'POST',
            data : {
                result_id : resultId,
                time      : time,
                event_id  : eventId,
            }
        }).done((data) => {
            console.log(data);
        }).fail(() => {
            $('#live-main').before('<p class="alert alert-danger mt-2">Something went wrong, couldn\'t save event.</p>');
        });
    }

    /**
     * startPolling
     *
     * Do an ajax call every 25 secs to get the current possesion percentages
     * and update the progress bar.
     *
     * return null
     */
    startPolling()
    {
        this.pollTimer = setInterval(() => {
            let time = $('#timer > span').text();

            $.ajax({
                url: $('#live-main').attr('data-possession-route'),
                timeout: 2000,
                data : { time: time }
            }).done((data) => {
                let total = data.data.home.seconds + data.data.away.seconds;

                let percentage = 0;

                if (total > 0) {
                    percentage = (data.data.home.seconds / total) * 100;
                }

                $('#possession-bar').find('.progress-bar').css('width', percentage + '%');
            });
        }, 25000);
    }
}
