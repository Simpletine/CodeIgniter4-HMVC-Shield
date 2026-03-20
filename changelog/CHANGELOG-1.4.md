# Changelog

---

## v1.4.0

### Summary
v1.4.0 is a major feature release covering modular command infrastructure, path unification, migration auto-discovery, full UI configurability (branding, navbar, sidebar restructure), streamlined onboarding via `simpletine:setup`, and new built-in auth/profile endpoints.

---

### New Commands

| Command | Description |
|---|---|
| `module:route <module> <action>` | Add, list, or remove route entries for a module |
| `module:view <module> <name>` | Generate a view file inside a module |
| `module:migrate [<module>]` | Scope migrations to one module, or migrate all modules |

`module:migrate` options: `--rollback`, `--fresh`, `--status`.

---

### New Classes & Config Files

#### `BaseModuleCommand` (`Commands/BaseModuleCommand.php`)
Abstract base for all `module:*` commands. Centralises path resolution, template loading, namespace building, and `ConfigStorage` access.

#### `ConfigStorage` (`Commands/ConfigStorage.php`)
Persistent JSON config at `WRITEPATH/simpletine_config.json`. Stores: `namespace_base`, `class_prefix`, `table_prefix`, `modules_directory`, `base_controller`, `base_model`.

#### `HMVCPaths` (`Config/Paths.php`)
Single source of truth for all path-related settings.

```php
public string $modulesDirectory   = 'Modules';           // relative to APPPATH
public string $assetsDirectory    = 'assets/simpletine'; // relative to FCPATH
public string $viewsOverridePath  = '';                  // set after publish:views
```

Priority: `app/Config/HMVCPaths.php` (user) > package default > `ConfigStorage` fallback.
Published via `publish:config`.

#### `MigrationPaths` (`Config/MigrationPaths.php`)
Auto-discovers `App/Modules/*/Database/Migrations/` via `glob` at construction time.
Registers namespaces and paths so `php spark migrate` processes module migrations with no extra flags.
Published via `publish:config`.

---

### New Global Helper

#### `stn_view(string $view, array $data, array $options): string` (`Common.php`)
Resolves admin layout views with override support.

Resolution order:
1. `HMVCPaths::$viewsOverridePath` — user-published copy
2. Package built-in `Views/` directory

All generated admin controllers call `stn_view('index', $data)` instead of `view(HMVCSHIELDVIEWS . 'index', $data)`.

---

### New Seed

#### `Database/Seeds/UsersSeeder.php`
Idempotent admin user seed (no-op if `super@admin.com` already exists). Called during `simpletine:setup`.

---

### `simpletine:setup` Enhancements

- Interactive collection of: namespace base, class prefix, table prefix, modules directory, base controller, base model.
- Input validation for all fields.
- Existing config detection with use-or-replace prompt.
- New step: scaffold **Users** CRUD module via `module:create users --admin` (replaces legacy `members`).
- Setup completion prints default login credentials.
- Flags:

| Flag | Description |
|---|---|
| `--skip-config` | Skip interactive configuration |
| `--skip-db` | Skip database creation |
| `--skip-shield` | Skip Shield installation |
| `--only-publish` | Only run publish steps (recovery mode) |

---

### UI Configuration (`Config/StnConfig.php`)

#### Branding (new)

```php
public string $appName        = 'SimpleTine';
public string $appLogo        = '/assets/simpletine/img/AdminLTELogo.png';
public string $footerCopyright = '&copy; 2026 SimpleTine. All rights reserved.';
public string $footerVersion  = '';  // leave empty to hide
```

#### Sidebar — Simplified Format (new, backward compatible)

New format:
```php
['icon' => 'fas fa-users', 'label' => 'Users', 'link' => '#', 'children' => [
    ['icon' => 'far fa-circle', 'label' => 'All Users', 'link' => '/users'],
]]
```

Legacy format (still supported, detected via `anchor` key):
```php
['label' => '...', 'attributes' => [...], 'icon_class' => '...', 'anchor' => [...]]
```

New and legacy items can coexist in the same `$sidebars` array.
Default items changed from `members` to `users`.

#### Navbar Left (new)

```php
public array $navbarLeft = [
    ['icon' => 'fas fa-home', 'label' => 'Home', 'link' => '/'],
];
```

Sidebar-toggle button is always prepended automatically.

#### Navbar Right Toggles (new)

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

---

### Command Enhancements

#### `module:create`
- Now creates `Database/Migrations/` and `Database/Seeds/` subdirectories.
- Reads path and namespace from `HMVCPaths` > `ConfigStorage`.
- `--admin` flag uses AdminLTE controller template.
- `--crud` flag generates Shield-based user CRUD (controller / 3 views / CRUD routes); operates directly on Shield native tables — no migration generated.
- `--sidebar` flag adds the module to `StnConfig::$sidebars` immediately after scaffolding.
- When `--sidebar` is absent, the command prompts interactively: `Add "<module>" to the sidebar navigation? [y/n]`.
- If `--crud` is also set, the sidebar entry is generated as a dropdown (All / New).

#### `module:sidebar` (new)
Standalone command for sidebar management.

```bash
# Add sidebar entry (plain link)
php spark module:sidebar Blogs publish

# Add sidebar entry as CRUD dropdown (All / New links)
php spark module:sidebar Blogs publish --crud

# Remove sidebar entry
php spark module:sidebar Blogs unpublish

# Update fields on an existing parent sidebar entry (any combination of --label / --link / --icon)
php spark module:sidebar Blogs update --label="My Blogs"
php spark module:sidebar Blogs update --icon="fas fa-rss" --link="/my-blogs"
php spark module:sidebar Blogs update --label="My Blogs" --icon="fas fa-rss" --link="/my-blogs"

# List all child items of an existing parent entry
php spark module:sidebar Blogs list

# Add a custom child item to an existing parent entry
php spark module:sidebar Blogs add-child "Archive" "/blogs/archive"
php spark module:sidebar Blogs add-child "Archive" "/blogs/archive" --icon="fas fa-archive"

# Update a child item (any combination of --new-label / --link / --icon)
php spark module:sidebar Blogs update-child "All Blogs" --new-label="Archive" --link="/blogs/archive"
php spark module:sidebar Blogs update-child "Archive" --icon="fas fa-box-archive"

# Remove a child item by label
php spark module:sidebar Blogs remove-child "Archive"
```

- Modifies `app/Config/StnConfig.php` if present (user-published); falls back to package `Config/StnConfig.php`.
- Uses bracket-counting to safely insert/locate entries — no regex fragility on publish/unpublish/update.
- Duplicate-safe on `publish`: skips silently when the entry already exists.
- `update` locates the parent entry via its `// ModuleName` comment marker; no re-publish required.
- `update-child` locates the child by its current `label`; at least one of `--new-label`, `--link`, `--icon` must be given.

#### `module:route` — Sidebar actions
Two new actions added: `publish` and `unpublish`.

```bash
php spark module:route Blogs publish    # add Blogs sidebar entry
php spark module:route Blogs unpublish  # remove Blogs sidebar entry
```

- These actions do **not** require a `Routes.php` file to exist.
- Delegates to `BaseModuleCommand::publishSidebar()` / `unpublishSidebar()`.

#### `module:controller`
- Dynamic namespace and path from `ConfigStorage`.
- `--admin` flag for AdminLTE template.

#### `module:model`
- Reads table prefix from `ConfigStorage`.

#### `module:copy`
- Resolves source/destination paths dynamically from configuration.

#### `BaseModuleCommand` — Sidebar helpers
Five protected methods shared by all `module:*` commands (three new in this release, two added in this update):

| Method | Description |
|---|---|
| `getStnConfigPath()` | Resolves StnConfig path (app > package) |
| `publishSidebar(string $module, bool $crud)` | Inserts sidebar entry into `$sidebars` |
| `unpublishSidebar(string $module)` | Removes sidebar entry from `$sidebars` |
| `updateSidebar(string $module, ?string $label, ?string $link, ?string $icon)` | Updates `label`/`link`/`icon` on an existing parent entry in-place |
| `updateSidebarChild(string $module, string $currentLabel, ?string $newLabel, ?string $link, ?string $icon)` | Updates fields on a child item matched by its current label |
| `addSidebarChild(string $module, string $label, string $link, string $icon)` | Appends a child item to an existing parent entry |
| `removeSidebarChild(string $module, string $label)` | Removes a child item by label |
| `listSidebarChildren(string $module)` | Prints all child items of a parent entry |

#### `BaseModuleCommand::getModulesDirectory()`
- Priority: `HMVCPaths` config > `ConfigStorage` > default `'Modules'`.

---

### Publish Command Enhancements

#### `publish:config`
- Publishes three files: `StnConfig.php`, `HMVCPaths.php`, `MigrationPaths.php`.
- `--force` to overwrite all without per-file confirmation.

#### `publish:views`
- Copies full `Views/` directory (not just Auth.php patching).
- `--path=<dir>` to specify custom destination.
- `--backup` to preserve originals before patching Auth.php.

#### `publish:assets`
- Target path detection and overwrite confirmation.

---

### Infrastructure Fixes & Changes

#### `Config/Routes.php`
- Module discovery `glob` reads `config('HMVCPaths')->modulesDirectory` (was hardcoded `'Modules'`).

#### `Views/index.php`
- **Fixed**: `config('StnConfig.php')` → `config('StnConfig')` (`.php` suffix caused null return).
- Sidebar renderer extracted to `_stn_render_nav_item()` — handles new and legacy formats.
- Navbar left items rendered from `$stn_config->navbarLeft`.
- Navbar right widgets conditionally rendered via `$stn_config->navbarRight` toggles.
- Brand logo/name from `$stn_config->appLogo` / `$stn_config->appName`.
- Footer copyright/version from `$stn_config->footerCopyright` / `$stn_config->footerVersion`.
- User dropdown in navbar with email display.
- All outputs escaped with `esc()`.

#### `Commands/Views/controller.admin.tpl.php`
- `render()` changed to `stn_view('index', $this->data)` (was `view(HMVCSHIELDVIEWS . 'index', ...)`).

---

### Files Changed

**New Files:**
- `Config/Paths.php` — class `HMVCPaths`
- `Config/MigrationPaths.php`
- `Commands/BaseModuleCommand.php`
- `Commands/ConfigStorage.php`
- `Commands/ModuleRoute.php`
- `Commands/ModuleView.php`
- `Commands/ModuleMigrate.php`
- `Database/Seeds/UsersSeeder.php`

**Modified Files:**
- `Config/Routes.php`
- `Config/StnConfig.php`
- `Commands/SimpleTine.php`
- `Commands/SimpleTineSetup.php`
- `Commands/ModuleCreate.php`
- `Commands/ModuleController.php`
- `Commands/ModuleModel.php`
- `Commands/ModuleCopy.php`
- `Commands/BaseModuleCommand.php`
- `Commands/PublishAssets.php`
- `Commands/PublishConfig.php`
- `Commands/PublishViews.php`
- `Commands/Views/controller.admin.tpl.php`
- `Common.php`
- `Views/index.php`

---

## v1.3.0 - 2024-08-10

### Features
- Sidebar nav items customization
- Allow Custom CSS and JavaScript file injection
- New spark command `simpletine:setup` for quick installation

### Fixed
- Fixed `module:create` missing replacer issue

### Changes
- Removed fixed logout button from sidebar
- Added logout nav item to `StnConfig.php`
- Updated assets directory

---

## v1.2.0-beta - 2024-07-27

### Major Update
- Transitioned from manual installation to Composer-based installation (`composer require simpletine/hmvc-shield`).

---

## v1.2.0-alpha - 2024-07-16

### Fixed
- Fix: Template file not found: `controller.new.tpl.php`

### Features
- Implemented CodeIgniter Shield
- Implemented AdminLTE
- Coding Standard enforcement

---

## v1.1.0 - 2024-07-13

### Changes
- Renamed command `make:module` → `module:create`

### Features
- Module `Config/` folder for route management
- `module:controller`, `module:model`, `module:copy` commands

---

## v1.0.0 - 2024-06-22

- Initial release
- `php spark make:module blogs` command

---

### New Features

#### Path Configuration (`Config/HMVCPaths.php`)
- Introduced `HMVCPaths` config class as the single source of truth for all path-related settings.
- Properties: `$modulesDirectory`, `$moduleViewsDirectory`, `$assetsDirectory`, `$viewsOverridePath`.
- Priority chain: `app/Config/HMVCPaths.php` (user override) > package default > `ConfigStorage` fallback.
- Published to `app/Config/` via `publish:config`.

#### Module Migration Auto-Discovery (`Config/MigrationPaths.php`)
- New `MigrationPaths` config class auto-discovers `Database/Migrations/` directories across all modules via `glob`.
- Enables `php spark migrate` to process module migrations without extra flags.
- Published to `app/Config/` via `publish:config`.

#### `module:migrate` Command
- New `php spark module:migrate <name>` command scopes migrations to a single module.
- Options: `--rollback`, `--fresh`, `--status`.
- Omit module name to migrate every discovered module.

#### `stn_view()` Helper (`Common.php`)
- New global helper function resolves admin layout views.
- Resolution order: `viewsOverridePath` (user-published) → package built-in `Views/`.
- Replaces direct `view(HMVCSHIELDVIEWS . 'index', ...)` calls in generated controllers.

#### Users Module Default Scaffold
- `simpletine:setup` now scaffolds a **Users** CRUD module (replaces legacy `members`).
- Calls `module:create users --admin` interactively during setup.
- New `Database/Seeds/UsersSeeder.php` provides idempotent seed (skips if admin user already exists).

#### Branding Configuration
- `StnConfig` gains: `$appName`, `$appLogo`, `$footerCopyright`, `$footerVersion`.
- Navbar brand and footer are fully driven by these values — no view editing required.

#### Navbar Configuration
- `StnConfig::$navbarLeft` — dynamic left-side items (same structure as sidebar).
- `StnConfig::$navbarRight` — toggle map for built-in right-side widgets: `show_search`, `show_notifications`, `show_messages`, `show_fullscreen`, `show_control_panel`, `show_user_menu`.

---

### Enhancements

#### Sidebar Simplified Format
- New format: `['icon', 'label', 'link', 'children?', 'link_class?']`.
- Replaces the verbose `attributes` / `anchor` / `icon_class` / `dropdown_items` structure.
- **Backward compatible** — legacy format items are still rendered correctly via key-detection (`anchor` key present = legacy path).
- Default `$sidebars` updated to use `users` instead of `members`.

#### `publish:config` — Multi-file
- Now publishes three files: `StnConfig.php`, `HMVCPaths.php`, `MigrationPaths.php`.
- Added `--force` flag to overwrite without per-file confirmation.

#### `publish:views` — Full Directory Copy
- Extended beyond Auth.php patching to copy the entire `Views/` directory.
- `--path=` option to specify a custom destination (default: `app/Views/simpletine/`).
- Auto-updates `viewsOverridePath` in the published `HMVCPaths.php`.

#### `module:create` — Database Directories
- Scaffold now creates `Database/Migrations/` and `Database/Seeds/` subdirectories per module.

#### Root Auth + Profile (new built-in endpoints)
- `GET /` — `Controllers/HomeController`: unauthenticated users redirected to `/login`; authenticated users redirected to `StnConfig::$homeRedirect` (default `/admin`).
- `GET /profile` — `Controllers/ProfileController`: AdminLTE 3 password-change card. Requires auth.
- `POST /profile` — validates `current_password` / `new_password` / `confirm_password`; updates via Shield `Passwords` service.
- `StnConfig::$homeRedirect` property (default `'/admin'`) added — publish `app/Config/StnConfig.php` to override.
- The existing user-menu dropdown in `Views/index.php` already links to `/profile` — no view change needed.

#### `BaseModuleCommand::getModulesDirectory()`
- Reads from `HMVCPaths` config first, falls back to `ConfigStorage`, then to `'Modules'`.

#### `Config/Routes.php`
- `glob` path reads `config('HMVCPaths')->modulesDirectory` instead of hardcoded `'Modules'`.

#### `Views/index.php`
- Fixed incorrect `config('StnConfig.php')` call → `config('StnConfig')`.
- Navbar left/right rendered from `StnConfig` properties.
- Sidebar renderer extracted to `_stn_render_nav_item()` helper (supports new + legacy formats).
- Brand logo and name read from `StnConfig`.
- Footer copyright and version read from `StnConfig`.
- User dropdown in navbar (show_user_menu toggle).

#### `controller.admin.tpl.php`
- Generated `render()` method changed from `view(HMVCSHIELDVIEWS . 'index', ...)` to `stn_view('index', ...)`.

#### `SimpleTineSetup`
- `executeScaffoldUsers()` step added after Shield setup.
- Setup completion message now displays default login credentials.

---

### Files Changed

**New Files:**
- `Config/Paths.php` (class `HMVCPaths`)
- `Config/MigrationPaths.php`
- `Commands/ModuleMigrate.php`
- `Commands/ModuleSidebar.php`
- `Commands/Views/controller.crud.tpl.php`
- `Commands/Views/view.crud.index.tpl.php`
- `Commands/Views/view.crud.create.tpl.php`
- `Commands/Views/view.crud.edit.tpl.php`
- `Commands/Views/route.crud.tpl.php`
- `Controllers/HomeController.php`
- `Controllers/ProfileController.php`
- `Database/Seeds/UsersSeeder.php`
- `Views/profile_form.php`

**Modified Files:**
- `Config/Routes.php` (root + profile routes)
- `Config/StnConfig.php` (`$homeRedirect` property)
- `Commands/BaseModuleCommand.php` (`getStnConfigPath`, `publishSidebar`, `unpublishSidebar`, `updateSidebar`, `updateSidebarChild`, `addSidebarChild`, `removeSidebarChild`, `listSidebarChildren`)
- `Commands/ModuleSidebar.php` (`update`, `update-child` actions; `--label`, `--link`, `--new-label` options)
- `Commands/ModuleCreate.php` (`--crud`, `--sidebar` options + post-scaffold prompt)
- `Commands/ModuleRoute.php` (`publish`/`unpublish` actions)
- `Commands/PublishConfig.php`
- `Commands/PublishViews.php`
- `Commands/SimpleTineSetup.php`
- `Commands/Views/controller.admin.tpl.php`
- `Common.php`
- `Views/index.php`
- `README.md` (usage + output blocks for all sidebar-related commands)

---

## Version 1.4

### Summary
Version 1.4 introduces significant improvements to the HMVC Shield framework, focusing on modular command enhancements, configuration management, and error handling. This release adds new base classes, improves existing commands, and ensures better user interaction and validation.

---

### New Features

#### BaseModuleCommand
- Introduced `BaseModuleCommand` to centralize shared logic for module-related commands.
- Handles directory parsing, configuration reading, and path construction.

#### ConfigStorage
- Added `ConfigStorage` for managing HMVC configuration preferences.
- Stores settings such as namespace base, class prefix, table prefix, and module directory.

#### New Commands
- **ModuleRoute**: Generates route files for specified modules.
- **ModuleView**: Generates view files for specified modules.

---

### Enhancements

#### SimpleTine
- Simplified logic by leveraging `BaseModuleCommand`.
- Removed redundant ASCII art display.

#### SimpleTineSetup
- Added interactive configuration collection (namespace, prefix, directory).
- Implemented input validation for namespace, class name, table prefix, and directory name.
- Enhanced error handling for database setup, Shield installation, and publishing steps.
- Introduced new flags:
  - `--skip-db`: Skip database creation.
  - `--skip-shield`: Skip Shield installation.
  - `--only-publish`: Only execute publishing steps.

#### Module Commands
- **ModuleCreate**: Dynamically determines directory and namespace from `ConfigStorage`.
- **ModuleController**: Uses dynamic namespace and path; added template selection.
- **ModuleModel**: Reads table prefix from `ConfigStorage`.
- **ModuleCopy**: Resolves paths dynamically from configuration.

#### Publish Commands
- **PublishAssets**: Added target path detection and overwrite confirmation.
- **PublishViews**: Improved selective overwrite and error recovery.
- **PublishConfig**: Minor adjustments to leverage `BaseModuleCommand` methods.

---

### Technical Changes
- Centralized configuration management via `ConfigStorage`.
- Improved error handling with `try/catch` blocks for all critical steps.
- Enhanced user interaction with detailed prompts and validation.

---

### Files Changed

**New Files:**
- `Commands/BaseModuleCommand.php`
- `Commands/ConfigStorage.php`
- `Commands/ModuleRoute.php`
- `Commands/ModuleView.php`

**Modified Files:**
- `Commands/SimpleTine.php`
- `Commands/SimpleTineSetup.php`
- `Commands/ModuleCreate.php`
- `Commands/ModuleController.php`
- `Commands/ModuleModel.php`
- `Commands/ModuleCopy.php`
- `Commands/PublishAssets.php`
- `Commands/PublishViews.php`
- `Commands/PublishConfig.php`