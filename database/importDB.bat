@echo off
:: MySQL credentials
set SERVER=localhost
set USER=root
set /p PASSWORD="Enter MySQL password: "
set DATABASE=db_billiard

:: Get the current directory of the batch script
set SCRIPT_DIR=%~dp0
set SQLFILE=%SCRIPT_DIR%db_billiard.sql

:: Path to MySQL executable (update this if necessary)
set MYSQL_PATH="C:\Program Files\MySQL\MySQL Server 8.0\bin\"

:: Change directory to MySQL bin directory
cd /d %MYSQL_PATH%

:: Drop the database if it exists
mysql -u %USER% -p%PASSWORD% -e "DROP DATABASE IF EXISTS %DATABASE%;"
if %ERRORLEVEL% neq 0 (
    echo Failed to drop database %DATABASE%
) else (
    echo Database %DATABASE% dropped successfully
)

:: Create the database
mysql -u %USER% -p%PASSWORD% -e "CREATE DATABASE %DATABASE%;"
if %ERRORLEVEL% neq 0 (
    echo Failed to create database %DATABASE%
) else (
    echo Database %DATABASE% created successfully
)

:: Import the SQL file into the database
mysql -u %USER% -p%PASSWORD% %DATABASE% < %SQLFILE%
if %ERRORLEVEL% neq 0 (
    echo Failed to import SQL file into database %DATABASE%
) else (
    echo Database imported successfully from %SQLFILE%
)

:: Change directory back to the script directory
cd /d %SCRIPT_DIR%

echo Done.

pause