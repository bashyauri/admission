@echo off
cd /d c:\laragon\www\admission
php -r "echo getcwd() . PHP_EOL;"
php artisan --version
php artisan test --compact
