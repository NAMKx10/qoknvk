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


/**
 * ✨ الدالة المصححة ✨
 * دالة مركزية لجلب الخيارات من جدول lookup_options لمجموعة معينة.
 *
 * @param PDO $pdo كائن الاتصال.
 * @param string $group_key المفتاح البرمجي للمجموعة (مثل 'property_type', 'status').
 * @param bool $use_key_as_value إذا كانت true، ستكون القيمة (value) في الخيار هي المفتاح (key).
 * @return array مصفوفة جاهزة للاستخدام في القوائم المنسدلة.
 */
function get_lookup_options($pdo, $group_key, $use_key_as_value = false)
{
    // نستبعد السجل الخاص باسم المجموعة نفسها
    $sql = "SELECT option_key, option_value FROM lookup_options WHERE group_key = ? AND option_key != ? AND deleted_at IS NULL ORDER BY display_order, id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$group_key, $group_key]);

    if ($use_key_as_value) {
        // ترجع [ 'Active' => 'نشط', 'Expired' => 'منتهي' ]
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    } else {
        // ✨ هذا هو السطر الذي تم تصحيحه ✨
        // نطلب منه صراحةً جلب العمود الثاني (ذي الفهرس 1) وهو "option_value"
        return $stmt->fetchAll(PDO::FETCH_COLUMN, 1);
    }
}

/**
 * ✨ الدالة الجديدة ✨
 * دالة مركزية فائقة الذكاء لحفظ (إضافة أو تعديل) سجل في أي جدول.
 *
 * @param PDO $pdo كائن الاتصال.
 * @param string $table اسم الجدول.
 * @param array $data مصفوفة من البيانات على شكل ['column_name' => 'value'].
 * @param int|null $id معرف السجل في حالة التعديل، أو null في حالة الإضافة.
 * @return int|false معرف السجل المضاف/المعدل أو false عند الفشل.
 */
function save_record($pdo, $table, $data, $id = null)
{
    // تأمين اسم الجدول لمنع حقن SQL
    $safe_table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
    
    try {
        if ($id) { // عملية تعديل (UPDATE)
            $set_parts = [];
            foreach (array_keys($data) as $key) {
                // نستخدم backticks حول أسماء الأعمدة لتجنب أخطاء الكلمات المحجوزة
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
        // في بيئة الإنتاج، من الأفضل تسجيل الخطأ بدلاً من طباعته
        // error_log($e->getMessage());
        return false;
    }
}

?>