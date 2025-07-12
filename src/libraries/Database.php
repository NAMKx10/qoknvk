<?php
/**
 * src/libraries/Database.php
 *
 * مكتبة مركزية تحتوي على دوال للتعامل المباشر مع قاعدة البيانات.
 * تطبق مبدأ (لا تكرر نفسك - DRY) لعمليات CRUD.
 */

/**
 * دالة مركزية فائقة الذكاء لحفظ (إضافة أو تعديل) سجل في أي جدول.
 */
function save_record($pdo, $table, $data, $id = null)
{
    $safe_table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
    
    try {
        if ($id) { // عملية تعديل (UPDATE)
            $set_parts = [];
            foreach (array_keys($data) as $key) {
                $set_parts[] = "`{$key}` = ?";
            }
            $set_clause = implode(', ', $set_parts);
            
            $sql = "UPDATE `{$safe_table}` SET {$set_clause} WHERE `id` = ?";
            
            $params = array_values($data);
            $params[] = $id;

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $id;

        } else { // عملية إضافة (INSERT)
            $fields = '`' . implode('`, `', array_keys($data)) . '`';
            $placeholders = implode(', ', array_fill(0, count($data), '?'));
            $sql = "INSERT INTO `{$safe_table}` ({$fields}) VALUES ({$placeholders})";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array_values($data));
            return $pdo->lastInsertId();
        }
    } catch (PDOException $e) {
        // يمكن تسجيل الخطأ هنا مستقبلاً
        return false;
    }
}

/**
 * دالة مركزية للقيام بالحذف الناعم (Soft Delete).
 */
function soft_delete($pdo, $table, $ids)
{
    $safe_table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
    $ids_array = (array) $ids;
    
    if (empty($ids_array)) {
        return 0;
    }
    
    $placeholders = implode(',', array_fill(0, count($ids_array), '?'));
    $sql = "UPDATE `{$safe_table}` SET deleted_at = NOW() WHERE id IN ({$placeholders})";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($ids_array);
    
    return $stmt->rowCount();
}

/**
 * دالة مركزية لاستعادة سجل من الأرشيف.
 */
function restore_from_archive($pdo, $table, $ids)
{
    $safe_table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
    $ids_array = (array) $ids;

    if (empty($ids_array)) {
        return 0;
    }
    
    $placeholders = implode(',', array_fill(0, count($ids_array), '?'));
    $sql = "UPDATE `{$safe_table}` SET deleted_at = NULL WHERE id IN ({$placeholders})";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($ids_array);
    
    return $stmt->rowCount();
}

/**
 * دالة مركزية للحذف النهائي (Force Delete).
 */
function force_delete($pdo, $table, $ids)
{
    $safe_table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
    $ids_array = (array) $ids;

    if (empty($ids_array)) {
        return 0;
    }
    
    $placeholders = implode(',', array_fill(0, count($ids_array), '?'));
    $sql = "DELETE FROM `{$safe_table}` WHERE id IN ({$placeholders})";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($ids_array);
    
    return $stmt->rowCount();
}
?>