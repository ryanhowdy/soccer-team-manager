<form method="post" action="{{ $createClubAction }}" enctype="multipart/form-data">
    @csrf
    <div class="mb-3">
        <label class="form-label" for="name">Name</label>
        <input type="text" class="form-control" id="name" name="name" placeholder="Club Name">
    </div>
    <div class="mb-3">
        <label class="form-label" for="logo">Logo</label>
        <input type="file" class="form-control" id="logo" name="logo">
    </div>
    <div class="row g-3">
        <div class="col-8">
            <label class="form-label" for="city">City</label>
            <input type="text" class="form-control" name="city" id="city">
        </div>
        <div class="col-4">
            <label class="form-label" for="state">State</label>
            <input type="text" class="form-control" name="state" id="state">
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label" for="website">Website</label>
        <input type="text" class="form-control" id="website" name="website" placeholder="https://www.my-soccer-club.com">
    </div>
    <div class="mb-3">
        <label for="notes">Notes</label>
        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Submit</button>
</form>
