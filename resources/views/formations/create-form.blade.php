<form method="post" action="{{ $action }}" enctype="multipart/form-data">
    @csrf
    <div class="mb-3">
        <label class="form-label" for="players">Players</label>
        <select class="form-select" id="players" name="players">
            <option value="11">11v11</option>
            <option value="9">9v9</option>
            <option value="7">7v7</option>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label" for="name">Name</label>
        <input type="text" class="form-control" id="name" name="name" placeholder="433">
    </div>
    <div class="mb-3">
        <label for="formation">Formation</label>
        <textarea class="form-control" id="formation" name="formation" rows="3"></textarea>
        <div class="form-text">
            Enter positions separated by comma and newline between each line in the formation.
        </div>
    </div>
    <button type="submit" class="btn btn-primary text-white">Submit</button>
</form>
