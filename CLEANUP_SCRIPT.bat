@echo off
REM BAZARIO Project Cleanup Script
REM Removes all redundant, test, and debug files from the project
REM Generated: May 23, 2026

SETLOCAL ENABLEDELAYEDEXPANSION

SET project_root=c:\xampp\htdocs\mobile-accessories

echo.
echo ============================================
echo BAZARIO PROJECT CLEANUP SCRIPT
echo ============================================
echo This script will remove redundant files.
echo.

REM Ask for confirmation
set /p confirm="Are you sure you want to proceed with cleanup? (Y/N): "
if /i not "!confirm!"=="Y" (
    echo Cleanup cancelled.
    goto :eof
)

echo.
echo Removing test and debug files...

REM Test files
del /Q "%project_root%\test_notifications.php" 2>nul && echo  ✓ Deleted: test_notifications.php
del /Q "%project_root%\test_delivered_notification.php" 2>nul && echo  ✓ Deleted: test_delivered_notification.php
del /Q "%project_root%\test_notification_creation.php" 2>nul && echo  ✓ Deleted: test_notification_creation.php
del /Q "%project_root%\NOTIFICATION_QUICK_TEST.php" 2>nul && echo  ✓ Deleted: NOTIFICATION_QUICK_TEST.php
del /Q "%project_root%\e2e_test_complete.php" 2>nul && echo  ✓ Deleted: e2e_test_complete.php
del /Q "%project_root%\debug_login.php" 2>nul && echo  ✓ Deleted: debug_login.php
del /Q "%project_root%\debug_notifications.php" 2>nul && echo  ✓ Deleted: debug_notifications.php
del /Q "%project_root%\diagnostic.php" 2>nul && echo  ✓ Deleted: diagnostic.php
del /Q "%project_root%\fix_login.php" 2>nul && echo  ✓ Deleted: fix_login.php
del /Q "%project_root%\reset_admin.php" 2>nul && echo  ✓ Deleted: reset_admin.php
del /Q "%project_root%\cli_update_order.php" 2>nul && echo  ✓ Deleted: cli_update_order.php

echo.
echo Removing schema checkers...

REM Schema checkers
del /Q "%project_root%\check_db.php" 2>nul && echo  ✓ Deleted: check_db.php
del /Q "%project_root%\check_notification_schema.php" 2>nul && echo  ✓ Deleted: check_notification_schema.php
del /Q "%project_root%\check_orders_schema.php" 2>nul && echo  ✓ Deleted: check_orders_schema.php
del /Q "%project_root%\check_table_schema.php" 2>nul && echo  ✓ Deleted: check_table_schema.php

echo.
echo Removing redundant database setup files...

REM Redundant DB setup
del /Q "%project_root%\init_db.php" 2>nul && echo  ✓ Deleted: init_db.php
del /Q "%project_root%\database_setup.sql" 2>nul && echo  ✓ Deleted: database_setup.sql

echo.
echo Removing old HTML files...

REM Old HTML files
del /Q "%project_root%\product.html" 2>nul && echo  ✓ Deleted: product.html
del /Q "%project_root%\profile.html" 2>nul && echo  ✓ Deleted: profile.html
del /Q "%project_root%\dashstyle.html" 2>nul && echo  ✓ Deleted: dashstyle.html

echo.
echo Removing redundant CSS file...

REM Redundant CSS
del /Q "%project_root%\dashstyle.css" 2>nul && echo  ✓ Deleted: dashstyle.css

echo.
echo Removing redundant/duplicate admin files...

REM Duplicate admin files
del /Q "%project_root%\edit_product.php" 2>nul && echo  ✓ Deleted: edit_product.php (duplicate of admin/edit_product.php)

echo.
echo ============================================
echo CLEANUP COMPLETED!
echo ============================================
echo.
echo Removed files:
echo  - 11 test/debug files
echo  - 4 schema checkers
echo  - 2 redundant DB setup files
echo  - 3 old HTML files
echo  - 1 redundant CSS file
echo  - 1 duplicate admin file
echo.
echo Total: 21 files removed
echo.
echo The project structure is now clean!
echo See docs\PROJECT_CLEANUP_REPORT.md for details.
echo.
pause
