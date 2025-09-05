export default class EventTimeline
{
    constructor(selector)
    {
        this.selector = selector;

        this.eventToIcon = {
            'start'                : 'watch_arrow',
            'goal'                 : 'sports_soccer',
            'sub_in'               : 'arrow_right_alt',
            'sub_out'              : 'arrow_left_alt',
            'goal_against'         : 'sports_soccer',
            'shot_on_target'       : 'target',
            'shot_off_target'      : 'block',
            'tackle_won'           : 'podiatry',
            'tackle_lost'          : 'do_not_step',
            'save'                 : 'pan_tool',
            'shot_against'         : 'block',
            'corner_kick'          : 'flag',
            'corner_kick_against'  : 'flag',
            'offsides'             : 'sprint',
            'foul'                 : 'sports',
            'fouled'               : 'falling',
            'yellow_card'          : 'sell',
            'red_card'             : 'sell',
            'penalty_goal'         : 'sports_soccer',
            'penalty_on_target'    : 'target',
            'penalty_off_target'   : 'block',
            'free_kick_goal'       : 'sports_soccer',
            'free_kick_on_target'  : 'target',
            'free_kick_off_target' : 'block',
            'halftime'             : 'timer_pause',
            'fulltime'             : 'timer',
        };

        this.eventToName = {
            'start'                : 'Starters',
            'sub_in'               : 'In',
            'sub_out'              : 'Out',
            'goal'                 : 'Goal',
            'goal_against'         : 'Goal',
            'shot_on_target'       : 'Shot On Target',
            'shot_off_target'      : 'Shot Off Target',
            'tackle_won'           : 'Tackle Won',
            'tackle_lost'          : 'Tackle Lost',
            'save'                 : 'Save',
            'shot_against'         : 'Shot Off Target',
            'corner_kick'          : 'Corner Kick',
            'corner_kick_against'  : 'Corner Kick',
            'offsides'             : 'Offsides',
            'foul'                 : 'Foul',
            'fouled'               : 'Fouled',
            'yellow_card'          : 'Yellow Card',
            'red_card'             : 'Red Card',
            'penalty_goal'         : 'Penalty Goal',
            'penalty_on_target'    : 'Penalty On Target',
            'penalty_off_target'   : 'Penalty Off Target',
            'free_kick_goal'       : 'Free Kick Goal',
            'free_kick_on_target'  : 'Free Kick On Target',
            'free_kick_off_target' : 'Free Kick Off Target',
            'halftime'             : 'Half',
            'fulltime'             : 'Full',
        };
    }

    /**
     * addEvent
     *
     * <div class="event them home foul border rounded">
     *     <div class="header d-flex justify-content-between">
     *         <div class="type d-flex align-items-center">
     *             <span class="material-symbols-outlined">sell</span>
     *             <b class="ps-2">Yellow Card</b>
     *         </div>
     *         <div class="time">12</div>
     *     </div>
     *     <div class="details">
     *         <b>Unknown</b>
     *         <div class="notes">Jerks</div>
     *     </div>
     * </div>
     */
    addEvent(eventData, side, managed)
    {
        let eventDiv   = document.createElement('div');
        let headerDiv  = document.createElement('div');
        let typeDiv    = document.createElement('div');
        let iconSpan   = document.createElement('span');
        let typeB      = document.createElement('b');
        let timeDiv    = document.createElement('div');
        let detailsDiv = document.createElement('div');
        let detailsB   = document.createElement('b');
        let notesDiv   = document.createElement('div');
        let xgDiv      = document.createElement('div');

        // event
        eventDiv.className = 'event border border-light rounded ' + side + ' ' + eventData.event_name;

        if (managed)
        {
            eventDiv.className += ' managed shadow-sm';
        }

        // header
        headerDiv.className = 'header d-flex justify-content-between';

        // type
        typeDiv.className = 'type d-flex align-items-center';

        // icon
        iconSpan.className = 'icon material-symbols-outlined';
        iconSpan.textContent = this.eventToIcon[eventData.event_name];

        // event name
        typeB.className = 'fs-6 ps-2';
        typeB.textContent = this.eventToName[eventData.event_name];

        // time
        let t = eventData.time;
        t = t.substring(0, 2);
        t = parseInt(t) + 1;

        timeDiv.className = 'time fs-6 text-secondary fw-bold';
        timeDiv.textContent = t;

        // details
        detailsDiv.className = 'details';

        // player name
        detailsB.textContent = eventData.player_name;

        // notes
        notesDiv.className = 'notes';
        notesDiv.textContent = eventData.notes;

        // xg
        if (eventData.xg !== null)
        {
            let xgSpan = document.createElement('span');

            xgDiv.className = 'xg';
            xgDiv.textContent = '0.' + eventData.xg;

            xgSpan.className = 'pe-2';
            xgSpan.textContent = 'xG';
            xgDiv.prepend(xgSpan);
        }

        // now put it all together
        detailsDiv.append(detailsB);
        detailsDiv.append(notesDiv);
        detailsDiv.append(xgDiv);
        typeDiv.append(iconSpan);
        typeDiv.append(typeB);
        headerDiv.append(typeDiv);
        headerDiv.append(timeDiv);
        eventDiv.append(headerDiv);
        eventDiv.append(detailsDiv);

        $(this.selector).append(eventDiv);
    }
}
