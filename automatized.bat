@echo off
start /min "" cmd /k "/apache24/bin/httpd.exe"
start /min cmd /k "cd C:\Users\thier\Desktop\Sonatel Academy\transfert\transfert-backend && code . && exit"
start /min cmd /k "cd C:\Users\thier\Desktop\Sonatel Academy\transfert\transfert-frontEnd && tsc --watch"
start /min "" cmd /k "cd C:\Users\thier\Desktop\Sonatel Academy\transfert\transfert-backend && php artisan serve"
start /min "" cmd /k "cd C:\Users\thier\Desktop\Sonatel Academy\transfert\transfert-frontEnd && npx lite-server"
start /min "" cmd /k "cd C:\Users\thier\Desktop\Sonatel Academy\transfert\transfert-frontEnd && code . && exit"
start /min "" cmd /k "cd C:\Users\thier\Desktop && start postman && exit"
start cmd /k "cd C:\Users\thier\Desktop\Sonatel Academy\transfert\transfert-backend"

