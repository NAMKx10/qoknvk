<?php
/**
 * src/core/db_functions.php
 *
 * هذا الملف يحتوي على دوال مركزية للتعامل مع قاعدة البيانات
 * لتوحيد المهام المتكررة وتطبيق مبدأ (لا تكرر نفسك - DRY).
 */

/**
 * دالة مركزية للقيام بالحذف الناعم (Soft Delete) لسجل واحد أو عدة سجلات.
 * تقوم بتحديث حقل 'deleted_at' إلى الوقت الحالي.
 *
 * @param PDO $pdo كائن الاتصال بقاعدة البيانات.
 * @param string $table اسم الجدول المراد التحديث فيه.
 * @param int|array $ids معرف السجل (int) أو مصفوفة من المعرفات (array).
 * @return int عدد السجلات التي تأثرت بالعملية.
 */
function soft_delete($pdo, $table, $ids)
{
    // 1. تأمين اسم الجدول لمنع حقن SQL. يسمح فقط بالأحرف والأرقام والشرطة السفلية.
    $safe_table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
    
    // 2. توحيد المدخلات: نتأكد دائمًا من أن $ids هي مصفوفة، حتى لو تم تمرير رقم واحد.
    $ids_array = (array) $ids;
    
    // 3. التحقق من أن المصفوفة ليست فارغة لمنع حدوث خطأ.
    if (empty($ids_array)) {
        return 0;
    }
    
    // 4. إنشاء علامات الاستفهام '?' ديناميكيًا بعدد المعرفات.
    // مثال: لو كانت $ids_array تحتوي على 3 عناصر، ستكون النتيجة: "?,?,?"
    $placeholders = implode(',', array_fill(0, count($ids_array), '?'));
    
    // 5. بناء جملة SQL النهائية.
    $sql = "UPDATE `{$safe_table}` SET deleted_at = NOW() WHERE id IN ({$placeholders})";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($ids_array);
    
    // 6. إرجاع عدد الصفوف التي تم تحديثها فعليًا.
    return $stmt->rowCount();
}

/**
 * دالة مركزية لاستعادة سجل واحد أو عدة سجلات من الأرشيف.
 * تقوم بتعيين حقل 'deleted_at' إلى NULL.
 *
 * @param PDO $pdo كائن الاتصال.
 * @param string $table اسم الجدول.
 * @param int|array $ids معرف السجل أو مصفوفة من المعرفات.
 * @return int عدد السجلات التي تأثرت.
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
 * دالة مركزية للحذف النهائي (Force Delete) لسجل واحد أو عدة سجلات.
 * تقوم بحذف السجل بشكل دائم من قاعدة البيانات.
 *
 * @param PDO $pdo كائن الاتصال.
 * @param string $table اسم الجدول.
 * @param int|array $ids معرف السجل أو مصفوفة من المعرفات.
 * @return int عدد السجلات التي تأثرت.
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