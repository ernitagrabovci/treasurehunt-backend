<?php
$db = new PDO('mysql:host=127.0.0.1;port=3306;dbname=treasurehuntdb;charset=utf8mb4', 'root', '');

// Delete test users created during debugging, keep real users
$testEmails = [
    'player@test.com',
    'regtest@test.com',
    'x@x.com',
    'x2@x.com',
    'newuser1@test.com',
];
$placeholders = implode(',', array_fill(0, count($testEmails), '?'));
$stmt = $db->prepare("DELETE FROM users WHERE email IN ($placeholders)");
$stmt->execute($testEmails);
echo "Deleted test users\n";

// Show remaining users
$stmt = $db->query('SELECT id, name, email, created_at FROM users ORDER BY id');
echo "\n=== Remaining users ===\n";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode($row) . "\n";
}
