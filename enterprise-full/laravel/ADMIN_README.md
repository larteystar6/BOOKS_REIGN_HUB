# Admin creation

An artisan command has been added to the Laravel app to create a one-time admin user.

Usage (in enterprise-full/laravel):
1. composer install
2. php artisan migrate
3. php artisan app:create-admin admin admin@example.com "StrongP@ssw0rd"

After creation, you may remove this command file for security if desired.
