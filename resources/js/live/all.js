import Live from './live';

export default class LiveAll extends Live
{
    constructor(formations, players, playersByPosition, liveState)
    {
        // Call Live constructor
        super();

        this.formations        = formations;
        this.players           = players;
        this.playersByPosition = playersByPosition;
        this.starters          = {};
        this.processedEventIds = new Set();

        this.drawer   = new FormationDrawer(players, playersByPosition);
        this.timeline = new EventTimeline('#game-timeline');

        // Close/Leave page
        addEventListener('beforeunload', (e) => {
            this.confirmExit(e);
        });

        // Fit the controls and field to the screen on phones, again whenever
        // the screen or the controls (start, end half, 2nd half...) change size
        this.fitField();
        addEventListener('resize', () => this.fitField());
        new ResizeObserver(() => this.fitField()).observe(document.getElementById('game-controls'));

        // Resume an existing game from server state
        if (liveState && liveState.started)
        {
            this.resumeExistingGame(liveState);
        }

        // Click Save Formation
        $('.main-content').on('click', '#submit-formation', (e) => {
            this.clickSaveFormation(e);
        });

        // Click Change Formation
        $('.main-content').on('click', '#current-formation > span.badge', (e) => {
            this.clickChangeFormation(e);
        });

        // Click Player Picker
        $('#live-main').on('click', '.position.empty .dropdown-menu.player > a.dropdown-item', (e) => {
            this.clickPlayerPicker(e);
        });

        // Click Remove Player
        $('#live-main').on('click', '.position button.btn-close', (e) => {
            this.clickRemovePlayer(e);
        });

        // Click Event Picker
        $('#live-main').on('click', '.position:not(.empty) .event-picker', (e) => {
            this.clickEventPicker(e);
        });

        // Click event
        $('#event-modal').on('click', 'button.btn', (e) => {
            this.clickEvent(e);
        });

        // Click goal against
        $('.main-content').on('click', '#game-controls .actions-against span.goal_against', (e) => {
            this.clickGoalAgainst(e);
        });

        // Click event against
        $('.main-content').on('click', '#game-controls .actions-against span.more_against', (e) => {
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
     * fitField
     *
     * On a phone, narrow the field (it keeps the pitch's proportions, so this
     * shortens it too) until the whole page down to the bottom of the field -
     * navbar, score, timer, buttons and pitch - fits the screen without
     * scrolling. It never grows past the width it
     * would have had, and stops shrinking at a usable minimum on very short
     * screens.
     *
     * return null
     */
    fitField()
    {
        let field = document.getElementById('live-main');

        field.style.width = '';

        if (!window.matchMedia('(max-width: 500px)').matches)
        {
            return;
        }

        // where the field starts on the page, so everything above it (navbar,
        // score, timer, buttons) is on screen without scrolling
        let fieldTop  = field.getBoundingClientRect().top + window.scrollY;
        let available = window.innerHeight - fieldTop - 8;
        let fitted    = Math.floor(available * 452 / 684);

        if (fitted < field.offsetWidth)
        {
            field.style.width = Math.max(fitted, 220) + 'px';
        }
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
        // bail if game already started
        if (this.gameStarted)
        {
            return;
        }

        // Make sure we have a formation
        if (!$('#formation').val())
        {
            $('#live-main').before('<p class="alert alert-danger mt-2">Choose a formation first.</p>');
            return;
        }
        if (!this.savedFormationId)
        {
            $('#live-main').before('<p class="alert alert-danger mt-2">Choose a formation first.</p>');
            return;
        }

        // Make sure we have at least 1 starter
        if (Object.keys(this.starters).length < 1)
        {
            $('#live-main').before('<p class="alert alert-danger mt-2">Must have at least one starter.</p>');
            return;
        }

        $('.alert').remove();

        let resultId = $('#live-main').attr('data-result-id');

        // Save the starters and formation
        $.ajax({
            url  : $('#live-main').attr('data-start-game-route'),
            type : 'POST',
            data : {
                resultId    : resultId,
                starters    : this.starters,
                formationId : this.savedFormationId,
            },
        }).done((data) => {
            this.startGame();
            this.startTimer();

            // Save live state: period 1, timer running, offset 0
            this.saveLiveState('1', true, 0);

            // Store period on the element for pause/unpause
            $('#live-main').attr('data-period', '1');
        }).fail(() => {
            $('#live-main').before('<p class="alert alert-danger mt-2">Something went wrong, couldn\'t save starting lineup.</p>');
        });
    }

    /**
     * clickSaveFormation
     *
     * Save the formation, then draw it.
     *
     * @param {Object} event
     * return null
     */
    clickSaveFormation(event)
    {
        event.preventDefault();

        let selectedFormationId = document.getElementById('formation').value;

        this.setCurrentFormation(selectedFormationId);
    }

    /**
     * clickChangeFormation
     *
     * Remove the currently drawn formation.
     *
     * @param {Object} event
     * return null
     */
    clickChangeFormation(event)
    {
        let state = 'initial';
        let period = $('#live-main').attr('data-period');

        if (period)
        {
            state = period == 'half' ? 'half'
                  : period == '2'    ? 'second'
                  : 'first';
        }

        $('#game-controls').removeClass();
        $('#game-controls').addClass(state + ' row text-center mb-3');

        // Show the formation select
        $('#formation-form').show();
        // hide the current formation badge
        $('#current-formation').hide();

        // empty the current formation
        $('#current-formation > span.badge').empty();

        // remove the formation rows/cols
        $('#live-main > .row').remove();

        // remove the formation classes
        let ready = $('#live-main').hasClass('ready') ? 'ready' : '';
        $('#live-main').removeClass();
        $('#live-main').addClass('field mx-auto text-center position-relative ' + ready);

        // removed saved formationId
        this.savedFormationId = null;

        if ($('#live-main').hasClass('ready'))
        {
            let resultId = $('#live-main').attr('data-result-id');
            let time     = $('#timer > span').text();

            // send an event that the current starters were subbed out
            for (let id in this.starters)
            {
                $.ajax({
                    url  : $('#live-main').attr('data-create-event-route'),
                    type : 'POST',
                    data : {
                        result_id  : resultId,
                        player_id  : id,
                        time       : time,
                        event_id   : '4'
                    },
                }).done((data) => {
                    // do nothing on success
                }).fail(() => {
                    $('#live-main').before('<p class="alert alert-danger mt-2">Something went wrong, couldn\'t save sub out event.</p>');
                });
            }
        }

        // reset the starters
        this.starters = {};
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
        let playerId  = $anchor.attr('data-player-id');

        this.drawer.addPlayer($position, playerId);

        this.starters[playerId] = $position.attr('data-player-position');

        // Save this as a sub_in event
        if ($('#live-main').hasClass('ready'))
        {
            $.ajax({
                url  : $('#live-main').attr('data-create-event-route'),
                type : 'POST',
                data : {
                    result_id  : $('#live-main').attr('data-result-id'),
                    player_id  : playerId,
                    time       : $('#timer > span').text(),
                    event_id   : '3',
                    additional : $position.attr('data-player-position')
                },
            }).done((data) => {
                // do nothing on success
            }).fail(() => {
                $('#live-main').before('<p class="alert alert-danger mt-2">Something went wrong, couldn\'t save sub in event.</p>');
            });
        }

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
        let playerId  = $position.find('img').attr('data-player-id');

        // remove the photo, the name and the jersey number badge
        $position.find('.event-picker').empty();

        $position.addClass('empty');

        delete this.starters[playerId];

        // Save this as a sub_out event
        if ($('#live-main').hasClass('ready'))
        {
            $.ajax({
                url  : $('#live-main').attr('data-create-event-route'),
                type : 'POST',
                data : {
                    result_id  : $('#live-main').attr('data-result-id'),
                    player_id  : playerId,
                    time       : $('#timer > span').text(),
                    event_id   : '4'
                },
            }).done((data) => {
                // do nothing on success
            }).fail(() => {
                $('#live-main').before('<p class="alert alert-danger mt-2">Something went wrong, couldn\'t save sub out event.</p>');
            });
        }

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
        if (!$('#live-main').hasClass('ready'))
        {
            return;
        }

        let $eventPickerDiv = $(event.currentTarget);

        let playerId = $eventPickerDiv.find('img').attr('data-player-id');

        let goodGuysTeamName = $('#game-controls .team-name.good-guys').text();

        // change all buttons to primary for (good guys)
        $('#event-modal button.btn-secondary')
            .removeClass('btn-secondary')
            .addClass('btn-primary-light');

        // Set header to the tapped player, so a mis-tap is obvious before
        // anything is saved
        let playerName = this.players[playerId] ? this.players[playerId].name : goodGuysTeamName;
        $('#event-modal .modal-header .modal-title > span').text(playerName);

        // Show all the event buttons
        $('#event-modal button').show();

        // show events modal and set player id and remove against flag
        $('#event-modal')
            .attr('data-player-id', playerId)
            .attr('data-against', 0)
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
                if ($(el).attr('data-player-id') == id)
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
        let $eventButton = $(event.target).closest('button');

        let resultId = $('#live-main').attr('data-result-id');
        let playerId = $('#event-modal').attr('data-player-id');
        let against  = $('#event-modal').attr('data-against');
        let time     = $('#timer > span').text();
        let eventId  = $eventButton.attr('data-event-id');

        // reset the additional form
        document.getElementById('additional-form').reset();
        $('input[name=xg] + label').css('opacity', 1);
        $('#pkfk-details').hide();
        $('#assist-details').hide();
        $('#xg-details').hide();

        // which additional form details this event uses
        let show = JSON.parse($eventButton.attr('data-show') || '[]');

        // don't show assist for bad guys
        if (against == "1")
        {
            show = show.filter((detail) => detail != 'assist');
        }

        let eventText = $eventButton.contents().not($eventButton.children()).text().trim();

        // Nothing to add (tackles, fouls, cards...), so save straight away
        // instead of opening the additional modal just to press Save
        if (show.length == 0)
        {
            let eventData = {
                result_id : resultId,
                against   : against,
                time      : time,
                event_id  : eventId,
            };

            // Against events have no player. Left out entirely (as the
            // additional modal does), since the event modal can still hold
            // the last player tapped.
            if (against != "1")
            {
                eventData.player_id = playerId;
            }

            this.saveEvent(eventData, eventText);

            return;
        }

        for (let i = 0; i < show.length; i++)
        {
            $('#' + show[i] + '-details').show();
        }

        // update additional modal title
        $('#additional-modal .modal-title').text(eventText);

        // show the addition info modal, and pass data to it
        $('#additional-modal').attr('data-result-id', resultId)
            .attr('data-player-id', playerId)
            .attr('data-against', against)
            .attr('data-time', time)
            .attr('data-event-id', eventId)
            .modal('show');
    }

    /**
     * clickGoalAgainst
     *
     * @param {Object} event
     * return null
     */
    clickGoalAgainst(event)
    {
        let $eventSpan = $(event.target);

        let resultId = $('#live-main').attr('data-result-id');
        let time     = $('#timer > span').text();
        let eventId  = $eventSpan.attr('data-event-id');

        // reset any data passed to the additional modal
        $('#additional-modal')
            .removeAttr('data-player-id')
            .removeAttr('data-against');

        // reset the additional form
        document.getElementById('additional-form').reset();
        $('input[name=xg] + label').css('opacity', 1);

        // show pk/fk and xg, hide assist
        $('#pkfk-details').show();
        $('#assist-details').hide();
        $('#xg-details').show();

        $('#additional-modal .modal-title').text('Goal Against');

        // show the addition info modal, and pass data to it
        $('#additional-modal')
            .attr('data-result-id', resultId)
            .attr('data-against', 1)
            .attr('data-time', time)
            .attr('data-event-id', eventId)
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
        let badGuysTeamName = $('#game-controls .team-name.bad-guys').text();

        // change all buttons to grey for (bad guys)
        $('#event-modal button.btn-primary-light')
            .removeClass('btn-primary-light')
            .addClass('btn-secondary');

        // Set header for (bad guys)
        $('#event-modal .modal-header .modal-title > span').text(badGuysTeamName);

        // hide a few events that don't make sense against
        $('#event-modal #goal').hide();
        $('#event-modal #save').hide();
        $('#event-modal #foul').hide();
        $('#event-modal #fouled').hide();

        // show events modal
        $('#event-modal')
            .attr('data-against', 1)
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

        this.saveEvent({
            result_id  : $('#additional-modal').attr('data-result-id'),
            player_id  : $('#additional-modal').attr('data-player-id'),
            against    : $('#additional-modal').attr('data-against'),
            time       : $('#additional-modal').attr('data-time'),
            event_id   : $('#additional-modal').attr('data-event-id'),
            additional : additional,
            pk_fk      : $('#additional-modal input[name=pk_fk]:checked').val(),
            xg         : $('input[name=xg]:checked').val(),
            notes      : $('#notes').val(),
        }, $('#additional-modal .modal-title').text());
    }

    /**
     * saveEvent
     *
     * Save an event, straight from the event modal or via the additional
     * modal, then confirm it with an undo toast.
     *
     * @param {Object} eventData
     * @param {String} label  what to call the event in the toast
     * return null
     */
    saveEvent(eventData, label)
    {
        $.ajax({
            url  : $('#live-main').attr('data-create-event-route'),
            type : 'POST',
            data : eventData,
        }).always(() => {
            // clear any data saved to either modal
            $('#event-modal')
                .removeAttr('data-player-id')
                .removeAttr('data-against');
            $('#additional-modal')
                .removeAttr('data-result-id')
                .removeAttr('data-player-id')
                .removeAttr('data-against')
                .removeAttr('data-time')
                .removeAttr('data-event-id');
            // close both modals
            $('#event-modal').modal('hide');
            $('#additional-modal').modal('hide');
        }).done((data) => {
            // Track this event so polling doesn't double-count it
            if (data.data.id)
            {
                this.processedEventIds.add(data.data.id);
            }
            // Update Summary, Events and Player stats
            this.updateSummaryEventPlayerStats(data.data);

            this.showUndoToast(data.data, label);
        }).fail(() => {
            $('#live-main').before('<p class="alert alert-danger mt-2">Something went wrong, couldn\'t save event.</p>');
        });
    }

    /**
     * showUndoToast
     *
     * Confirm a saved event, with a chance to take it back.
     *
     * @param {Object} data  the saved event
     * @param {String} label
     * return null
     */
    showUndoToast(data, label)
    {
        let who = data.against == 1
            ? $('#game-controls .team-name.bad-guys').text()
            : (this.players[data.player_id] ? this.players[data.player_id].name : '');

        let $toast = $('#event-toast');

        $toast.find('.event-toast-label').text(label);
        $toast.find('.event-toast-who').text(who);
        $toast.find('.event-toast-undo')
            .prop('disabled', false)
            .off('click')
            .on('click', () => this.undoEvent(data));

        bootstrap.Toast.getOrCreateInstance($toast[0]).show();
    }

    /**
     * undoEvent
     *
     * Delete a just-saved event and take it back out of the summary, events
     * and player stats.
     *
     * @param {Object} data  the saved event
     * return null
     */
    undoEvent(data)
    {
        let $toast = $('#event-toast');

        // stop a double tap deleting twice
        $toast.find('.event-toast-undo').prop('disabled', true);

        $.ajax({
            url  : $('#live-main').attr('data-destroy-event-route').replace('__EVENT__', data.id),
            type : 'POST',
        }).done(() => {
            this.updateSummaryEventPlayerStats(data, -1);

            bootstrap.Toast.getOrCreateInstance($toast[0]).hide();
        }).fail(() => {
            $toast.find('.event-toast-undo').prop('disabled', false);

            $('#live-main').before('<p class="alert alert-danger mt-2">Something went wrong, couldn\'t undo event.</p>');
        });
    }

    /**
     * updateSummaryEventPlayerStats
     *
     * Updates the Summary, Events, Player stat areas.  Called after a new event has been added,
     * and with a delta of -1 to take one back out when it's undone.
     *
     * @param {Object} data
     * @param {Number} delta  1 to add the event, -1 to remove it
     * return null
     */
    updateSummaryEventPlayerStats(data, delta = 1)
    {
        let eventName = data.event_name;
        let usOrThem  = data.against == 1 ? this.them : this.us;

        // Hide the no stats yet message, show the timeline
        $('#no-events-yet').hide();
        $('#game-timeline').show();

        if (eventName == 'goal' || eventName == 'penalty_goal' || eventName == 'free_kick_goal')
        {
            // update the score
            this.bump('#' + usOrThem + '-score > .score', delta);

            // Summary
            this.bump('#game-goals-' + usOrThem, delta);
            this.bump('#game-shots-' + usOrThem, delta);
            this.bump('#game-shots-on-' + usOrThem, delta);

            // Events
            this.updateTimeline(data, delta, usOrThem);

            // Players (only for us)
            if (data.against == 0)
            {
                let pSelector = '#players-pane tr#player-' + data.player_id + ' td';
                this.bump(pSelector + '.goals', delta);
                this.bump(pSelector + '.shots', delta);

                if (data.additional)
                {
                    let aSelector = '#players-pane tr#player-' + data.additional + ' td.assists';
                    this.bump(aSelector, delta);
                }
            }
        }
        if (eventName == 'shot_on_target' || eventName == 'penalty_on_target' || eventName == 'free_kick_on_target')
        {
            this.bump('#game-shots-' + usOrThem, delta);
            this.bump('#game-shots-on-' + usOrThem, delta);

            this.updateTimeline(data, delta, usOrThem);

            if (data.against == 0)
            {
                let pSelector = '#players-pane tr#player-' + data.player_id + ' td.shots';
                this.bump(pSelector, delta);
            }
        }
        if (eventName == 'shot_off_target' || eventName == 'penalty_off_target' || eventName == 'free_kick_off_target')
        {
            this.bump('#game-shots-' + usOrThem, delta);
            this.bump('#game-shots-off-' + usOrThem, delta);

            this.updateTimeline(data, delta, usOrThem);

            if (data.against == 0)
            {
                let pSelector = '#players-pane tr#player-' + data.player_id + ' td.shots';
                this.bump(pSelector, delta);
            }
        }
        if (eventName == 'corner_kick')
        {
            this.bump('#game-corners-' + usOrThem, delta);

            this.updateTimeline(data, delta, usOrThem);
        }
        if (eventName == 'foul')
        {
            this.bump('#game-fouls-' + this.us, delta);

            this.updateTimeline(data, delta, this.us);
        }
        if (eventName == 'fouled')
        {
            this.bump('#game-fouls-' + this.them, delta);

            this.updateTimeline(data, delta, this.them);
        }
        if (eventName == 'tackle_won')
        {
            this.updateTimeline(data, delta, usOrThem);

            if (data.against == 0)
            {
                let pSelector = '#players-pane tr#player-' + data.player_id + ' td.tackles';
                this.bump(pSelector, delta);
            }
        }
        if (eventName == 'tackle_lost')
        {
            this.updateTimeline(data, delta, usOrThem);
        }
        if (eventName == 'offsides')
        {
            this.bump('#game-offsides-' + usOrThem, delta);

            this.updateTimeline(data, delta, usOrThem);
        }
        if (eventName == 'yellow_card')
        {
            this.updateTimeline(data, delta, usOrThem);
        }
        if (eventName == 'red_card')
        {
            this.updateTimeline(data, delta, usOrThem);
        }
        if (eventName == 'save')
        {
            this.bump('#game-shots-' + this.them, delta);
            this.bump('#game-shots-on-' + this.them, delta);

            // yes this is right - shots count for them, but show up in timeline as us
            this.updateTimeline(data, delta, this.us);
        }

        // redraw the datatable so sorting works again
        $('#players-pane table').DataTable().rows().invalidate().draw();

        this.updateSummaryProgressBars();
    }

    /**
     * bump
     *
     * Add delta to the number shown in an element.
     *
     * @param {String} selector
     * @param {Number} delta
     * return null
     */
    bump(selector, delta)
    {
        $(selector).text(parseInt($(selector).text()) + delta);
    }

    /**
     * updateTimeline
     *
     * Add an event to the timeline, or remove it again on undo.
     *
     * @param {Object} data
     * @param {Number} delta
     * @param {String} side
     * return null
     */
    updateTimeline(data, delta, side)
    {
        if (delta > 0)
        {
            this.timeline.addEvent(data, side);
        }
        else
        {
            this.timeline.removeEvent(data.id);

            if ($('#game-timeline .event').length == 0)
            {
                $('#game-timeline').hide();
                $('#no-events-yet').show();
            }
        }
    }

    /**
     * updateSummaryProgressBars
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
            // Back to the even split the page starts with when there's
            // nothing to compare, e.g. after undoing the only shot (0 / 0
            // would be a NaN width, which the browser ignores, leaving the
            // bar where it was)
            let percentage = totalCount ? (homeCount / totalCount) * 100 : 50;

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
            if (data.id)
            {
                this.processedEventIds.add(data.id);
            }
            this.updateSummaryEventPlayerStats(data);
        }
    }

    /**
     * onSyncGameData
     *
     * Called on each poll to sync new events from the server
     * into the summary, events and player tabs.
     *
     * @param {Object} state
     * return null
     */
    onSyncGameData(state)
    {
        if (!state.resultEvents || !state.resultEvents.length)
        {
            return;
        }

        for (let i = 0; i < state.resultEvents.length; i++)
        {
            let data = state.resultEvents[i];

            if (this.processedEventIds.has(data.id))
            {
                continue;
            }

            this.processedEventIds.add(data.id);
            this.updateSummaryEventPlayerStats(data);
        }
    }

    resumeExistingGame(liveState)
    {
        let period      = liveState.period;
        let seconds     = liveState.timerSeconds;
        let formationId = liveState.formationId;
        let starters    = liveState.starters;

        // Formation
        if (formationId !== null)
        {
            // select the saved formation from the dropdown
            $('#formation').val(formationId);

            // save and draw the formation
            this.setCurrentFormation(formationId);
        }

        // Starters
        if (starters !== null && Object.keys(starters).length > 0)
        {
            this.starters = starters;

            let clonedStarters = JSON.parse(JSON.stringify(starters));

            this.drawer.addPlayerStarters(clonedStarters);
            this.updatePlayerDropdowns();
        }

        // Game/Timer
        if (period !== null && seconds !== null)
        {
            this.startGame();

            // Set the period on the element for pause/unpause
            $('#live-main').attr('data-period', period);

            this.setTimerDisplay(seconds);

            $('#game-controls').removeClass();

            // half time
            if (period == 'half')
            {
                clearInterval(this.timer);
                $('#game-controls').addClass('half row text-center mb-3');
            }
            // 2nd half
            else if (period == '2')
            {
                if (liveState.timerRunning)
                {
                    this.resumeTimer();
                }
                $('#game-controls').addClass('second row text-center mb-3');
            }
            // 1st half
            else
            {
                if (liveState.timerRunning)
                {
                    this.resumeTimer();
                }
                $('#game-controls').addClass('first row text-center mb-3');
            }
        }
    }
}
