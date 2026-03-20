<?php if (session()->has('message')) : ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= esc(session('message')) ?>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php endif; ?>

<?= show_errors() ?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card card-primary">
            <div class="card-header">
                <h4 class="card-title">Change Password</h4>
            </div>
            <form action="/profile" method="post">
                <?= csrf_field() ?>
                <div class="card-body">

                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password"
                               id="current_password"
                               name="current_password"
                               class="form-control"
                               placeholder="Enter current password"
                               autocomplete="current-password"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password"
                               id="new_password"
                               name="new_password"
                               class="form-control"
                               placeholder="Enter new password (min. 8 characters)"
                               autocomplete="new-password"
                               minlength="8"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password"
                               id="confirm_password"
                               name="confirm_password"
                               class="form-control"
                               placeholder="Repeat new password"
                               autocomplete="new-password"
                               minlength="8"
                               required>
                    </div>

                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
