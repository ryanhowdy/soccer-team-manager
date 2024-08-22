    <div id="event-modal" class="modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div>Shooting</div>
                    <button type="button" id="goal" data-event-id="1" data-show='["pkfk", "assist", "xg"]' class="btn btn-secondary mb-1 me-1">
                        <span class="material-symbols-outlined align-top me-1">sports_soccer</span>Goal
                    </button>
                    <button type="button" id="shot_on_target" data-event-id="6" data-show='["pkfk", "assist", "xg"]' class="btn btn-secondary mb-1 me-1">
                        <span class="material-symbols-outlined align-top me-1">target</span>Shot (On Target)
                    </button>
                    <button type="button" id="shot_off_target" data-event-id="7" data-show='["pkfk", "assist", "xg"]' class="btn btn-light mb-1 me-1">
                        <span class="material-symbols-outlined align-top me-1">block</span>Shot (Off Target)
                    </button>
                    <div>Attack</div>
                    <button type="button" id="corner_kick" data-event-id="12" data-show='["assist"]' class="btn btn-secondary mb-1 me-1">
                        <span class="material-symbols-outlined align-top me-1">flag</span>Corner Kick
                    </button>
                    <div>Defence</div>
                    <button type="button" id="tackle_won" data-event-id="8" class="btn btn-secondary mb-1 me-1">
                        <span class="material-symbols-outlined align-top me-1">podiatry</span>Tackle (Won)
                    </button>
                    <button type="button" id="tackle_lost" data-event-id="9" class="btn btn-light mb-1 me-1">
                        <span class="material-symbols-outlined align-top me-1">do_not_step</span>Tackle (Lost)
                    </button>
                    <div>Fouls</div>
                    <button type="button" id="offsides" data-event-id="14" class="btn btn-light mb-1 me-1">
                        <span class="material-symbols-outlined align-top me-1">sprint</span>Offsides
                    </button>
                    <button type="button" id="fouled" data-event-id="16" class="btn btn-secondary mb-1 me-1">
                        <span class="material-symbols-outlined align-top me-1">falling</span>Fouled
                    </button>
                    <button type="button" id="foul" data-event-id="15" class="btn btn-light mb-1 me-1">
                        <span class="material-symbols-outlined align-top me-1">sports</span>Foul
                    </button>
                    <button type="button" id="yellow_card" data-event-id="17" class="btn btn-light mb-1 me-1">
                        <span class="material-symbols-outlined align-top me-1 text-warning">sell</span>Yellow Card
                    </button>
                    <button type="button" id="red_card" data-event-id="18" class="btn btn-light mb-1 me-1">
                        <span class="material-symbols-outlined align-top me-1 text-danger">sell</span>Red Card
                    </button>
                    <div>Goalie</div>
                    <button type="button" id="save" data-event-id="10" data-show='["xg"]' class="btn btn-secondary mb-1 me-1">
                        <span class="material-symbols-outlined align-top me-1">pan_tool</span>Save
                    </button>
                </div><!--/.modal-body-->
            </div><!--/modal-content-->
        </div><!--/.modal-dialog-->
    </div><!--/#event-modal-->
