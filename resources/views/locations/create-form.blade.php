<form method="post" action="{{ $action }}" enctype="multipart/form-data">
    @csrf
    <div class="mb-3">
        <label class="form-label" for="name">Name</label>
        <input type="text" class="form-control" id="name" name="name">
    </div>
    <div class="mb-3">
        <label class="form-label" for="address">Address</label>
        <input type="text" class="form-control" id="address" name="address" placeholder="123 4th Street, Big City, OH">
    </div>

    <button type="submit" class="btn btn-primary">Submit</button>
</form>
