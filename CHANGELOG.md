# Change Log

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
