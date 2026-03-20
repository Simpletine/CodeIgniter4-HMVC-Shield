<?= show_errors() ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card card-warning">
            <div class="card-header">
                <h4 class="card-title">Edit User</h4>
            </div>
            <form action="/{groupName}/<?= esc($userRow->id) ?>/update" method="post">
                <?= csrf_field() ?>
                <div class="card-body">

                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text"
                               id="username"
                               name="username"
                               class="form-control"
                               value="<?= esc(old('username', $userRow->username)) ?>"
                               placeholder="Enter username"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email"
                               id="email"
                               name="email"
                               class="form-control"
                               value="<?= esc(old('email', $userRow->email)) ?>"
                               placeholder="Enter email"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="new_password">
                            New Password
                            <small class="text-muted">(leave blank to keep current)</small>
                        </label>
                        <input type="password"
                               id="new_password"
                               name="new_password"
                               class="form-control"
                               placeholder="Enter new password (min. 8 characters)"
                               minlength="8"
                               autocomplete="new-password">
                    </div>

                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-warning">Save Changes</button>
                    <a href="/{groupName}" class="btn btn-default ml-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
