# CSV Upload

## Requirements

-   PHP 8.1
-   MySQL 8.0
-   Composer 2.4
-   NodeJS 18.18.0
-   Apache or Nginx

## Quick Setup

1. Clone this repo `git clone git@github.com:thuzarthaungsein/csv-upload.git`
2. Checkout `develop` branch `git checkout develop`
3. Run `composer install`
4. Create Database
5. Copy `.env.example` to `.env`, Update `.env` and run `php artisan migrate`
6. Run `npm install && npm run build`
7. Run `php artisan optimize:clear`
8. Run `php artisan config:cache`
9. Run `php artisan websocket:serve`
10. Run `php artisan queue:work`
11. Run `php artisan horizon`
