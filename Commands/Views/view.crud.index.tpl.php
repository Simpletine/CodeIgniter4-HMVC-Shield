<?= show_errors() ?>

<?php if (session()->has('message')) : ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= esc(session('message')) ?>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php endif; ?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Users</h3>
                <div class="card-tools">
                    <a href="/{groupName}/create" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> New User
                    </a>
                </div>
            </div>
            <div class="card-body">
                <table id="users-table" class="table table-bordered table-striped dataTable dtr-inline">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u) : ?>
                        <tr>
                            <td><?= esc($u->id) ?></td>
                            <td><?= esc($u->username) ?></td>
                            <td><?= esc($u->email) ?></td>
                            <td>
                                <?php if ($u->status === 'active' || $u->status === null) : ?>
                                    <span class="badge badge-success">Active</span>
                                <?php else : ?>
                                    <span class="badge badge-secondary"><?= esc($u->status) ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= esc($u->created_at) ?></td>
                            <td>
                                <a href="/{groupName}/<?= esc($u->id) ?>/edit" class="btn btn-xs btn-info">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <form method="post" action="/{groupName}/<?= esc($u->id) ?>/delete" style="display:inline-block"
                                      onsubmit="return confirm('Delete this user?')">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-xs btn-danger">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
