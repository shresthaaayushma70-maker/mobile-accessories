<?php
/**
 * Database Migration: Add Profile Picture Support
 * This script adds profile_picture column to users table and creates uploads/profiles directory
 */

require_once "config.php";

$status = [];
$errors = [];

echo "========================================\n";
echo "Profile Picture System Migration\n";
echo "========================================\n\n";

// 1. Create uploads/profiles directory if it doesn't exist
echo "[STEP 1] Creating uploads/profiles directory...\n";
if (!is_dir("uploads")) {
    mkdir("uploads", 0755);
    $status[] = "✓ Created uploads directory";
} else {
    $status[] = "✓ uploads directory already exists";
}

if (!is_dir("uploads/profiles")) {
    mkdir("uploads/profiles", 0755);
    $status[] = "✓ Created uploads/profiles directory";
} else {
    $status[] = "✓ uploads/profiles directory already exists";
}

// 2. Check and add profile_picture column to users table
echo "\n[STEP 2] Checking for profile_picture column...\n";
$check_column = mysqli_query($conn, "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='users' AND COLUMN_NAME='profile_picture' AND TABLE_SCHEMA='Mproject'");

if (mysqli_num_rows($check_column) == 0) {
    // Column doesn't exist, add it
    if (mysqli_query($conn, "ALTER TABLE users ADD COLUMN profile_picture VARCHAR(255) NULL AFTER password")) {
        echo "✓ Added 'profile_picture' column to users table\n";
        $status[] = "✓ profile_picture column added";
    } else {
        echo "✗ Failed to add profile_picture column: " . mysqli_error($conn) . "\n";
        $errors[] = "Failed to add profile_picture column: " . mysqli_error($conn);
    }
} else {
    echo "✓ Column 'profile_picture' already exists\n";
    $status[] = "✓ profile_picture column already exists";
}

// 3. Create .htaccess file to prevent script execution in uploads directory
echo "\n[STEP 3] Creating security .htaccess file...\n";
$htaccess_path = "uploads/.htaccess";
$htaccess_content = "<FilesMatch \"\\.(php|php3|php4|php5|phtml)$\">\n    Deny from all\n</FilesMatch>\n";

if (!file_exists($htaccess_path)) {
    if (file_put_contents($htaccess_path, $htaccess_content)) {
        echo "✓ Created .htaccess file to prevent script execution\n";
        $status[] = "✓ .htaccess security file created";
    } else {
        echo "⚠ Warning: Could not create .htaccess file (not critical)\n";
        $status[] = "⚠ .htaccess file creation skipped";
    }
} else {
    echo "✓ .htaccess file already exists\n";
    $status[] = "✓ .htaccess file already exists";
}

// 4. Summary
echo "\n========================================\n";
echo "Migration Summary\n";
echo "========================================\n";
foreach ($status as $msg) {
    echo $msg . "\n";
}

if (!empty($errors)) {
    echo "\n❌ Errors encountered:\n";
    foreach ($errors as $error) {
        echo "  - " . $error . "\n";
    }
} else {
    echo "\n✅ Migration completed successfully!\n";
}

echo "\nProfile picture directory: uploads/profiles/\n";
echo "Allowed formats: JPG, JPEG, PNG, WEBP\n";
echo "Max file size: 5MB\n";
?>
