<?= show_errors() ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card card-primary">
            <div class="card-header">
                <h4 class="card-title">Create User</h4>
            </div>
            <form action="/{groupName}/store" method="post">
                <?= csrf_field() ?>
                <div class="card-body">

                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text"
                               id="username"
                               name="username"
                               class="form-control"
                               value="<?= esc(old('username')) ?>"
                               placeholder="Enter username"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email"
                               id="email"
                               name="email"
                               class="form-control"
                               value="<?= esc(old('email')) ?>"
                               placeholder="Enter email"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password"
                               id="password"
                               name="password"
                               class="form-control"
                               placeholder="Enter password (min. 8 characters)"
                               minlength="8"
                               autocomplete="new-password"
                               required>
                    </div>

                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Create User</button>
                    <a href="/{groupName}" class="btn btn-default ml-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
