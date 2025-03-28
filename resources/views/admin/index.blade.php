@extends('layouts.main')

@section('body-id', 'me')

@section('content')
    <div class="container main-content">

        <div class="rounded rounded-3 bg-white py-2 px-3 mb-2 d-flex align-items-center justify-content-end">
            <div>
                <a href="#" class="btn btn-sm btn-primary text-white text-nowrap" data-bs-toggle="modal" data-bs-target="#create-user">
                    <span class="bi-plus-lg pe-2"></span>Add User
                </a>
            </div>
        </div>

        <div class="rounded rounded-3 bg-white p-3 mb-2">
            <h4 class="border-bottom border-light mb-4 pt-4 pb-2 d-flex flex-wrap justify-content-between">
                <div class="pe-3">Users</div>
            </h4>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th scope="col">Email</th>
                        <th scope="col">Managed Players</th>
                        <th scope="col">Roles</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($users as $u)
                    <tr>
                        <td>{{ $u->email }}</td>
                        <td>
                        @foreach($u->managedPlayers as $m)
                            <div class="">{{ $m->player->name }}</div>
                        @endforeach
                        </td>
                        <td>
                        @forelse($u->getRoleNames() as $role)
                            <span @class([
                                'badge',
                                'bg-info-subtle border-info text-info' => $role == 'admin',
                                'bg-danger-subtle border-danger text-danger' => $role == 'manager',
                            ])>{{ $role }}</span>
                        @empty
                            <select class="form-select form-select-sm w-auto" data-url="{{ route('ajax.users.roles.store', ['user' => $u->id]) }}">
                                <option></option>
                            @foreach(['admin', 'manager'] as $r)
                                <option value="{{ $r }}">{{ $r }}</span>
                            @endforeach
                            </select>
                        @endforelse
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <script>
    $('select').on('change', function() {
        let $select = $(this);

        $.ajax({
            url  : $select.attr('data-url'),
            type : 'POST',
            data : {
                role : $select.val(),
            },
        }).done((data) => {
            location.reload();
        }).fail(() => {
            $('table').before('<p class="alert alert-danger mt-2">Something went wrong, couldn\'t add role.</p>');
        });
    });
    </script>
    <div id="create-user" class="modal fade" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content py-4 px-2">
                <div class="modal-header">
                    <h5 class="modal-title">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" action="{{ route('admin.users.store') }}">
                        @csrf
                        <div class="mb-3 required">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}">
                        </div>
                        <div class="mb-3 required">
                            <label for="password">Password</label>
                            <input type="password" class="form-control" id="password" name="password">
                        </div>
                        <div class="mb-3 required">
                            <label for="password_confirmation">Confirm Password</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                        </div>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

