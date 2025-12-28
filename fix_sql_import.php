<?php

/**
 * Fix SQL file for better import compatibility
 * - Replaces utf8mb4_0900_ai_ci with utf8mb4_unicode_ci
 * - Adds proper structure for better import
 * - Ensures compatibility with MySQL 5.7+ and MariaDB
 */

$inputFile = __DIR__ . '/ofisi (13).sql';
$outputFile = __DIR__ . '/ofisi_fixed_import.sql';

if (!file_exists($inputFile)) {
    echo "Error: Input file not found: {$inputFile}\n";
    exit(1);
}

echo "Reading SQL file...\n";
$content = file_get_contents($inputFile);

// Count original issues
$originalIssues = substr_count($content, 'utf8mb4_0900_ai_ci');
echo "Found {$originalIssues} instances of utf8mb4_0900_ai_ci\n";

// Fix 1: Replace incompatible collation
$content = str_replace('utf8mb4_0900_ai_ci', 'utf8mb4_unicode_ci', $content);

// Fix 2: Ensure proper SQL mode settings at the beginning
$header = <<<'HEADER'
-- phpMyAdmin SQL Dump
-- Fixed for MySQL/MariaDB Compatibility
-- 
-- Compatibility fixes applied:
-- 1. Replaced utf8mb4_0900_ai_ci with utf8mb4_unicode_ci
-- 2. Added proper SQL mode settings
-- 3. Enhanced structure for better import

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET FOREIGN_KEY_CHECKS = 0;
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

HEADER;

// Replace the header section
$content = preg_replace(
    '/^-- phpMyAdmin SQL Dump.*?SET NAMES utf8mb4 \*\/;/ms',
    $header,
    $content,
    1
);

// Fix 3: Ensure FOREIGN_KEY_CHECKS is set to 0 at the start
if (strpos($content, 'SET FOREIGN_KEY_CHECKS = 0;') === false) {
    $content = str_replace(
        'SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";',
        "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\nSET FOREIGN_KEY_CHECKS = 0;",
        $content
    );
}

// Fix 4: Add COMMIT and restore FOREIGN_KEY_CHECKS at the end if not present
if (strpos($content, 'SET FOREIGN_KEY_CHECKS = 1;') === false) {
    // Remove any existing COMMIT
    $content = preg_replace('/COMMIT;\s*$/m', '', $content);
    
    // Add proper ending
    $content .= "\n\nSET FOREIGN_KEY_CHECKS = 1;\nCOMMIT;\n\n";
    $content .= "/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\n";
    $content .= "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\n";
    $content .= "/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;\n";
}

// Fix 5: Ensure all CREATE TABLE statements have proper structure
// Add AUTO_INCREMENT where needed for id columns
$content = preg_replace_callback(
    '/CREATE TABLE `([^`]+)`\s*\((.*?)\)\s*ENGINE=InnoDB/s',
    function($matches) {
        $tableName = $matches[1];
        $tableDef = $matches[2];
        
        // Check if table has an id column but no AUTO_INCREMENT
        if (preg_match('/`id`\s+(?:bigint|int|smallint|tinyint).*?NOT NULL(?!.*AUTO_INCREMENT)/i', $tableDef)) {
            // Add AUTO_INCREMENT to id column if it's the first column
            $tableDef = preg_replace(
                '/(`id`\s+(?:bigint|int|smallint|tinyint)[^,]*?NOT NULL)/i',
                '$1 AUTO_INCREMENT',
                $tableDef,
                1
            );
        }
        
        return "CREATE TABLE `{$tableName}` (\n{$tableDef}\n) ENGINE=InnoDB";
    },
    $content
);

// Fix 6: Ensure proper charset and collation for all tables
$content = preg_replace(
    '/ENGINE=InnoDB\s+DEFAULT\s+CHARSET=([^\s]+)\s+COLLATE=([^\s;]+)/i',
    'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
    $content
);

// Fix 7: Fix any remaining collation issues in column definitions
$content = preg_replace(
    '/COLLATE\s+utf8mb4_0900_ai_ci/i',
    'COLLATE utf8mb4_unicode_ci',
    $content
);

// Write the fixed file
echo "Writing fixed SQL file...\n";
file_put_contents($outputFile, $content);

// Verify fixes
$fixedIssues = substr_count($content, 'utf8mb4_0900_ai_ci');
$newCollation = substr_count($content, 'utf8mb4_unicode_ci');

echo "\n✅ SQL file fixed successfully!\n\n";
echo "Summary:\n";
echo "  - Original file: {$inputFile}\n";
echo "  - Fixed file: {$outputFile}\n";
echo "  - Collation issues fixed: {$originalIssues}\n";
echo "  - Remaining issues: {$fixedIssues}\n";
echo "  - utf8mb4_unicode_ci instances: {$newCollation}\n";
echo "  - File size: " . number_format(filesize($outputFile)) . " bytes\n\n";

if ($fixedIssues > 0) {
    echo "⚠️  Warning: {$fixedIssues} collation issues may still remain. Please review.\n";
} else {
    echo "✓ All collation issues have been fixed!\n";
}

echo "\nThe fixed file is ready for import.\n";
echo "You can now import: {$outputFile}\n";

