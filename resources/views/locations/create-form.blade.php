<form method="post" action="{{ route('locations.store') }}">
    @csrf
    <div class="mb-3">
        <label class="form-label required" for="name">Name</label>
        <input type="text" class="form-control" id="name" name="name" required>
    </div>
    <div class="mb-3">
        <label class="form-label required" for="address">Address</label>
        <input type="text" class="form-control" id="address" name="address" required placeholder="123 4th Street, Big City, OH">
    </div>

    <button type="submit" class="btn btn-primary">Submit</button>
</form>
