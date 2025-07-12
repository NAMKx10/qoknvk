<?php
/**
 * src/core/db_functions.php
 *
 * يحتوي على دوال مساعدة خاصة بقاعدة البيانات لا تنتمي لمكتبة معينة.
 */

/**
 * دالة مركزية لجلب الخيارات من جدول lookup_options لمجموعة معينة.
 */
function get_lookup_options($pdo, $group_key, $use_key_as_value = false)
{
    $sql = "SELECT option_key, option_value FROM lookup_options WHERE group_key = ? AND option_key != ? AND deleted_at IS NULL ORDER BY display_order, id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$group_key, $group_key]);

    if ($use_key_as_value) {
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    } else {
        return $stmt->fetchAll(PDO::FETCH_COLUMN, 1);
    }
}
?>