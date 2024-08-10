[![Official Website](https://img.shields.io/badge/Official_Website-Visit-yellow)](https://simpletine.com)   [![YouTube Channel](https://img.shields.io/badge/YouTube_Channel-Subscribe-FF0000)](https://www.youtube.com/channel/UCRuDf31rPyyC2PUbsMG0vZw) 
 
# Codeigniter 4 HMVC 
This repository features a modular HMVC (Hierarchical Model-View-Controller) architecture for CodeIgniter 4, integrated with the official authentication system, CodeIgniter 4 Shield. It also includes a comprehensive Admin Dashboard built with AdminLTE, providing a robust foundation for scalable and secure web applications.


# Installation Guide

Create a project
```bash
composer create-project simpletine/codeigniter4-starter ci4_hmvc --stability=dev
```

Copy `env` file and setup database environment
```bash
cp env .env
```

Run the app, using different port, add options `--port=9000`
```bash
php spark serve
```

## Configuration
Install HMVC package

```bash
composer require simpletine/hmvc-shield
```

Setup Command Line

```bash
php spark simpletine:setup
```

## Default Auth

```bash
email: super@admin.com
password: password
```

## Create User
Use command line to create a new user
```bash
php spark shield:user create
```

## Notice
If you are using old versions, after composer update, you need to update the `index.php` and `spark` file to root folder (Upgrading to v4.4.8)
```bash
composer update
cp vendor/codeigniter4/framework/public/index.php public/index.php
cp vendor/codeigniter4/framework/spark spark
```

# Module Commands
Commands available and funcationality

## General
Create a new module named `Blogs`
```bash
php spark module:create Blogs 
```

Clone a existing module of `Blogs` and renamed to `Items`
```bash
php spark module:copy Blogs Items 
```

Create a controller to module `Blogs` named `Categories.php`
```bash
php spark module:controller Blogs Categories 
```

Create a model to module `Blogs` named `Categories.php`
```bash
php spark module:model Blogs Categories 
```

## Publisher
Publish require assets to `public` folder
```bash
php spark publish:assets 
```

Publish views with `AdminLTE` to `COnfig/Auth.php`
```bash
php spark publish:views 
```

## Admin
Additional options for the commands

Create a new controller and view to `Admin` module with `AdminLTE`
```bash
php spark module:controller Admin Users --admin 
```

##  Modules Directory
    App 
    ├── Modules      
    │   └── Blogs
    │       ├──  Config
    │           └──  Routes.php
    │       ├──  Controllers
    │           └──  Blogs.php
    │       ├──  Models
    │           └──  Blogs.php
    │       └──  Views
    │           └──  index.php
    └── ...  

### Route Group
Add new subroutes to `blogs` named `new` with method name

```php
$routes->get('new', 'Blogs::new');
```

Sample of routes group after new subroutes
```php
$routes->group(
    'blogs', ['namespace' => '\Modules\Blogs\Controllers'], function ($routes) {
        $routes->get('/', 'Blogs::index');
        $routes->get('new', 'Blogs::new');
    }
);
```

# PHPCS

Default instance of containing all rules applicable for the CodeIgniter organization
```bash
composer run fix
```

# PSR4
At `App/Config/Autoload.php`, you can configure your custom namespace:
```php
public $psr4 = [
    "Blogs" => APPPATH . "Modules/Blogs", // Example 
    // ...
];
```