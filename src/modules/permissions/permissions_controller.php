<?php
// src/modules/permissions/permissions_controller.php

global $pdo;

// جلب المجموعات مع عدد الصلاحيات في كل منها
$groups_stmt = $pdo->query("
    SELECT pg.*, COUNT(p.id) as permissions_count
    FROM permission_groups pg
    LEFT JOIN permissions p ON pg.id = p.group_id AND p.deleted_at IS NULL
    WHERE pg.deleted_at IS NULL
    GROUP BY pg.id
    ORDER BY pg.display_order, pg.id ASC
");
$groups = $groups_stmt->fetchAll(PDO::FETCH_ASSOC);

// تحديد المجموعة النشطة
$active_group_id = $_GET['group_id'] ?? ($groups[0]['id'] ?? 0);

// جلب صلاحيات المجموعة النشطة
$permissions = [];
if ($active_group_id) {
    $permissions_stmt = $pdo->prepare("SELECT * FROM permissions WHERE group_id = ? AND deleted_at IS NULL ORDER BY id ASC");
    $permissions_stmt->execute([$active_group_id]);
    $permissions = $permissions_stmt->fetchAll(PDO::FETCH_ASSOC);
}

// جلب بيانات المجموعة النشطة
$active_group = null;
foreach ($groups as $group) {
    if ($group['id'] == $active_group_id) {
        $active_group = $group;
        break;
    }
}

// استدعاء الواجهة
require_once __DIR__ . '/permissions_view.php';
?>