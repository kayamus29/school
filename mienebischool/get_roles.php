<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=mienebischool', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->query('SELECT name FROM roles');
    $roles = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Roles in DB:\n";
    print_r($roles);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
