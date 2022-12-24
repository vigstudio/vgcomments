#  VgComments Package
Comments package for applications. Using this package, you can create and associate comments with Eloquent models.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/vigstudio/vgcomments.svg?style=flat-square)](https://packagist.org/packages/vigstudio/vgcomments)
[![Total Downloads](https://img.shields.io/packagist/dt/vigstudio/vgcomments.svg?style=flat-square)](https://packagist.org/packages/vigstudio/vgcomments)

## _Features_
- [x] Add comments to any model
- [x] Multiple comment systems on the same page
- [x] Multiple auth guards
- [x] Image and File upload support
- [x] Drag and drop, copy and paste upload files support
- [x] reCaptcha v3 support
- [x] Emoji support
- [x] Markdown support
- [x] NSFW image upload check support

## _Pending Features_
- [ ] Allow guest to comment
- [ ] Admin panel
- [ ] Mention user with @
- [ ] Emoji Suggestion Popup
- [ ] Delete Report comment
- [ ] Ratting system
- [ ] Toolbar for comment
- [ ] Comment history
- [ ] Show Nested comments
- [ ] Unit test

## _Packages_
- [s9e/TextFormatter](https://github.com/s9e/TextFormatter)
- [laravolt/avatar](https://github.com/laravolt/avatar)
- [Laravel StopForumSpam](https://github.com/nickurt/laravel-stopforumspam)
- [Intervention Image](https://image.intervention.io/v2s)


## _Prerequisites_
- [Composer](https://getcomposer.org/download/)
- [Laravel 9.x](https://laravel.com/docs/9.x/installation)

### _Installation_
```bash
composer require vigstudio/vgcomments
```

## _Usage_
**Publish the assets files with:**
```bash
php artisan vendor:publish --tag=vgcomment-assets
```

**You can publish the config with:**
```bash
php artisan vendor:publish --tag=vgcomment-config
```
Edit prefix route in `config/vgcomment.php` file.
```php
    /*
    |--------------------------------------------------------------------------
    | Route Prefix
    |--------------------------------------------------------------------------
    |
    | This is the URI path where VgComment will be accessible from. Feel free to
    | change this path to anything you like.
    |
    */
    'prefix' => 'vgcomment',
```

Edit connection name in `config/vgcomment.php` file.
```php
    /*
    |--------------------------------------------------------------------------
    | Database Connection
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all of your database work.
    |
    */
    'connection' => env('DB_CONNECTION', 'mysql'),
```

Edit table names in `config/vgcomment.php` file.

```php
    /*
    |--------------------------------------------------------------------------
    | Name of Tables in Database
    |--------------------------------------------------------------------------
    |
    | This is the name of the table that will be created by the migration and
    | used by the Comment model shipped with this package.
    |
    | "comments"    : Comments Table
    | "files"       : Files Attachment Table
    | "reactions"   : Reactions Table
    | "reports"     : Reports Table
    | "settings"    : Settings Table
    |
    */
    'table' => [
        'comments' => 'vgcomments',
        'files' => 'vgcomment_files',
        'reactions' => 'vgcomment_reactions',
        'reports' => 'vgcomment_reports',
        'settings' => 'vgcomment_settings',
    ],
```

Config Column or Attribute User Model in `config/vgcomment.php` file.
```php
        /*
    |--------------------------------------------------------------------------
    | Column of User Table for get Data
    |--------------------------------------------------------------------------
    |
    | This is the setting for column of user table for get data.
    | "user_column_name"  : Column name for get name user
    | "user_column_email" : Column name for get email user
    | "user_column_url"   : Column name for get url user
    |
    */
    'user_column_name' => 'name',
    'user_column_email' => 'email',
    'user_column_url' => 'url',
    'user_column_avatar_url' => 'avatar_url',
```

Set moderation user in `config/vgcomment.php` file.
```php
        /*
    |--------------------------------------------------------------------------
    | Users Manager Comments
    |--------------------------------------------------------------------------
    |
    | This is the setting for users manager comments.
    | 'guard' => [user_id]
    |
    | Example:
    | 'web' => [1, 2, 3]
    | 'api' => [1, 2, 3]
    |
    */
    'moderation_users' => [
        'web' => [1],
    ],
```

**Run the migrate command to create the necessary tables:**
Before running the migrate command, you can edit the `config/vgcomment.php` file to change the table names.
```bash
php artisan migrate
```

**Additionally you may want to clear the config, cache, etc:**
```bash
php artisan optimize:clear
```

# Fontend for VgComments Package

## _[Livewire Comments Packages](https://vgcomment.netlify.app/livewire-comments/index.html)_
Comments package for applications using Livewire. Using this package, you can create and associate comments with Eloquent models.

#### [Version 1.0.0](https://vgcomment.netlify.app/livewire-comments/1.0.0/index.html) 

## _Blade Comments Packages (Coming soon)_
Comments package for applications using Blade. Using this package, you can create and associate comments with Eloquent models.

## _VueJs Comments Packages (Coming soon)_
Comments package for applications using Vuejs. Using this package, you can create and associate comments with Eloquent models.

## _AlpineJs Comments Packages (Coming soon)_
Comments package for applications using Alpinejs. Using this package, you can create and associate comments with Eloquent models.

[!["Buy Me A Coffee"](https://www.buymeacoffee.com/assets/img/custom_images/orange_img.png)](https://www.buymeacoffee.com/nghianecom)

[!["Donate Me!"](https://i.ibb.co/Pw6s74r/image.png)](https://nghiane.com)
