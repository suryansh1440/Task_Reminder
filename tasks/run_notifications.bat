@echo off
:loop
php notifications.php
timeout /t 300 /nobreak
goto loop 