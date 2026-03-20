[![Official Website](https://img.shields.io/badge/Official_Website-Visit-yellow)](https://simpletine.com)   [![YouTube Channel](https://img.shields.io/badge/YouTube_Channel-Subscribe-FF0000)](https://www.youtube.com/channel/UCRuDf31rPyyC2PUbsMG0vZw)

# CodeIgniter 4 HMVC

A modular HMVC (Hierarchical Model-View-Controller) architecture for CodeIgniter 4, integrated with the official authentication package CodeIgniter 4 Shield and a full Admin Dashboard built on AdminLTE.

---

# Installation Guide

Create a project
```bash
composer create-project simpletine/codeigniter4-starter ci4_hmvc --stability=dev
```

Copy `env` file and configure database
```bash
cp env .env
```

Run the app (use `--port=9000` for a custom port)
```bash
php spark serve
```

## Configuration

Install HMVC package
```bash
composer require simpletine/hmvc-shield
```

Run the interactive setup wizard
```bash
php spark simpletine:setup
```

The setup wizard will:
1. Collect namespace / prefix / directory preferences
2. Optionally create a database (`db:create`)
3. Optionally install Shield (`shield:setup`)
4. Scaffold the default **Users** CRUD module
5. Publish assets, views, and config files

### Setup Flags

| Flag | Description |
|---|---|
| `--skip-config` | Skip interactive configuration, use existing or defaults |
| `--skip-db` | Skip database creation |
| `--skip-shield` | Skip Shield installation |
| `--only-publish` | Only run the publish steps (recovery mode) |

## Default Auth

```
email:    super@admin.com
password: password
```

> Change these credentials immediately after first login.

## Create Additional Users

```bash
php spark shield:user create
```

## Upgrade Notice

After `composer update`, refresh the framework bootstrap files:
```bash
composer update
cp vendor/codeigniter4/framework/public/index.php public/index.php
cp vendor/codeigniter4/framework/spark spark
```

---

# Path Configuration

All path-related settings are centralised in `Config/HMVCPaths.php`.
Publish it to `app/Config/` to override any value:

```bash
php spark publish:config
```

Key properties (`app/Config/HMVCPaths.php` after publishing):

```php
public string $modulesDirectory = 'Modules';       // relative to APPPATH
public string $assetsDirectory  = 'assets/simpletine'; // relative to FCPATH
public string $viewsOverridePath = '';             // set after publish:views
```

Priority: `app/Config/HMVCPaths.php` > package default > `ConfigStorage`.

---

# Module Commands

## Create / Scaffold

```bash
# Create a full MVC module (prompts: Add to sidebar? [y/n])
php spark module:create Blogs
```
```
Module 'Blogs' created at App/Modules/Blogs/
Add "Blogs" to the sidebar navigation? [y/n]: _
```

```bash
# Create with AdminLTE admin template
php spark module:create Blogs --admin

# Create with Shield-based user CRUD (no migration needed — uses Shield tables)
php spark module:create Users --crud
```

```bash
# Create and immediately add to the sidebar (skips the y/n prompt)
php spark module:create Blogs --sidebar
```
```
Module 'Blogs' created at App/Modules/Blogs/
Sidebar entry for 'Blogs' published to app/Config/StnConfig.php.
```

```bash
# Create CRUD module and add dropdown sidebar entry (All / New)
php spark module:create Users --crud --sidebar
```
```
Module 'Users' created at App/Modules/Users/
Sidebar entry for 'Users' published to app/Config/StnConfig.php.
```

```bash
# Clone (copy + rename) an existing module
php spark module:copy Blogs Items
```
```
Module 'Blogs' copied to 'Items' at App/Modules/Items/
```

Each module is created with the following structure:

```
App/Modules/Blogs/
├── Config/
│   └── Routes.php
├── Controllers/
│   └── Index.php
├── Models/
│   └── Blogs.php
├── Views/
│   └── index.php
└── Database/
    ├── Migrations/
    └── Seeds/
```

## Add Files to an Existing Module

```bash
# Add a controller
php spark module:controller Blogs Categories
php spark module:controller Blogs Categories --admin   # AdminLTE template

# Add a model
php spark module:model Blogs Categories

# Add a view
php spark module:view Blogs categories
php spark module:view Blogs categories --admin
```

### Route management

```bash
# Add a route entry
php spark module:route Blogs add new "Blogs::create" --method=POST
```
```
Route 'new' [POST → Blogs::create] added to Blogs/Config/Routes.php.
```

```bash
# List all route entries
php spark module:route Blogs list
```
```
Routes for 'Blogs':
  1. GET  /                → Index::index
  2. GET  /new             → Index::new
  3. POST /new             → Blogs::create
```

```bash
# Remove a route entry
php spark module:route Blogs remove new
```
```
Route 'new' removed from Blogs/Config/Routes.php.
```

```bash
# Add Blogs to sidebar (delegates to module:sidebar publish)
php spark module:route Blogs publish
```
```
Sidebar entry for 'Blogs' published to app/Config/StnConfig.php.
```

```bash
# Remove Blogs from sidebar (delegates to module:sidebar unpublish)
php spark module:route Blogs unpublish
```
```
Sidebar entry for 'Blogs' removed from StnConfig.php.
```

## Sidebar Management

Add or remove a module's entry from the AdminLTE sidebar in `StnConfig.php`.

> **Config resolution**: `app/Config/StnConfig.php` (user-published, preferred) › package `Config/StnConfig.php` (fallback).  
> Run `php spark publish:config` first to get a user-editable copy.

### During module creation

```bash
# Prompted interactively after scaffolding (enter y/n)
php spark module:create Blogs

# Skip the prompt — add to sidebar automatically
php spark module:create Blogs --sidebar

# CRUD module with a dropdown sidebar entry (All / New)
php spark module:create Users --crud --sidebar
```

### Standalone sidebar command

```bash
# Add sidebar entry (plain link)
php spark module:sidebar Blogs publish
```
```
Sidebar entry for 'Blogs' published to app/Config/StnConfig.php.
```

```bash
# Add sidebar entry as CRUD dropdown (All / New links)
# Verifies the module's DB table exists before writing — run the migration first.
# Also auto-generates full CRUD routes in Blogs/Config/Routes.php.
php spark module:sidebar Blogs publish --crud
```
```
Table 'st_blogs' found — proceeding.
Sidebar entry for 'Blogs' published to app/Config/StnConfig.php.
CRUD routes file created: Blogs/Config/Routes.php
```

```bash
# Remove sidebar entry
php spark module:sidebar Blogs unpublish
```
```
Sidebar entry for 'Blogs' removed from StnConfig.php.
```

```bash
# Update fields on an existing parent sidebar entry (any combination of --label / --link / --icon)
php spark module:sidebar Blogs update --label="My Blogs"
php spark module:sidebar Blogs update --icon="fas fa-rss" --link="/my-blogs"
php spark module:sidebar Blogs update --label="My Blogs" --icon="fas fa-rss" --link="/my-blogs"
```
```
Sidebar entry for 'Blogs' updated in app/Config/StnConfig.php.
```

```bash
# List all children of an existing sidebar entry
php spark module:sidebar Blogs list
```
```
Sidebar children for 'Blogs':

   1. [far fa-circle        ] All Blogs            → /blogs
   2. [far fa-circle        ] New Blogs            → /blogs/create
```

```bash
# Add a custom child item to an existing parent entry
php spark module:sidebar Blogs add-child "Archive" "/blogs/archive"
php spark module:sidebar Blogs add-child "Archive" "/blogs/archive" --icon="fas fa-archive"
```
```
Child item 'Archive' → '/blogs/archive' added to 'Blogs' sidebar.
```

```bash
# Update a child item (any combination of --new-label / --link / --icon)
php spark module:sidebar Blogs update-child "All Blogs" --new-label="Archive" --link="/blogs/archive"
php spark module:sidebar Blogs update-child "Archive" --icon="fas fa-box-archive"
```
```
Child item 'All Blogs' → 'Archive' updated in 'Blogs' sidebar.
```

```bash
# Remove a child item by label
php spark module:sidebar Blogs remove-child "Archive"
```
```
Child item 'Archive' removed from 'Blogs' sidebar.
```

> `add-child` / `remove-child` / `update-child` / `list` require the parent entry to already exist — run `publish` first.

### Via `module:route`

```bash
php spark module:route Blogs publish
```
```
Sidebar entry for 'Blogs' published to app/Config/StnConfig.php.
```

```bash
php spark module:route Blogs unpublish
```
```
Sidebar entry for 'Blogs' removed from StnConfig.php.
```

## Migrations

Module migrations live in `App/Modules/<Name>/Database/Migrations/`.
They are auto-discovered by `php spark migrate` via `Config/MigrationPaths.php` (published by `publish:config`).

Or use the dedicated command to scope migrations to one module:

```bash
# Run pending migrations for a module
php spark module:migrate Blogs
```
```
Running migrations for module: Blogs
Migration 'CreateBlogsTable' applied successfully.
```

```bash
# Roll back last batch
php spark module:migrate Blogs --rollback
```
```
Rolling back last migration batch for module: Blogs
```

```bash
# Drop and re-run all
php spark module:migrate Blogs --fresh
```
```
Dropping all tables and re-running migrations for module: Blogs
```

```bash
# Show migration status
php spark module:migrate Blogs --status
```
```
+------+-------+-----------------------------------------------+
| Ran? | Group | Migration                                     |
+------+-------+-----------------------------------------------+
| Yes  | App   | 2024-07-13-101226_CreateBlogsTable            |
+------+-------+-----------------------------------------------+
```

```bash
# Migrate every discovered module
php spark module:migrate
```
```
Running migrations for all modules...
[Blogs]  Migration 'CreateBlogsTable' applied successfully.
[Items]  All migrations up to date.
```

---

# Publish Commands

```bash
# Publish AdminLTE assets to public/
php spark publish:assets

# Publish package views to app/ (enables view override / base render takeover)
php spark publish:views
php spark publish:views --path=app/Views/custom   # custom destination

# Publish config files to app/Config/
# Publishes: StnConfig.php, HMVCPaths.php, MigrationPaths.php
php spark publish:config
php spark publish:config --force   # overwrite without prompting
```

After running `publish:views`, set `viewsOverridePath` in `app/Config/HMVCPaths.php`
to point to the published directory. The `stn_view()` helper and all admin controllers
will automatically prefer the user copy over the package built-in layout.

---

# UI Configuration (`app/Config/StnConfig.php`)

Publish first: `php spark publish:config`

## Branding

```php
public string $appName         = 'My App';
public string $appLogo         = '/assets/simpletine/img/AdminLTELogo.png';
public string $footerCopyright = '&copy; 2026 My Company. All rights reserved.';
public string $footerVersion   = 'v1.4.0';  // leave empty to hide

// Redirect target for GET / and post-login. Change to your dashboard route.
public string $homeRedirect    = '/admin';
```

## Sidebar

New simplified format (v1.4+):

```php
public array $sidebars = [
    // Plain link
    ['icon' => 'fas fa-tachometer-alt', 'label' => 'Dashboard', 'link' => '/'],

    // Dropdown
    ['icon' => 'fas fa-users', 'label' => 'Users', 'link' => '#', 'children' => [
        ['icon' => 'far fa-circle', 'label' => 'All Users', 'link' => '/users'],
        ['icon' => 'far fa-circle', 'label' => 'New User',  'link' => '/users/new'],
    ]],

    // Custom link class
    ['icon' => 'fas fa-sign-out-alt', 'label' => 'Logout', 'link' => '/logout',
     'link_class' => 'nav-link bg-danger'],
];
```

> **Backward compatible** — items using the legacy `anchor`/`icon_class`/`attributes` format
> still render correctly and can coexist with new-format items in the same array.

> Use `php spark module:sidebar <module> publish|unpublish` (or `module:route <module> publish|unpublish`)
> to manage sidebar entries programmatically instead of editing this file manually.

## Navbar — Left

Same structure as `$sidebars`. The sidebar-toggle button is always prepended automatically.

```php
public array $navbarLeft = [
    ['icon' => 'fas fa-home', 'label' => 'Home', 'link' => '/'],
];
```

## Navbar — Right (feature toggles)

```php
public array $navbarRight = [
    'show_search'        => true,
    'show_notifications' => true,
    'show_messages'      => true,
    'show_fullscreen'    => true,
    'show_control_panel' => true,
    'show_user_menu'     => true,
];
```

Set any key to `false` to hide that widget entirely.

---

# Built-in Endpoints

The package registers these routes automatically via `Config/Routes.php`.

| Method | Path | Behaviour |
|--------|------|-----------|
| `GET` | `/` | Unauthenticated → redirect to `/login`. Authenticated → redirect to `StnConfig::$homeRedirect` (default `/admin`). |
| `GET` | `/profile` | Password-change form (AdminLTE 3). Requires auth. |
| `POST` | `/profile` | Validates `current_password` / `new_password` / `confirm_password` and updates via Shield. |

Publish `app/Config/StnConfig.php` and change `$homeRedirect` to control where `/` sends authenticated users.

---

# Route Group Example

```php
// App/Modules/Blogs/Config/Routes.php
$routes->group('blogs', ['namespace' => '\\App\\Modules\\Blogs\\Controllers'], function ($routes) {
    $routes->get('/', 'Index::index');
    $routes->get('new', 'Index::new');
});
```

---

# PHPCS

```bash
composer run fix
```

---

# PSR-4 Autoload

Register your modules namespace in `app/Config/Autoload.php`:

```php
public $psr4 = [
    APP_NAMESPACE => APPPATH,
    'App\\Modules' => APPPATH . 'Modules',  // covers all modules
];
```

---

## Changelog

For detailed changes in each version, refer to the [Changelog](changelog/CHANGELOG-1.4.md).