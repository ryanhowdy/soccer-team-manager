    {{--
        Event picker for a tapped player, or for the opponent (data-against="1").
        Above the divider each row is a matched pair: the good event on the left,
        its bad opposite on the right. Below it, the rest. Good events always sit
        in the left column and bad in the right, even when the opponent hides some.
        The header (who, minute) is filled in by LiveAll when the modal opens.
    --}}
    <div id="event-modal" class="modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="event-who">
                    <img class="event-who-img" src="{{ asset('img/photo_none.png') }}" alt="">
                    <div class="event-who-text">
                        <div class="event-who-name"></div>
                        <div class="event-who-detail"></div>
                    </div>
                    <span class="event-who-time badge text-bg-dark"></span>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <button type="button" id="goal" data-event-id="1" data-show='["pkfk", "assist", "xg"]' class="btn event-goal">
                        <span class="material-symbols-outlined">sports_soccer</span>Goal
                    </button>

                    <div class="event-pairs">
                        <button type="button" id="shot_on_target" data-event-id="6" data-show='["pkfk", "assist", "xg"]' class="btn good">
                            <span class="material-symbols-outlined">target</span>Shot on target
                        </button>
                        <button type="button" id="shot_off_target" data-event-id="7" data-show='["pkfk", "assist", "xg"]' class="btn bad">
                            <span class="material-symbols-outlined">block</span>Shot off target
                        </button>

                        <button type="button" id="tackle_won" data-event-id="8" class="btn good">
                            <span class="material-symbols-outlined">podiatry</span>Tackle won
                        </button>
                        <button type="button" id="tackle_lost" data-event-id="9" class="btn bad">
                            <span class="material-symbols-outlined">do_not_step</span>Tackle lost
                        </button>

                        <button type="button" id="fouled" data-event-id="16" class="btn good">
                            <span class="material-symbols-outlined">falling</span>Fouled
                        </button>
                        <button type="button" id="foul" data-event-id="15" class="btn bad">
                            <span class="material-symbols-outlined">sports</span>Foul
                        </button>

                        <hr class="event-divider">

                        <button type="button" id="corner_kick" data-event-id="12" class="btn good">
                            <span class="material-symbols-outlined">flag</span>Corner kick
                        </button>
                        <button type="button" id="offsides" data-event-id="14" class="btn bad">
                            <span class="material-symbols-outlined">sprint</span>Offsides
                        </button>

                        <button type="button" id="save" data-event-id="10" data-show='["xg"]' class="btn good">
                            <span class="material-symbols-outlined">pan_tool</span>Save
                        </button>
                        <button type="button" id="yellow_card" data-event-id="17" class="btn bad">
                            <span class="material-symbols-outlined card-yellow">sell</span>Yellow card
                        </button>

                        <button type="button" id="red_card" data-event-id="18" class="btn bad">
                            <span class="material-symbols-outlined card-red">sell</span>Red card
                        </button>
                    </div>
                </div><!--/.modal-body-->
            </div><!--/modal-content-->
        </div><!--/.modal-dialog-->
    </div><!--/#event-modal-->
