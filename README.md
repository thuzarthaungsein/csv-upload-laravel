# CSV Upload

-   The application allows users to upload CSV files into the system.
-   Once uploaded, the system processes the files in the background.
-   Users are then notified when the processing is complete.
-   Application provides users with a history of all file uploads.
-   For now, system can upload only small file. If user upload large file, that file will be stayed with Processing status.
-   Will enhance later.

-   upload small file.
-   will show real time upload process with percentage.
-   in background, will remove non-utf8 characters and save the file.
-   will chunk the file entries into small Jobs.
-   will insert or update unique entries
-   will notify when the job complete.

## Requirements

-   PHP 8.1
-   MySQL 8.0
-   Composer 2.4
-   NodeJS 18.18.0
-   Apache or Nginx

## Quick Setup

1. Clone this repo `git clone git@github.com:thuzarthaungsein/csv-upload-laravel.git`
2. Run `composer install`
3. Create Database
4. Copy `.env.example` to `.env`, Update `.env` and run `php artisan migrate`
5. Run `npm install && npm run dev`
6. Run `php artisan optimize:clear`
7. Run `php artisan config:cache`
8. Run `php artisan websocket:serve`
9. Run `php artisan queue:work --queue=default,upload,write`
10. Run `php artisan horizon`
