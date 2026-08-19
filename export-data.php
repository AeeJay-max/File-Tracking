<?php

$db = new PDO('sqlite:' . __DIR__ . '/database/database.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$output = __DIR__ . '/filetracking-data-clean.sql';

/*
 * Only export actual File Tracking System application data.
 * Do NOT export Laravel temporary/system tables.
 */
$tables = [
    'users',
    'departments',
    'designations',
    'file_records',
    'file_transfers',
    'file_movements',
    'transfer_requests',
    'notifications',
    'public_files',
    'audit_logs',
];

$handle = fopen($output, 'w');

if (!$handle) {
    die("Could not create output file.\n");
}

fwrite($handle, "-- File Tracking System data-only export\n");
fwrite($handle, "-- SQLite -> MySQL\n");
fwrite($handle, "-- Generated: " . date('Y-m-d H:i:s') . "\n\n");
fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n\n");

foreach ($tables as $table) {

    $exists = $db->prepare("
        SELECT COUNT(*)
        FROM sqlite_master
        WHERE type = 'table'
          AND name = ?
    ");

    $exists->execute([$table]);

    if (!$exists->fetchColumn()) {
        fwrite($handle, "-- Table {$table} does not exist in SQLite\n\n");
        continue;
    }

    $safeTable = str_replace('`', '``', $table);

    $columns = $db->query(
        "PRAGMA table_info(`{$safeTable}`)"
    )->fetchAll(PDO::FETCH_ASSOC);

    if (!$columns) {
        continue;
    }

    $columnNames = array_map(
        fn($column) =>
            '`' . str_replace('`', '``', $column['name']) . '`',
        $columns
    );

    $rows = $db->query(
        "SELECT * FROM `{$safeTable}`"
    );

    $count = 0;

    while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {

        $values = [];

        foreach ($columns as $column) {

            $name = $column['name'];
            $value = $row[$name];

            if ($value === null) {
                $values[] = 'NULL';
                continue;
            }

            if (
                is_int($value) ||
                is_float($value) ||
                (
                    is_string($value) &&
                    preg_match('/^-?\d+(\.\d+)?$/', $value)
                )
            ) {
                $values[] = $value;
                continue;
            }

            $value = str_replace(
                ["\\", "'"],
                ["\\\\", "''"],
                (string)$value
            );

            $values[] = "'" . $value . "'";
        }

        fwrite(
            $handle,
            "INSERT INTO `{$safeTable}` (" .
            implode(', ', $columnNames) .
            ") VALUES (" .
            implode(', ', $values) .
            ");\n"
        );

        $count++;
    }

    fwrite(
        $handle,
        "\n-- {$table}: {$count} rows\n\n"
    );
}

fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");

fclose($handle);

echo "Export complete.\n";
echo "File: {$output}\n";
echo "Tables exported: " . count($tables) . "\n";