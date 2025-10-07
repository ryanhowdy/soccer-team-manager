export default class Pk
{
    constructor()
    {
        this.curTeam    = null;
        this.curRound   = 1;
        this.maxRound   = 5;
        this.shootoutId = null;

        // Close/Leave page
        addEventListener('beforeunload', (e) => {
            this.confirmExit(e);
        });

        // Click who shoot first
        $('#home').on('click', (e) => {
            $('#start').parent('div').removeClass('d-none');
        });
        $('#away').on('click', (e) => {
            $('#start').parent('div').removeClass('d-none');
        });

        // Click Begin Shootout
        $('#start').on('click', (e) => {
            this.clickBeginShootout(e);
        });

        // Click on PK Event Button
        $('#controls button').on('click', (e) => {
            this.clickPkEventButton(e);
        });
    }

    /**
     * clickBeginShootout
     *
     * @param {Object} event
     * return null
     */
    clickBeginShootout(event)
    {
        // start the pk shootout in the db
        $.ajax({
            url  : $('#data').attr('data-start-route'),
            type : 'POST',
            data : {
                result_id     : $('#data').attr('data-result-id'),
                first_team_id : $('input[name="first"]:checked').val(),
            },
        }).done((data) => {
            this.shootoutId = data.data.id;

            $('#who-first').addClass('d-none');

            this.curTeam = $('input[name="first"]:checked').attr('data-who');

            $('#controls').removeClass('d-none');
            $('#controls > div.' + this.curTeam).removeClass('d-none');

            $('.round-' + this.curRound + ' > .' + this.curTeam + ' > span').removeClass('text-light');
            $('#round').text(this.curRound);
        }).fail(() => {
            $('#who-first').after('<p class="alert alert-danger mt-2">Something went wrong, couldn\'t start shootout.</p>');
        });
    }

    /**
     * clickPkEventButton
     *
     * @param {Object} event
     * return null
     */
    clickPkEventButton(event)
    {
        let $btn  = $(event.target);
        let evt   = $btn.attr('data-event');
        let evtId = $btn.attr('data-event-id');

        // make sure a player was picked first
        if ($btn.parent().hasClass('us')) {
            if ($('#player_id').val()) {
            } else {
                return;
            }
        }

        let ajaxData = {
            penalty_shootout_id : this.shootoutId,
            event_id            : evtId,
            against             : (this.curTeam == 'them' ? 1 : 0),
        };
        if (this.curTeam == 'us') {
            ajaxData.player_id = $('#player_id').val();
        }

        // save pk data to db
        $.ajax({
            url  : $('#data').attr('data-event-route'),
            type : 'POST',
            data : ajaxData,
        }).done((data) => {
            this.pkEvent($btn, evt, evtId);
        }).fail(() => {
            $('#controls').append('<p class="alert alert-danger mt-2">Something went wrong, couldn\'t save pk.</p>');
        });
    }

    /**
     * pkEvent
     *
     * Do all the things needed when a user has taken a shot.
     *
     * @param {jQuery Object} $btn
     * @param string          evt
     * @param string          evtId
     */
    pkEvent($btn, evt, evtId)
    {
        // made it
        if (evt == 'pk_goal') {
            $('.round-' + this.curRound + ' > .' + this.curTeam + ' > span')
                .removeClass()
                .addClass('fs-3 text-success bi-check-circle-fill');

            // update score
            let score = parseInt($('.score.' + this.curTeam).text());
            score++;
            $('.score.' + this.curTeam).text(score);
        // missed it
        } else {
            $('.round-' + this.curRound + ' > .' + this.curTeam + ' > span')
                .removeClass()
                .addClass('fs-3 text-danger bi-x-circle-fill');
        }

        // disable current player from the dropdown
        if ($btn.parent().hasClass('us')) {
            $('#player_id').find('option:selected').prop('disabled', true);
            $('#player_id').prop('selectedIndex', 0);
        }

        // go to next round? only if both teams have shot this round
        let nextRound      = false;
        let shotsThisRound = 0;
        $('.round-' + this.curRound + ' > .c > span').each(function() {
            if (!$(this).hasClass('bi-circle')) {
                shotsThisRound++;
            }
        });
        if (shotsThisRound == 2) {
            this.curRound++;
            nextRound = true;
        }

        // we shot the min required rounds
        if (this.curRound > this.maxRound) {
            let usScore = parseInt($('.score.us').text());
            let themScore = parseInt($('.score.them').text());
            // do we have a winner?
            if (usScore !== themScore) {
                $('#controls').addClass('d-none');
                $('#end-game').removeClass('d-none');
            // still tied, make another round
            } else {
                let $template = $('#template > div').clone();

                $template.addClass('round-' + this.curRound);
                $template.find('div.small').text(this.curRound);

                $('#template').before($template);

                if (nextRound) {
                    this.maxRound++;
                }
            }
        }

        $('#round').text(this.curRound);

        // go to next team
        this.curTeam = this.curTeam == 'us' ? 'them' : 'us';
        $('.round-' + this.curRound + ' > .' + this.curTeam + ' > span').removeClass('text-light');

        // show controls for next team
        $('#controls > div.us').addClass('d-none');
        $('#controls > div.them').addClass('d-none');
        $('#controls > div.' + this.curTeam).removeClass('d-none');
    }
}

