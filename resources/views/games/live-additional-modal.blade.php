    <div id="additional-modal" class="modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="additional-form">
                        <div id="shooting-options">
                            <div class="mb-3">
                                <input class="btn-check" type="radio" value="penalty" name="pk_fk" id="penalty">
                                <label class="btn btn-outline-primary" for="penalty">Penalty Kick?</label>
                                <input class="btn-check" type="radio" value="free_kick" name="pk_fk" id="free_kick">
                                <label class="btn btn-outline-primary" for="free_kick">Free Kick?</label>
                            </div>
                            <div class="mb-3">
                                <label for="player_id" class="form-label">Assist From</label>
                                <select id="player_id" name="player_id" class="form-select">
                                    <option></option>
                                @foreach ($players as $id => $p)
                                    <option value="{{ $id }}">{{ $p['name'] }}</option>
                                @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">xG</label>
                                <div class="xg">
                                    <input class="btn-check" type="radio" value="0" name="xg" id="xg-0">
                                    <label class="btn btn-scale-0" for="xg-0">0</label>
                                    <input class="btn-check" type="radio" value="1" name="xg" id="xg-1">
                                    <label class="btn btn-scale-1" for="xg-1">1</label>
                                    <input class="btn-check" type="radio" value="2" name="xg" id="xg-2">
                                    <label class="btn btn-scale-2" for="xg-2">2</label>
                                    <input class="btn-check" type="radio" value="3" name="xg" id="xg-3">
                                    <label class="btn btn-scale-3" for="xg-3">3</label>
                                    <input class="btn-check" type="radio" value="4" name="xg" id="xg-4">
                                    <label class="btn btn-scale-4" for="xg-4">4</label>
                                    <input class="btn-check" type="radio" value="5" name="xg" id="xg-5">
                                    <label class="btn btn-scale-5" for="xg-5">5</label>
                                    <input class="btn-check" type="radio" value="6" name="xg" id="xg-6">
                                    <label class="btn btn-scale-6" for="xg-6">6</label>
                                    <input class="btn-check" type="radio" value="7" name="xg" id="xg-7">
                                    <label class="btn btn-scale-7" for="xg-7">7</label>
                                    <input class="btn-check" type="radio" value="8" name="xg" id="xg-8">
                                    <label class="btn btn-scale-8" for="xg-8">8</label>
                                    <input class="btn-check" type="radio" value="9" name="xg" id="xg-9">
                                    <label class="btn btn-scale-9" for="xg-9">9</label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="notes">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button id="additional-save" type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </form>
                </div><!--/.modal-body-->
            </div><!--/modal-content-->
        </div><!--/.modal-dialog-->
    </div><!--/#additional-modal-->
