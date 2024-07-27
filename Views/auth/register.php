<!DOCTYPE html>
<html lang="en">

<?php include_once __DIR__ . '/../header.php'; ?>

<body class="hold-transition register-page">
    <div class="register-box">
        <div class="card card-outline card-primary">
            <div class="card-header text-center">
                <a href="/" class="h1"><b>Simple</b>Tine</a>
            </div>
            <div class="card-body">
                <?= show_errors() ?>
                <form action="<?= url_to('register') ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="input-group mb-3">
                        <input type="text" class="form-control" name="username" inputmode="text" autocomplete="username"
                            placeholder="<?= lang('Auth.username') ?>" value="<?= old('username') ?>">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-user"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="email" class="form-control" name="email" inputmode="email" autocomplete="email"
                            placeholder="<?= lang('Auth.email') ?>" value="<?= old('email') ?>">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-envelope"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="password" class="form-control" name="password" inputmode="text"
                            autocomplete="new-password" placeholder="<?= lang('Auth.password') ?>">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="password" class="form-control" name="password_confirm" inputmode="text"
                            autocomplete="new-password" placeholder="<?= lang('Auth.passwordConfirm') ?>">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block"><?= lang('Auth.register') ?></button>
                </form>

                <p class="text-center mt-3"><?= lang('Auth.haveAccount') ?> <a
                        href="<?= url_to('login') ?>"><?= lang('Auth.login') ?></a></p>
            </div>
            <!-- /.form-box -->
        </div><!-- /.card -->
    </div>
    <!-- /.register-box -->
</body>

</html>