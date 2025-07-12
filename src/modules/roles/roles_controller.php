<?php
// src/modules/roles/roles_controller.php

global $pdo;

// جلب الأدوار مع عدد المستخدمين في كل دور
$stmt  = $pdo->query("
    SELECT r.*, COUNT(u.id) as users_count
    FROM roles r
    LEFT JOIN users u ON r.id = u.role_id AND u.deleted_at IS NULL
    WHERE r.deleted_at IS NULL 
    GROUP BY r.id
    ORDER BY r.id ASC
");
$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// استدعاء الواجهة
require_once __DIR__ . '/roles_view.php';
?>