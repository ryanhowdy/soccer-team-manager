<form class="row g-3" action="{{ route('seasons.store') }}" method="post">
    @csrf
    <div class="col-12 col-sm-6">
        <label for="season" class="form-label">Season</label>
        <input type="text" class="form-control" name="season" id="season" placeholder="Fall/Spring">
    </div>
    <div class="col-12 col-sm-6">
        <label for="year" class="form-label">Year</label>
        <input type="number" class="form-control" name="year" id="year" placeholder="{{ date('Y') }}">
    </div>
    <div class="col-12">
        <button type="submit" class="btn btn-primary">Create</button>
    </div>
</form>

