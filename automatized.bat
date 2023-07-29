@echo off
start /min "" cmd /k "/apache24/bin/httpd.exe"
start /min cmd /k "cd C:\Users\CybeRooT\Desktop\Sonatel Academy\transfert\transfert-backend && code . &&exit"
start /min "" cmd /k "cd C:\Users\CybeRooT\Desktop\Sonatel Academy\transfert\transfert-backend && php artisan serve"
start /min "" cmd /k "cd C:\Users\CybeRooT\Desktop && start postman && exit"
start cmd /k "cd C:\Users\CybeRooT\Desktop\Sonatel Academy\transfert\transfert-backend"

