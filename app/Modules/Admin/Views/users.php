<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">List of users</h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <table id="table" class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Updated At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($rows) && is_array($rows)) : ?>
                            <?php foreach ($rows as $data) : ?>
                                <?php  $identity = $data->identities; ?>
                        <tr>
                            <td><?= esc($data->id) ?></td>
                            <td><?= esc($data->username) ?></td>
                            <td><?= esc($data->email) ?></td>
                            <td><?= esc($data->status ?? '-') ?></td>
                            <td><?= esc($data->created_at) ?></td>
                            <td><?= esc($data->updated_at) ?></td>
                        </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Updated At</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <!-- /.card-body -->
        </div>
    </div>
</div>
