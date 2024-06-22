## Initial

Clone project to your project root folder
```bash
composer create-project simpletine/codeigniter4-hmvc ci4_hmvc --stability=dev
```
Or
```bash
git clone https://github.com/Simpletine/CodeIgniter4-HMVC.git ci4_hmvc
```
Then
```bash
cd ci4_hmvc
``` 

Copy some require file to root folder (Upgrading to v4.4.8)
```bash
composer update
cp vendor/codeigniter4/framework/public/index.php public/index.php
cp vendor/codeigniter4/framework/spark spark
```

Copy `env` file
```bash
cp env .env
```

Run the app, using different port, add options `--port=9000`
```bash
php spark serve
```

---
#### Installation Issue
If you're facing any installation issue, [open a discussion](https://github.com/Simpletine/CodeIgniter4-HMVC/discussions) in community
