    <div id="event-modal" class="modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Event<span class="ps-4 text-muted"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row row-cols-3 g-2">
                        <div class="col-12">Shooting</div>
                        <div class="col-9">
                            <button type="button" id="goal" data-event-id="1" data-show='["pkfk", "assist", "xg"]' class="btn btn-secondary w-100">
                                <span class="material-symbols-outlined align-top d-block">sports_soccer</span>Goal
                            </button>
                        </div>
                        <div class="col-6">
                            <button type="button" id="shot_on_target" data-event-id="6" data-show='["pkfk", "assist", "xg"]' class="btn btn-secondary w-100">
                                <span class="material-symbols-outlined align-top d-block">target</span>Shot (On Target)
                            </button>
                        </div>
                        <div class="col-6">
                            <button type="button" id="shot_off_target" data-event-id="7" data-show='["pkfk", "assist", "xg"]' class="btn btn-light w-100">
                                <span class="material-symbols-outlined align-top d-block">block</span>Shot (Off Target)
                            </button>
                        </div>
                        <div class="col-12">Attack</div>
                        <div class="col-6">
                            <button type="button" id="corner_kick" data-event-id="12" data-show='["assist"]' class="btn btn-secondary w-100">
                                <span class="material-symbols-outlined align-top d-block">flag</span>Corner Kick
                            </button>
                        </div>
                        <div class="col-12">Defence</div>
                        <div class="col-6">
                            <button type="button" id="tackle_won" data-event-id="8" class="btn btn-secondary w-100">
                                <span class="material-symbols-outlined align-top d-block">podiatry</span>Tackle (Won)
                            </button>
                        </div>
                        <div class="col-6">
                            <button type="button" id="tackle_lost" data-event-id="9" class="btn btn-light w-100">
                                <span class="material-symbols-outlined align-top d-block">do_not_step</span>Tackle (Lost)
                            </button>
                        </div>
                        <div class="col-12">Fouls</div>
                        <div class="col">
                            <button type="button" id="offsides" data-event-id="14" class="btn btn-light w-100">
                                <span class="material-symbols-outlined align-top d-block">sprint</span>Offsides
                            </button>
                        </div>
                        <div class="col">
                            <button type="button" id="fouled" data-event-id="16" class="btn btn-secondary w-100">
                                <span class="material-symbols-outlined align-top d-block">falling</span>Fouled
                            </button>
                        </div>
                        <div class="col">
                            <button type="button" id="foul" data-event-id="15" class="btn btn-light w-100">
                                <span class="material-symbols-outlined align-top d-block">sports</span>Foul
                            </button>
                        </div>
                        <div class="col">
                            <button type="button" id="yellow_card" data-event-id="17" class="btn btn-light w-100">
                                <span class="material-symbols-outlined align-top d-block text-warning">sell</span>Yellow Card
                            </button>
                        </div>
                        <div class="col">
                            <button type="button" id="red_card" data-event-id="18" class="btn btn-light w-100">
                                <span class="material-symbols-outlined align-top d-block text-danger">sell</span>Red Card
                            </button>
                        </div>
                        <div class="col-12">Goalie</div>
                        <div class="col-6">
                            <button type="button" id="save" data-event-id="10" data-show='["xg"]' class="btn btn-secondary w-100">
                                <span class="material-symbols-outlined align-top d-block">pan_tool</span>Save
                            </button>
                        </div>
                    </div>
                </div><!--/.modal-body-->
            </div><!--/modal-content-->
        </div><!--/.modal-dialog-->
    </div><!--/#event-modal-->
