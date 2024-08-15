export default class EventTimeline
{
    constructor(selector)
    {
        this.selector = selector;

        this.eventToIcon = {
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
     * <div class="event home">
     *     <div class="time">4</div>
     *     <span class="icon material-symbols-outlined" data-event-id="3">sports_soccer</span>
     *     <div class="details">
     *         <div class="fw-bold">Goal</div>
     *         Bob Smith
     *     </div>
     * </div>
     */
    addEvent(eventData, side)
    {
        let eventDiv     = document.createElement('div');
        let timeDiv      = document.createElement('div');
        let iconSpan     = document.createElement('span');
        let detailsDiv   = document.createElement('div');
        let eventNameDiv = document.createElement('div');

        eventDiv.className = 'event ' + side + ' ' + eventData.event_name;

        let t = eventData.time;
        t = t.substring(0, 2);
        t = parseInt(t) + 1;

        timeDiv.className = 'time';
        timeDiv.textContent = t;

        iconSpan.className = 'icon material-symbols-outlined';
        iconSpan.textContent = this.eventToIcon[eventData.event_name];

        detailsDiv.className = 'details';
        detailsDiv.textContent = eventData.player_name;

        eventNameDiv.className = 'fw-bold';
        eventNameDiv.textContent = this.eventToName[eventData.event_name];
        
        detailsDiv.prepend(eventNameDiv);

        eventDiv.append(timeDiv);
        eventDiv.append(iconSpan);
        eventDiv.append(detailsDiv);

        $(this.selector).append(eventDiv);
    }
}
