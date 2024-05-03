export default class Live
{
    constructor(formations, players, playersByPosition)
    {
        this.formations        = formations;
        this.players           = players;
        this.playersByPosition = playersByPosition;
        this.starters          = {};

        this.us   = $('#field').data('goodGuys');
        this.them = this.us == 'home' ? 'away' : 'home';

        this.timerStartDate;
        this.timer;

        this.drawer   = new FormationDrawer(players, playersByPosition);
        this.timeline = new EventTimeline('#game-timeline');

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

        // Resume an existing game if available
        let savedResultId = localStorage.getItem('resultId');
        if (savedResultId !== null && savedResultId == $('#field').data('resultId'))
        {
            this.resumeExistingGame();
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

        // Click End Game
        $('.main-content').on('click', '#end-game', (e) => {
            this.clickEndGame(e);
        });

        // Click Save Formation
        $('.main-content').on('click', '#submit-formation', (e) => {
            this.clickSaveFormation(e);
        });

        // Click Change Formation
        $('.main-content').on('click', '#current-formation > span.badge', (e) => {
            this.clickChangeFormation(e);
        });

        // Click Player Picker
        $('#field').on('click', '.position.empty .dropdown-menu.player > a.dropdown-item', (e) => {
            this.clickPlayerPicker(e);
        });

        // Click Remove Player
        $('#field').on('click', '.position button.btn-close', (e) => {
            this.clickRemovePlayer(e);
        });

        // Click Event Picker
        $('#field').on('click', '.position:not(.empty) .event-picker', (e) => {
            this.clickEventPicker(e);
        });

        // Click event
        $('#event-modal').on('click', 'button.btn', (e) => {
            this.clickEvent(e);
        });

        // Click goal against
        $('.main-content').on('click', '#game-controls .actions-against span', (e) => {
            this.clickEventAgainst(e);
        });

        // Click xg btn
        $('#additional-modal').on('click', '.xg > input.btn-check', (e) => {
            this.clickXgButton(e);
        });

        // Click save event
        $('#additional-modal').on('click', '#additional-save', (e) => {
            this.clickSaveEvent(e);
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
        let resultId      = $('#field').data('resultId');
        let savedResultId = localStorage.getItem('resultId');
        let period        = localStorage.getItem('period');

        // bail if we already have a result id saved
        if (resultId == savedResultId && period != null)
        {
            // this prevents user from clicking start, before we had a chance to resume the game for them
            return;
        }

        // Make sure we have a formation
        if (!$('#formation').val())
        {
            $('#field').before('<p class="alert alert-danger mt-2">Choose a formation first.</p>');
            return;
        }
        if (localStorage.getItem('formationId') === null)
        {
            $('#field').before('<p class="alert alert-danger mt-2">Choose a formation first.</p>');
            return;
        }

        // Make sure we have at least 1 starter
        if (Object.keys(this.starters).length < 1)
        {
            $('#field').before('<p class="alert alert-danger mt-2">Must have at least one starter.</p>');
            return;
        }
        if (localStorage.getItem('starters') === null)
        {
            $('#field').before('<p class="alert alert-danger mt-2">Must have at least one starter.</p>');
            return;
        }

        $('.alert').remove();

        // Save the result and period, and reset timer
        localStorage.setItem('resultId', resultId);
        localStorage.setItem('period', '1');
        localStorage.removeItem('time');

        // Save the starters and formation
        $.ajax({
            url  : $('#field').data('startGameRoute'),
            type : 'POST',
            data : {
                resultId    : resultId,
                starters    : this.starters,
                formationId : localStorage.getItem('formationId'),
            },
        }).done((data) => {
            this.startGame();
            this.startTimer();
        }).fail(() => {
            $('#field').before('<p class="alert alert-danger mt-2">Something went wrong, couldn\'t save starting lineup.</p>');
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
            $('#field').before('<p class="alert alert-danger mt-2">You must enter a 2nd half time.</p>');
            return;
        }
        if (!/^\d\d$/.test(time))
        {
            $('#field').before('<p class="alert alert-danger mt-2">Time must be in minutes.</p>');
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
            url  : $('#field').data('endGameRoute'),
            type : 'POST',
            data : {
                resultId  : $('#field').data('resultId'),
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

            window.location.href = data.redirect;
        }).fail(() => {
            $('#field').before('<p class="alert alert-danger mt-2">Something went wrong, couldn\'t save game.</p>');
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
        $('#field').addClass('ready');

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

    /**
     * clickSaveFormation
     *
     * Save the formation in localStorage, then draw the formation.
     *
     * @param {Object} event
     * return null
     */
    clickSaveFormation(event)
    {
        event.preventDefault();

        let selectedFormationId = document.getElementById('formation').value;

        let formation     = this.formations[selectedFormationId];
        let formationName = this.formations[selectedFormationId].name;

        let dashed = formationName.split('').join('-');

        $('#current-formation > span.badge').text(dashed);

        $('#game-controls').removeClass();

        // hide the formation select box
        // show the current formation badge
        $('#game-controls').addClass('formation row text-center mb-3');

        // draw the formation on the field
        this.drawer.drawFormation(formation);

        // Save this formation
        localStorage.setItem('formationId', selectedFormationId);

        $('.alert').remove();
    }

    /**
     * clickChangeFormation
     *
     * Remove the currently drawn formation and saved formation.
     *
     * @param {Object} event
     * return null
     */
    clickChangeFormation(event)
    {
        // can't change the formation after game has started
        if ($('#field').hasClass('ready'))
        {
            return;
        }

        $('#game-controls').removeClass();

        // Show the formation select
        // hide the current formation badge
        $('#game-controls').addClass('initial row text-center mb-3');

        // empty the current formation
        $('#current-formation > span.badge').empty();

        // remove the formation rows/cols
        $('#field > .row').remove();

        // remove the formation classes
        $('#field').removeClass();
        $('#field').addClass('mx-auto text-center position-relative');

        // removed saved formationId
        localStorage.removeItem('formationId');

        // reset the starters
        this.starters = {};
        localStorage.setItem('starters', JSON.stringify(this.starters));
    }

    /**
     * clickPlayerPicker
     *
     * Save the player to the position in formation, and update player dropdowns.
     *
     * @param {Object} event
     * return null
     */
    clickPlayerPicker(event)
    {
        event.preventDefault();

        let $anchor   = $(event.target);
        let $position = $anchor.parents('.position').first();
        let playerId  = $anchor.data('playerId');

        this.drawer.addPlayer($position, playerId);

        this.starters[playerId] = $position.data('playerPosition');

        // Save this as a sub_in event
        if ($('#field').hasClass('ready'))
        {
            $.ajax({
                url  : $('#field').data('createEventRoute'),
                type : 'POST',
                data : {
                    result_id  : $('#field').data('resultId'),
                    player_id  : playerId,
                    time       : $('#timer > span').text(),
                    event_id   : '3',
                    additional : $position.data('playerPosition')
                },
            }).done((data) => {
                // do nothing on success
            }).fail(() => {
                $('#field').before('<p class="alert alert-danger mt-2">Something went wrong, couldn\'t save sub in event.</p>');
            });
        }

        // Save the starters
        localStorage.setItem('starters', JSON.stringify(this.starters));

        // remove the starters from all the player dropdowns
        this.updatePlayerDropdowns();
    }

    /**
     * clickRemovePlayer
     *
     * Remove a player from the formation, reset the starters, and player dropdowns.
     *
     * @param {Object} event
     * return null
     */
    clickRemovePlayer(event)
    {
        let $anchor   = $(event.target);
        let $position = $anchor.parents('.position').first();
        let playerId  = $position.find('img').data('playerId');

        // remove player photo
        $position.find('img').remove();

        // remove play name
        $position.find('span.name').remove();

        $position.addClass('empty');

        delete this.starters[playerId];

        // Save this as a sub_out event
        if ($('#field').hasClass('ready'))
        {
            $.ajax({
                url  : $('#field').data('createEventRoute'),
                type : 'POST',
                data : {
                    result_id  : $('#field').data('resultId'),
                    player_id  : playerId,
                    time       : $('#timer > span').text(),
                    event_id   : '4'
                },
            }).done((data) => {
                // do nothing on success
            }).fail(() => {
                $('#field').before('<p class="alert alert-danger mt-2">Something went wrong, couldn\'t save sub out event.</p>');
            });
        }

        // Save the starters
        localStorage.setItem('starters', JSON.stringify(this.starters));

        // remove the starters from all the player dropdowns
        this.updatePlayerDropdowns();
    }

    /**
     * clickEventPicker
     *
     * Show the event modal.
     *
     * @param {Object} event
     * return null
     */
    clickEventPicker(event)
    {
        if (!$('#field').hasClass('ready'))
        {
            return;
        }

        let $eventPickerDiv = $(event.currentTarget);

        let playerId = $eventPickerDiv.find('img').data('playerId');

        $('#event-modal').data('playerId', playerId)
            .modal('show');
    }

    /**
     * updatePlayerDropdowns
     *
     * Remove all starters from the player picker dropdowns, so we don't start same player more than once.
     * Remove all non starters from the assist user select.
     *
     * return null
     */
    updatePlayerDropdowns()
    {
        // show all players for all dropdowns
        $('.dropdown-menu.player a').show();

        // update all dropdowns, hiding the starters
        $('.dropdown-menu.player a').each((index, el) => {
            for (let id in this.starters)
            {
                if ($(el).data('playerId') == id)
                {
                    $(el).hide();
                }
            }
        });

        // show all assist users
        $('#additional-modal #player_id > option').prop('disabled', false);

        // update the assist user select, hiding the non-starters
        $('#additional-modal #player_id > option').each((index, option) => {
            if (option.value)
            {
                if (!(option.value in this.starters))
                {
                    option.disabled = true;
                }
            }
        });
    }

    /**
     * clickEvent
     *
     * @param {Object} event
     * return null
     */
    clickEvent(event)
    {
        let $eventButton = $(event.target);

        let resultId = $('#field').data('resultId');
        let playerId = $('#event-modal').data('playerId');
        let time     = $('#timer > span').text();
        let eventId  = $eventButton.data('eventId');

        // reset the additional form
        document.getElementById('additional-form').reset();
        $('input[name=xg] + label').css('opacity', 1);
        $('#shooting-options').hide();
        
        // shooting event?
        if ($eventButton.hasClass('shooting'))
        {
            $('#shooting-options').show();
        }

        // update additional modal title
        let eventText = $eventButton.contents().not($eventButton.children()).text();
        $('#additional-modal .modal-title').text(eventText);

        // show the addition info modal, and pass data to it
        $('#additional-modal').data('resultId', resultId)
            .data('playerId', playerId)
            .data('time', time)
            .data('eventId', eventId)
            .modal('show');
    }

    /**
     * clickEventAgainst
     *
     * @param {Object} event
     * return null
     */
    clickEventAgainst(event)
    {
        let $eventSpan = $(event.target);

        let resultId = $('#field').data('resultId');
        let time     = $('#timer > span').text();
        let eventId  = $eventSpan.data('eventId');

        // reset the additional form
        document.getElementById('additional-form').reset();
        $('input[name=xg] + label').css('opacity', 1);
        $('#shooting-options').hide();
        
        let eventText = '';

        // update additional modal title
        if ($eventSpan.hasClass('goal_against'))
        {
            eventText = 'Goal Against';
        }
        if ($eventSpan.hasClass('shot_against'))
        {
            eventText = 'Shot Against (Off Target)';
        }
        if ($eventSpan.hasClass('corner_kick_against'))
        {
            eventText = 'Corner Kick Against';
        }

        $('#additional-modal .modal-title').text(eventText);

        // show the addition info modal, and pass data to it
        $('#additional-modal').data('resultId', resultId)
            .data('time', time)
            .data('eventId', eventId)
            .modal('show');
    }

    /**
     * clickXgButton
     *
     * @param {Object} event
     * return null
     */
    clickXgButton(event)
    {
        let $selectedXg = $(event.target);

        $('input[name=xg] + label').css('opacity', 0.2);

        $selectedXg.next('label').css('opacity', 1);
    }

    /**
     * clickSaveEvent
     *
     * @param {Object} event
     * return null
     */
    clickSaveEvent(event)
    {
        event.preventDefault();

        let additional = null;

        if ($('#additional-modal #player_id').val())
        {
            additional = $('#additional-modal #player_id').val();
        }

        $.ajax({
            url  : $('#field').data('createEventRoute'),
            type : 'POST',
            data : {
                result_id  : $('#additional-modal').data('resultId'),
                player_id  : $('#additional-modal').data('playerId'),
                time       : $('#additional-modal').data('time'),
                event_id   : $('#additional-modal').data('eventId'),
                additional : additional,
                pk_fk      : $('#additional-modal input[name=pk_fk]:checked').val(),
                xg         : $('input[name=xg]:checked').val(),
                notes      : $('#notes').val(),
            },
        }).done((data) => {
            // close both modals
            $('#event-modal').modal('hide');
            $('#additional-modal').modal('hide');

            // Update Summary, Events and Player stats
            this.updateSummaryEventPlayerStats(data.data);
        }).fail(() => {
            $('#field').before('<p class="alert alert-danger mt-2">Something went wrong, couldn\'t save event.</p>');
        });
    }

    /**
     * updateSummaryEventPlayerStats
     *
     * Updates the Summary, Events, Player stat areas.  Called after a new event has been added.
     *
     * @param {Object} data
     * return null
     */
    updateSummaryEventPlayerStats(data)
    {
        let eventName = data.event_name;

        // Hide the no stats yet message, show the timeline
        $('#no-events-yet').hide();
        $('#game-timeline').show();

        // us events
        if (eventName == 'goal' || eventName == 'penalty_goal' || eventName == 'free_kick_goal')
        {
            // update the score
            $('#' + this.us + '-score > .score').text(parseInt($('#' + this.us + '-score > .score').text()) + 1);

            // Summary
            $('#game-goals-' + this.us).text(parseInt($('#game-goals-' + this.us).text()) + 1);
            $('#game-shots-' + this.us).text(parseInt($('#game-shots-' + this.us).text()) + 1);
            $('#game-shots-on-' + this.us).text(parseInt($('#game-shots-on-' + this.us).text()) + 1);

            // Events
            this.timeline.addEvent(data, this.us);

            // Players
            let pSelector = '#players-pane tr#player-' + data.player_id + ' td.goals';
            $(pSelector).text(parseInt($(pSelector).text()) + 1);

            $('#players-pane table').DataTable().rows().invalidate().draw();
        }
        if (eventName == 'shot_on_target' || eventName == 'penalty_on_target' || eventName == 'free_kick_on_target')
        {
            $('#game-shots-' + this.us).text(parseInt($('#game-shots-' + this.us).text()) + 1);
            $('#game-shots-on-' + this.us).text(parseInt($('#game-shots-on-' + this.us).text()) + 1);

            this.timeline.addEvent(data, this.us);

            let pSelector = '#players-pane tr#player-' + data.player_id + ' td.shots';
            $(pSelector).text(parseInt($(pSelector).text()) + 1);

            $('#players-pane table').DataTable().rows().invalidate().draw();
        }
        if (eventName == 'shot_off_target' || eventName == 'penalty_off_target' || eventName == 'free_kick_off_target')
        {
            $('#game-shots-' + this.us).text(parseInt($('#game-shots-' + this.us).text()) + 1);
            $('#game-shots-off-' + this.us).text(parseInt($('#game-shots-off-' + this.us).text()) + 1);

            this.timeline.addEvent(data, this.us);

            let pSelector = '#players-pane tr#player-' + data.player_id + ' td.shots';
            $(pSelector).text(parseInt($(pSelector).text()) + 1);

            $('#players-pane table').DataTable().rows().invalidate().draw();
        }
        if (eventName == 'corner_kick')
        {
            $('#game-corners-' + this.us).text(parseInt($('#game-corners-' + this.us).text()) + 1);

            this.timeline.addEvent(data, this.us);
        }
        if (eventName == 'foul')
        {
            $('#game-fouls-' + this.us).text(parseInt($('#game-fouls-' + this.us).text()) + 1);

            this.timeline.addEvent(data, this.us);
        }
        if (eventName == 'save')
        {
            $('#game-shots-' + this.us).text(parseInt($('#game-shots-' + this.us).text()) + 1);
            $('#game-shots-on-' + this.us).text(parseInt($('#game-shots-on-' + this.us).text()) + 1);

            this.timeline.addEvent(data, this.us);
        }
        if (eventName == 'tackle_won')
        {
            this.timeline.addEvent(data, this.us);

            let pSelector = '#players-pane tr#player-' + data.player_id + ' td.tackles';
            $(pSelector).text(parseInt($(pSelector).text()) + 1);

            $('#players-pane table').DataTable().rows().invalidate().draw();
        }
        if (eventName == 'tackle_lost')
        {
            this.timeline.addEvent(data, this.us);

            let pSelector = '#players-pane tr#player-' + data.player_id + ' td.tackles';
            $(pSelector).text(parseInt($(pSelector).text()) + 1);

            $('#players-pane table').DataTable().rows().invalidate().draw();
        }
        if (eventName == 'offsides')
        {
            this.timeline.addEvent(data, this.us);
        }
        if (eventName == 'yellow_card')
        {
            this.timeline.addEvent(data, this.us);
        }
        if (eventName == 'red_card')
        {
            this.timeline.addEvent(data, this.us);
        }

        // them events
        if (eventName == 'goal_against')
        {
            $('#' + this.them + '-score > .score').text(parseInt($('#' + this.them + '-score > .score').text()) + 1);

            $('#game-goals-' + this.them).text(parseInt($('#game-goals-' + this.them).text()) + 1)
            $('#game-shots-' + this.them).text(parseInt($('#game-shots-' + this.them).text()) + 1)
            $('#game-shots-on-' + this.them).text(parseInt($('#game-shots-on-' + this.them).text()) + 1)

            this.timeline.addEvent(data, this.them);
        }
        if (eventName == 'shot_against')
        {
            $('#game-shots-' + this.them).text(parseInt($('#game-shots-' + this.them).text()) + 1)
            $('#game-shots-off-' + this.them).text(parseInt($('#game-shots-off-' + this.them).text()) + 1)

            this.timeline.addEvent(data, this.them);
        }
        if (eventName == 'corner_kick_against')
        {
            $('#game-corners-' + this.them).text(parseInt($('#game-corners-' + this.them).text()) + 1)

            this.timeline.addEvent(data, this.them);
        }
        if (eventName == 'fouled')
        {
            $('#game-fouls-' + this.them).text(parseInt($('#game-fouls-' + this.them).text()) + 1)

            this.timeline.addEvent(data, this.them);
        }

        this.updateSummaryProgressBars();
    }

    /**
     * clickEvent
     *
     * return null
     */
    updateSummaryProgressBars()
    {
        $('#summary-pane > .progress').each((index, progress) => {
            let $parent = $(progress).prev();

            let homeCount  = parseInt($parent.find('div').first().text());
            let awayCount  = parseInt($parent.find('div').eq(2).text());
            let totalCount = homeCount + awayCount;
            let percentage = (homeCount / totalCount) * 100;

            $(progress).find('.progress-bar').css('width', percentage + '%');
        });
    }

    /**
     * addExistingEvents
     *
     * When resuming an existing game, will add the events we got from php to the 
     * summary, events and players area on screen.
     *
     * @parem {object} events
     * return null
     */
    addExistingEvents(events)
    {
        for (let [i, data] of Object.entries(events))
        {
            this.updateSummaryEventPlayerStats(data);
        }
    }

    resumeExistingGame()
    {
        let savedStarters    = JSON.parse(localStorage.getItem('starters'));
        let savedFormationId = localStorage.getItem('formationId');
        let savedPeriod      = localStorage.getItem('period');
        let savedTime        = localStorage.getItem('time');

        // Formation
        if (savedFormationId !== null)
        {
            // select the saved formation from the dropdown
            $('#formation').val(savedFormationId);

            // save and draw the formation
            this.clickSaveFormation(new Event('click'));
        }

        // Starters
        if (savedStarters !== null)
        {
            this.starters = savedStarters;

            let clonedStarters = JSON.parse(JSON.stringify(savedStarters));

            this.drawer.addPlayerStarters(clonedStarters);
        }

        // Game/Timer
        if (savedPeriod !== null && savedTime !== null)
        {
            this.startGame();
            $('#timer > span').empty().append(savedTime);

            $('#game-controls').removeClass();

            // half time
            if (savedPeriod == 'half')
            {
                clearInterval(this.timer);
                $('#game-controls').addClass('half row text-center mb-3');
            }
            // 2nd half
            else if (savedPeriod == '2')
            {
                this.resumeTimer();
                $('#game-controls').addClass('second row text-center mb-3');
            }
            // 1st half
            else
            {
                this.resumeTimer();
                $('#game-controls').addClass('first row text-center mb-3');
            }
        }
    }
}
