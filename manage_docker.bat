@echo off
setlocal

set ENV=%1
if "%ENV%"=="" set ENV=dev

if "%ENV%"=="dev" (
    echo [Docker] Starting Development Environment...
    set APP_ENV=development
    docker-compose up -d --build
) else if "%ENV%"=="prod" (
    echo [Docker] Starting Production Environment...
    docker-compose -f docker-compose.prod.yml up -d --build
) else (
    echo Usage: manage_docker.bat [dev^|prod]
)

endlocal
pause
