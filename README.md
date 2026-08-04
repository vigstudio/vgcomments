# VgComments

Core comments backend for Laravel: REST API, moderation admin, reactions, attachments, spam/NSFW checks.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/vigstudio/vgcomments.svg?style=flat-square)](https://packagist.org/packages/vigstudio/vgcomments)
[![Total Downloads](https://img.shields.io/packagist/dt/vigstudio/vgcomments.svg?style=flat-square)](https://packagist.org/packages/vigstudio/vgcomments)

**Version:** `2.0` · PHP `^8.2` · Laravel `^10|^11|^12|^13`

**Live demo:** [vgcomment.nghiane.com](https://vgcomment.nghiane.com)

## UI packages (2.x)

| Package | Stack |
|---------|--------|
| [`vigstudio/blade-comments`](https://github.com/vigstudio/blade-comments) `^2.0` | Blade + vanilla JS (recommended) |
| [`vigstudio/livewire-comments`](https://github.com/vigstudio/livewire-comments) `^2.0` | Livewire **4** |

## Installation

```bash
composer require vigstudio/vgcomments:^2.0
php artisan vendor:publish --tag=vgcomment-config
php artisan vendor:publish --tag=vgcomment-assets
php artisan migrate
php artisan optimize:clear
```

> Deploy note: keep `public/vendor/vgcomments/` on every deploy or admin CSS breaks.

## Configuration

See `config/vgcomment.php`. Important keys: `prefix`, `connection` (`VCOMMENT_DB_CONNECTION`), `allow_guests`, `moderation_users`, `min_length` / `max_length`, `recaptcha`.

## Admin

Moderators in `moderation_users` open `/{prefix}/admin` (default `/vgcomments/admin`).

## REST API

Base: `/{prefix}/api` — list/create/update/delete comments, reactions, reports, file upload/stream.

## License

MIT.
