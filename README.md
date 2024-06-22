# Codeigniter4-HMVC

[![Official Website](https://img.shields.io/badge/Official_Website-Visit-yellow)](https://simpletine.com)  
[![YouTube Channel](https://img.shields.io/badge/YouTube_Channel-Subscribe-FF0000)](https://www.youtube.com/channel/UCRuDf31rPyyC2PUbsMG0vZw) 
 
### Prerequisites
1. PHP 7.4 or above
2. Composer required
2. CodeIgniter 4.4.8

### Installation Guide
For the guideline, see the [documentation](/INSTALLING.md) 

---
## Generate New Module 
```bash
php spark make:module [module] 
```

### Example
Create a blog module
```bash
php spark make:module blogs
```

###  Result Directory
    App 
    ├── Config       
    │   └── Routes.php (Added group called blogs)
    ├── Modules      
    │   └── Blogs
    │       ├──  Controllers
    │           └──  Blogs.php
    │       ├──  Models
    │           └──  BlogsModel.php
    │       └──  Views
    │           └──  index.php
    └── ...  

### Route Group
After generate `Blogs` Module, add a route group for the module at `App\Config\Routes.php`
```php
$routes->group(
    'blogs', ['namespace' => '\Modules\Blogs\Controllers'], function ($routes) {
        $routes->get('/', 'Blogs::index');
    }
);
```

## PSR4
At `App/Config/Autoload.php`, you can configure your custom namespace:
```php
public $psr4 = [
    // Sample
    "$module" => APPPATH . "$module",

    // Base on Example above
    "Blogs" => APPPATH . "Modules/Blogs", // Example 
    // ...
];
```
---
### Contribute
To contribute to this repository and extend its architectural capabilities or you find an issue, follow these [steps](/CONTRIBUTE.md)