<?php
/**
 * handlers/settings_handler.php
 * 
 * معالجات إعدادات القوائم (Lookups Settings Handlers)
 * - خاص بمعالجات AJAX الخاصة بالإضافة والتعديل لحركات القوائم (المجموعات والخيارات)
 * - يُستخدم مع موجّه مركزي في index.php
 * - لا يحتوي طباعة أو try/catch أو headers
 * - فقط يعدّل متغير $response حسب نتيجة المعالجة
 */

if (!defined('IS_HANDLER')) { die('Direct access not allowed.'); }

/**
 * إضافة مجموعة جديدة
 * [POST] group_key, option_value
 * النتيجة: success, message
 */
if ($page === 'settings/handle_add_lookup_group_ajax') {
    $pdo->beginTransaction();
    $sql = "INSERT INTO lookup_options (group_key, option_key, option_value) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_POST['group_key'], $_POST['group_key'], $_POST['option_value']]);
    $pdo->commit();
    $response = ['success' => true, 'message' => 'تمت إضافة المجموعة بنجاح.'];
}

/**
 * تعديل مجموعة قائمة
 * [POST] new_group_key, original_group_key, new_option_value
 * النتيجة: success, message
 */
elseif ($page === 'settings/handle_edit_lookup_group_ajax') {
    $pdo->beginTransaction();
    // تحديث كل الخيارات التي تنتمي للمجموعة القديمة
    $sql_update_children = "UPDATE lookup_options SET group_key = ? WHERE group_key = ?";
    $stmt_update_children = $pdo->prepare($sql_update_children);
    $stmt_update_children->execute([$_POST['new_group_key'], $_POST['original_group_key']]);
    // تحديث سجل اسم المجموعة نفسه
    $sql_update_parent = "UPDATE lookup_options SET option_key = ?, option_value = ? WHERE group_key = ? AND id = (SELECT id FROM (SELECT id FROM lookup_options WHERE group_key = ? AND option_key = ?) AS tmp LIMIT 1)";
    $stmt_update_parent = $pdo->prepare($sql_update_parent);
    $stmt_update_parent->execute([$_POST['new_group_key'], $_POST['new_option_value'], $_POST['new_group_key'], $_POST['new_group_key'], $_POST['new_group_key']]);
    $pdo->commit();
    $response = ['success' => true, 'message' => 'تم تحديث المجموعة بنجاح.'];
}

/**
 * إضافة خيار جديد لمجموعة
 * [POST] group_key, option_key (اختياري), option_value
 * النتيجة: success, message
 */
elseif ($page === 'settings/handle_add_lookup_option_ajax') {
    $option_key = $_POST['option_key'] ?: str_replace(' ', '_', trim(strtolower($_POST['option_value'])));
    $sql = "INSERT INTO lookup_options (group_key, option_key, option_value) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_POST['group_key'], $option_key, $_POST['option_value']]);
    $response = ['success' => true, 'message' => 'تم إضافة الخيار بنجاح.'];
}

/**
 * تعديل خيار في مجموعة
 * [POST] id, option_value, option_key, color (اختياري), bg_color (اختياري), custom_fields (اختياري)
 * النتيجة: success, message
 */
elseif ($page === 'settings/handle_edit_lookup_option_ajax') {
    $pdo->beginTransaction();
    // تحديث البيانات الأساسية
    $sql = "UPDATE lookup_options SET option_value = ?, option_key = ?, color = ?, bg_color = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $_POST['option_value'], $_POST['option_key'],
        $_POST['color'] ?? '#ffffff', $_POST['bg_color'] ?? '#6c757d', $_POST['id']
    ]);
    // تحديث مخطط الحقول المخصصة
    if (isset($_POST['custom_fields'])) {
        $filtered_fields = array_filter($_POST['custom_fields'], function($field) {
            return !empty($field['label']) && !empty($field['name']);
        });
        $schema_json = json_encode(array_values($filtered_fields), JSON_UNESCAPED_UNICODE);
        $schema_sql = "UPDATE lookup_options SET custom_fields_schema = ? WHERE id = ?";
        $schema_stmt = $pdo->prepare($schema_sql);
        $schema_stmt->execute([$schema_json, $_POST['id']]);
    }
    $pdo->commit();
    $response = ['success' => true, 'message' => 'تم تحديث الخيار بنجاح.'];
}

// ملاحظة: معالجات الحذف (delete) التي تعتمد على إعادة التوجيه header() تبقى في index.php أو في handler منفصل للعمليات غير الـAJAX.
?>