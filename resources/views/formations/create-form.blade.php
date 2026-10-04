<form method="post" action="{{ $action }}" enctype="multipart/form-data">
    @csrf
    <div class="mb-3">
        <label class="form-label" for="players">Players</label>
        <select class="form-select" id="players" name="players">
            <option value="11" @selected(old('players') == 11)>11v11</option>
            <option value="9" @selected(old('players') == 9)>9v9</option>
            <option value="7" @selected(old('players') == 7)>7v7</option>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label" for="name">Name</label>
        <input type="text" class="form-control" id="name" name="name" placeholder="433" value="{{ old('name') }}"
            inputmode="numeric" pattern="[1-9]+" required>
        <div class="form-text">
            Digits only, one per row of outfield players. They must add up to one less than the player count (4+3+3 = 10 for 11v11).
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label" for="description">Description</label>
        <input type="text" class="form-control" id="description" name="description" placeholder="Defensive" value="{{ old('description') }}" maxlength="255">
    </div>
    <div class="mb-3">
        <label for="formation">Formation</label>
        <textarea class="form-control" id="formation" name="formation" rows="3">{{ old('formation') }}</textarea>
        <div class="form-text">
            Enter positions separated by comma and newline between each line in the formation.
        </div>
    </div>
    <button type="submit" class="btn btn-primary text-white">Submit</button>
</form>
