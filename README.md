# CampusHub with Laravel 13.x

## HOW TO SETUP

1. Clone the repository
2. Run `composer install`
3. Run `npm install`
4. Copy `.env.example` to `.env`
5. Run `php artisan key:generate`
6. Run `php artisan jwt:secret`
7. Run `php artisan migrate`

## Third Party Dependencies

- Laravel JWT Auth (tymon/jwt-auth by tymondesigns)
- Laravel Role Permissions (spatie/laravel-permission v7 by spatie)
  - Laravel 12 & 13 compatible
  - Needs PHP 8.3+