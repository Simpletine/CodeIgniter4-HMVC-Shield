<!DOCTYPE html>
<html lang="en">
<?php
use Config\StnConfig;

include_once __DIR__ . '/header.php'; ?>

<?php
/**
 * Load StnConfig and safely extract all properties with fallback defaults.
 *
 * @var StnConfig|null $stn_config
 */
$stn_config = config('StnConfig');

$_stn_appName         = $stn_config->appName ?? 'SimpleTine';
$_stn_appLogo         = $stn_config->appLogo ?? '/assets/simpletine/img/AdminLTELogo.png';
$_stn_footerCopyright = $stn_config->footerCopyright ?? '&copy; ' . date('Y') . ' SimpleTine. All rights reserved.';
$_stn_footerVersion   = $stn_config->footerVersion ?? '';
$_stn_sidebars        = (isset($stn_config->sidebars) && is_array($stn_config->sidebars)) ? $stn_config->sidebars : [];
$_stn_navbarLeft      = (isset($stn_config->navbarLeft) && is_array($stn_config->navbarLeft)) ? $stn_config->navbarLeft : [];
$_stn_navbarRight     = (isset($stn_config->navbarRight) && is_array($stn_config->navbarRight)) ? $stn_config->navbarRight : [
    'show_search'        => true,
    'show_notifications' => true,
    'show_messages'      => true,
    'show_fullscreen'    => true,
    'show_control_panel' => true,
    'show_user_menu'     => true,
];

/**
 * Resolve an image URL with filesystem fallback.
 *
 * Checks whether the image exists under FCPATH. If not, falls back to the
 * supplied fallback URL (expected to live under assets/img/).
 * Handles cases where simpletine assets have not been published yet,
 * or where a custom logo/avatar path is invalid.
 *
 * @param string $url      Absolute-root-relative URL, e.g. '/assets/simpletine/img/logo.png'
 * @param string $fallback Fallback URL under assets/img/
 */
function _stn_img_url(string $url, string $fallback = '/assets/img/user2-160x160.jpg'): string
{
    if ($url === '') {
        return $fallback;
    }
    $fsPath = rtrim(FCPATH, DIRECTORY_SEPARATOR)
        . DIRECTORY_SEPARATOR
        . ltrim(str_replace('/', DIRECTORY_SEPARATOR, $url), DIRECTORY_SEPARATOR);

    return file_exists($fsPath) ? $url : $fallback;
}

/**
 * Renders a single sidebar/navbar nav-item.
 * Supports new format (link/icon/label/children) and legacy format (anchor/icon_class/attributes).
 *
 * @param array<string, mixed> $item
 */
function _stn_render_nav_item(array $item, bool $isTopLevel = true): void
{
    $isLegacy = isset($item['anchor']);

    if ($isLegacy) {
        // Legacy format: anchor / icon_class / attributes / dropdown_items
        $liAttrs     = $item['attributes'] ?? ['class' => 'nav-item'];
        $anchorAttrs = $item['anchor'] ?? [];
        $icon        = $item['icon_class'] ?? '';
        $label       = $item['label'] ?? '';
        $children    = $item['dropdown_items'] ?? null;

        echo '<li ' . stringify_attributes($liAttrs) . '>';
        echo '<a ' . stringify_attributes($anchorAttrs) . '>';
        if ($icon !== '') {
            echo '<i class="nav-icon ' . esc($icon, 'attr') . '"></i>';
        }
        if (! empty($children) && is_array($children)) {
            echo '<p>' . esc($label) . '<i class="right fas fa-angle-left"></i></p>';
            echo '</a>';
            echo '<ul class="nav nav-treeview">';

            foreach ($children as $child) {
                _stn_render_nav_item($child, false);
            }
            echo '</ul>';
        } else {
            echo '<p>' . esc($label) . '</p>';
            echo '</a>';
        }
        echo '</li>';
    } else {
        // New format: icon / label / link / children / link_class
        $icon      = $item['icon'] ?? '';
        $label     = $item['label'] ?? '';
        $link      = $item['link'] ?? '#';
        $linkClass = $item['link_class'] ?? 'nav-link';
        $children  = $item['children'] ?? null;

        echo '<li class="nav-item">';
        echo '<a href="' . esc($link, 'attr') . '" class="' . esc($linkClass, 'attr') . '">';
        if ($icon !== '') {
            echo '<i class="nav-icon ' . esc($icon, 'attr') . '"></i>';
        }
        if (! empty($children) && is_array($children)) {
            echo '<p>' . esc($label) . '<i class="right fas fa-angle-left"></i></p>';
            echo '</a>';
            echo '<ul class="nav nav-treeview">';

            foreach ($children as $child) {
                _stn_render_nav_item($child, false);
            }
            echo '</ul>';
        } else {
            echo '<p>' . esc($label) . '</p>';
            echo '</a>';
        }
        echo '</li>';
    }
}

/**
 * Renders a single left-navbar item (simplified — no children).
 *
 * @param array<string, mixed> $item
 */
function _stn_render_navbar_item(array $item): void
{
    if (isset($item['anchor'])) {
        // Legacy
        $anchorAttrs = $item['anchor'] ?? [];
        $label       = $item['label'] ?? '';
        echo '<li class="nav-item d-none d-sm-inline-block">';
        echo '<a ' . stringify_attributes($anchorAttrs) . '>' . esc($label) . '</a>';
        echo '</li>';
    } else {
        $link      = $item['link'] ?? '#';
        $label     = $item['label'] ?? '';
        $linkClass = $item['link_class'] ?? 'nav-link';
        echo '<li class="nav-item d-none d-sm-inline-block">';
        echo '<a href="' . esc($link, 'attr') . '" class="' . esc($linkClass, 'attr') . '">' . esc($label) . '</a>';
        echo '</li>';
    }
}
?>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">

        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <!-- Sidebar toggle always present -->
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                        <i class="fas fa-bars"></i>
                    </a>
                </li>
                <?php foreach ($_stn_navbarLeft as $_navItem) :
                    _stn_render_navbar_item($_navItem);
                endforeach; ?>
            </ul>

            <!-- Right navbar -->
            <ul class="navbar-nav ml-auto">

                <?php if ($_stn_navbarRight['show_search'] ?? true) : ?>
                <!-- Navbar Search -->
                <li class="nav-item">
                    <a class="nav-link" data-widget="navbar-search" href="#" role="button">
                        <i class="fas fa-search"></i>
                    </a>
                    <div class="navbar-search-block">
                        <form class="form-inline">
                            <div class="input-group input-group-sm">
                                <input class="form-control form-control-navbar" type="search"
                                    placeholder="Search" aria-label="Search">
                                <div class="input-group-append">
                                    <button class="btn btn-navbar" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    <button class="btn btn-navbar" type="button" data-widget="navbar-search">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </li>
                <?php endif; ?>

                <?php if ($_stn_navbarRight['show_messages'] ?? true) : ?>
                <!-- Messages Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#">
                        <i class="far fa-comments"></i>
                        <span class="badge badge-danger navbar-badge">3</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <a href="#" class="dropdown-item">
                            <div class="media">
                                <img src="<?= esc(_stn_img_url('/assets/simpletine/img/user1-128x128.jpg', '/assets/img/user2-160x160.jpg'), 'attr') ?>"
                                    alt="User" class="img-size-50 mr-3 img-circle">
                                <div class="media-body">
                                    <h3 class="dropdown-item-title">Brad Diesel</h3>
                                    <p class="text-sm">Call me whenever you can...</p>
                                    <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
                                </div>
                            </div>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item dropdown-footer">See All Messages</a>
                    </div>
                </li>
                <?php endif; ?>

                <?php if ($_stn_navbarRight['show_notifications'] ?? true) : ?>
                <!-- Notifications Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#">
                        <i class="far fa-bell"></i>
                        <span class="badge badge-warning navbar-badge">15</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <span class="dropdown-header">15 Notifications</span>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item">
                            <i class="fas fa-envelope mr-2"></i> 4 new messages
                            <span class="float-right text-muted text-sm">3 mins</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item">
                            <i class="fas fa-users mr-2"></i> 8 friend requests
                            <span class="float-right text-muted text-sm">12 hours</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item">
                            <i class="fas fa-file mr-2"></i> 3 new reports
                            <span class="float-right text-muted text-sm">2 days</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item dropdown-footer">See All Notifications</a>
                    </div>
                </li>
                <?php endif; ?>

                <?php if ($_stn_navbarRight['show_fullscreen'] ?? true) : ?>
                <li class="nav-item">
                    <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                        <i class="fas fa-expand-arrows-alt"></i>
                    </a>
                </li>
                <?php endif; ?>

                <?php if ($_stn_navbarRight['show_control_panel'] ?? true) : ?>
                <li class="nav-item">
                    <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#" role="button">
                        <i class="fas fa-th-large"></i>
                    </a>
                </li>
                <?php endif; ?>

                <?php if (($_stn_navbarRight['show_user_menu'] ?? true) && user() !== null) : ?>
                <!-- User dropdown -->
                <?php $_stn_userImg = esc(_stn_img_url('/assets/simpletine/img/user2-160x160.jpg', '/assets/img/user2-160x160.jpg'), 'attr'); ?>
                <li class="nav-item dropdown user-menu">
                    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                        <img src="<?= $_stn_userImg ?>"
                            class="user-image img-circle elevation-2" alt="User">
                        <span class="d-none d-md-inline"><?= esc(username() ?? 'User') ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <li class="user-header bg-primary">
                            <img src="<?= $_stn_userImg ?>"
                                class="img-circle elevation-2" alt="User">
                            <p><?= esc(username() ?? 'User') ?>
                                <small><?= esc(email() ?? '') ?></small>
                            </p>
                        </li>
                        <li class="user-footer">
                            <a href="/profile" class="btn btn-default btn-flat">Profile</a>
                            <a href="/logout" class="btn btn-default btn-flat float-right">Logout</a>
                        </li>
                    </ul>
                </li>
                <?php endif; ?>

            </ul>
        </nav>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <a href="/" class="brand-link">
                <?php $_stn_logoUrl = _stn_img_url($_stn_appLogo, '/assets/img/AdminLTELogo.png'); ?>
                <?php if ($_stn_logoUrl !== '') : ?>
                <img src="<?= esc($_stn_logoUrl, 'attr') ?>" alt="Logo"
                    class="brand-image img-circle elevation-3" style="opacity:.8">
                <?php endif; ?>
                <span class="brand-text font-weight-light">
                    <?= esc($_stn_appName) ?>
                </span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Sidebar user panel -->
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image">
                        <img src="<?= esc(_stn_img_url('/assets/simpletine/img/user2-160x160.jpg', '/assets/img/user2-160x160.jpg'), 'attr') ?>"
                            class="img-circle elevation-2" alt="User Image">
                    </div>
                    <div class="info">
                        <a href="#" class="d-block"><?= esc(username() ?? '') ?></a>
                    </div>
                </div>

                <!-- Sidebar Search -->
                <div class="form-inline">
                    <div class="input-group" data-widget="sidebar-search">
                        <input class="form-control form-control-sidebar" type="search"
                            placeholder="Search" aria-label="Search">
                        <div class="input-group-append">
                            <button class="btn btn-sidebar">
                                <i class="fas fa-search fa-fw"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column"
                        data-widget="treeview" role="menu" data-accordion="false">
                        <?php foreach ($_stn_sidebars as $_sidebarItem) {
                            _stn_render_nav_item($_sidebarItem);
                        } ?>
                    </ul>
                </nav>
            </div>
            <!-- /.sidebar -->
        </aside>

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <!-- Content Header -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0"><?= esc($page_header ?? '') ?></h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="/">Home</a></li>
                                <li class="breadcrumb-item active"><?= esc($page_header ?? 'Index') ?></li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.content-header -->

            <!-- Main content -->
            <div class="content">
                <div class="container-fluid">
                    <?php if (isset($contents) && is_array($contents)) :
                        foreach ($contents as $content) {
                            echo view($content);
                        }
                    endif; ?>
                </div>
            </div>
            <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->

        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
            <div class="p-3">
                <h5><?= esc($_stn_appName) ?></h5>
                <p>Control sidebar</p>
            </div>
        </aside>

        <!-- Main Footer -->
        <footer class="main-footer">
            <div class="float-right d-none d-sm-inline">
                <?= last_login('Last Login: ') ?>
                <?php if ($_stn_footerVersion !== '') : ?>
                    &nbsp;&mdash;&nbsp;<?= esc($_stn_footerVersion) ?>
                <?php endif; ?>
            </div>
            <strong><?= $_stn_footerCopyright ?></strong>
        </footer>
    </div>
    <!-- ./wrapper -->

    <!-- REQUIRED SCRIPTS -->
    <script src="/assets/simpletine/plugins/jquery/jquery.min.js"></script>
    <script src="/assets/simpletine/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>

    <?php if (isset($is_datatables) && $is_datatables) : ?>
    <script src="/assets/simpletine/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="/assets/simpletine/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
    <script src="/assets/simpletine/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
    <script src="/assets/simpletine/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
    <?php endif; ?>

    <script src="/assets/simpletine/js/adminlte.min.js"></script>

    <?php if (isset($js) && is_array($js)) :
        foreach ($js as $file) {
            echo '<script src="' . esc($file, 'attr') . '"></script>';
        }
    endif; ?>

    <?php if (isset($scripts) && is_array($scripts)) :
        foreach ($scripts as $script) {
            echo view($script);
        }
    endif; ?>

</body>
</html>