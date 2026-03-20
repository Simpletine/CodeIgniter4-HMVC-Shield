# Change Log

## v1.4.0

### New Commands
- `module:route` — generate/manage route entries for a module
- `module:view` — generate view files for a module
- `module:migrate` — run, roll back, or check migration status scoped to a single module; omit name to operate on all modules
- `module:sidebar` — unified sidebar manager: `publish`, `unpublish`, `update`, `add-child`, `remove-child`, `update-child`, `list`

### New Configuration Classes
- `HMVCPaths` (`Config/Paths.php`) — unified path config (`$modulesDirectory`, `$assetsDirectory`, `$viewsOverridePath`); user override via `app/Config/HMVCPaths.php`
- `MigrationPaths` (`Config/MigrationPaths.php`) — auto-discovers `Database/Migrations/` across all modules so `php spark migrate` picks them up automatically
- `BaseModuleCommand` — shared base class for all `module:*` commands
- `ConfigStorage` — persistent JSON config for namespace, prefix, and directory preferences

### Features
- `simpletine:setup` interactive wizard: namespace base, class prefix, table prefix, modules directory, base controller/model
- `simpletine:setup` now scaffolds a default **Users** CRUD module (replaces legacy `members`)
- Branding config in `StnConfig`: `$appName`, `$appLogo`, `$footerCopyright`, `$footerVersion`
- Navbar left-side dynamic items via `StnConfig::$navbarLeft`
- Navbar right-side feature toggles via `StnConfig::$navbarRight` (`show_search`, `show_notifications`, `show_messages`, `show_fullscreen`, `show_control_panel`, `show_user_menu`)
- Sidebar simplified format: `['icon', 'label', 'link', 'children?']` — backward compatible with legacy `anchor`/`icon_class`/`dropdown_items` format
- `stn_view()` global helper — resolves admin layout with user-published override support
- Root path `/` enforces auth; redirects to `StnConfig::$homeRedirect` (default `/admin`) when logged in
- `/profile` route added — AdminLTE 3 password-change card, no other user data exposed
- `StnConfig::$homeRedirect` property (default `/admin`) — configurable post-login redirect
- Built-in `Controllers/HomeController` and `Controllers/ProfileController`
- `Views/profile_form.php` — AdminLTE 3 password-change card view

### Changes
- `module:create` now creates `Database/Migrations/` and `Database/Seeds/` subdirectories
- `module:create` gains `--sidebar` flag and post-scaffold interactive prompt to add module to sidebar; `--crud` flag also accepted
- `module:create` gains `--crud` flag — generates Shield user CRUD (controller / 3 views / routes) without creating a migration
- `module:route` gains `publish` and `unpublish` actions for sidebar management
- `publish:config` now publishes three files: `StnConfig.php`, `HMVCPaths.php`, `MigrationPaths.php`; added `--force` flag
- `publish:views` extended to copy the full `Views/` directory; added `--path=` option
- `BaseModuleCommand` gains `publishSidebar()` and `unpublishSidebar()` helpers
- `module:sidebar` refactored — now supports `add-child`, `remove-child`, `list` sub-actions in addition to `publish` / `unpublish`
- `module:sidebar` gains `update` action: updates `label`/`link`/`icon` on an existing parent entry in-place via `--label`, `--link`, `--icon` options; no unpublish / re-publish required
- `module:sidebar` gains `update-child` action: locates a child item by its current label and updates any combination of `--new-label`, `--link`, `--icon`
- `module:sidebar publish --crud` now verifies the module's DB table exists before writing the sidebar entry (fails fast if the migration has not been run); auto-generates full CRUD routes in the module's `Config/Routes.php` after a successful publish
- `BaseModuleCommand` gains `writeCrudRoutes()`, `addSidebarChild()`, `removeSidebarChild()`, `listSidebarChildren()`, `updateSidebar()`, `updateSidebarChild()` helpers
- `BaseModuleCommand::getModulesDirectory()` reads from `HMVCPaths` first, falls back to `ConfigStorage`
- `Config/Routes.php` module glob uses `config('HMVCPaths')->modulesDirectory` instead of hardcoded `'Modules'`
- `Views/index.php` fully rewritten: fixed `config('StnConfig')` call, all sidebar/navbar/brand/footer driven by `StnConfig`, new + legacy sidebar formats supported
- Generated admin controllers use `stn_view('index', ...)` instead of `view(HMVCSHIELDVIEWS . 'index', ...)`
- Default `$sidebars` updated from `members` to `users`
- Setup flags: `--skip-config`, `--skip-db`, `--skip-shield`, `--only-publish`

### Fixed
- `Views/index.php`: incorrect `config('StnConfig.php')` call (with `.php` extension) replaced with `config('StnConfig')`

---

## v1.3.0 - 2024-08-10

### Features
- Sidebar nav items customization
- Allow Custom CSS file and JavaScript files
- New spark command `simpletine:setup` to achieve quick installation 

### Fixed
- Fixed Command `module:create` missing replacer issue

### Changes

- Remove fixed logout button from sidebar
- Added logout nav item to `StnConfig.php`
- Update assets directory

## v1.2.0-beta - 2024-07-27

### Major Update
- Transitioned from traditional manual installation to Composer installation.
  - This change enhances stability in version control.
  - You can now install the package via Composer using: `composer require simpletine/hmvc-shield`.
  - This update significantly speeds up the setup process.

## v1.2.0-alpha - 2024-07-16

### Fixed
- Fix: Template file not found: `controller.new.tpl.php`

### Changes

- Update README.md and CHANGELOG.md

### Features

- Implement CodeIgniter Shield
- Implement AdminLTE
- Coding Standard


## v1.1.0 - 2024-07-13

### Changes

- Renamed command `make:module` to `module:create` for better consistency

### Features

- Added module `config` folder for management of routes within configs ([#5](https://github.com/Simpletine/CodeIgniter4-HMVC/issues/5))
- `module:controller`: Create a controller in a specified module ([#4](https://github.com/Simpletine/CodeIgniter4-HMVC/issues/4))
- `module:model`: Create a model in a specified module ([#3](https://github.com/Simpletine/CodeIgniter4-HMVC/issues/3))
- `module:copy`: Copy an existing module and rename key elements ([#2](https://github.com/Simpletine/CodeIgniter4-HMVC/issues/2))

## v1.0.0 - 2024-06-22

- Initial release
- Implemented `php spark make:module blogs` command for creating new modules called `blogs`
