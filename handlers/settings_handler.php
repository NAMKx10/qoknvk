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
* تعديل خيار في مجموعة (تم تحديثه)
 * [POST] id, option_value, ... , custom_fields (اختياري), advanced_config (اختياري)
 * النتيجة: success, message
 */

elseif ($page === 'settings/handle_edit_lookup_option_ajax') {
    $pdo->beginTransaction();

    // 1. تحديث البيانات الأساسية (يبقى كما هو)
    $sql = "UPDATE lookup_options SET option_value = ?, option_key = ?, color = ?, bg_color = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $_POST['option_value'], $_POST['option_key'],
        $_POST['color'] ?? '#ffffff', $_POST['bg_color'] ?? '#6c757d', $_POST['id']
    ]);
    
    // 2. تحديث مخطط الحقول المخصصة (يبقى كما هو)
    // 2. ✨ تحديث مخطط الحقول المخصصة (تم إصلاحه) ✨
// نقوم بتجهيز مصفوفة فارغة في البداية
$filtered_fields = []; 
if (isset($_POST['custom_fields'])) {
    // إذا تم إرسال حقول، نقوم بفلترتها
    $filtered_fields = array_filter($_POST['custom_fields'], function($field) {
        return !empty($field['label']) && !empty($field['name']);
    });
}
// نقوم دائمًا بحفظ النتيجة، سواء كانت تحتوي على حقول أو كانت فارغة
$schema_json = json_encode(array_values($filtered_fields), JSON_UNESCAPED_UNICODE);
$schema_sql = "UPDATE lookup_options SET custom_fields_schema = ? WHERE id = ?";
$schema_stmt = $pdo->prepare($schema_sql);
$schema_stmt->execute([$schema_json, $_POST['id']]);
    
    // 3. ✨ هنا الجزء الجديد: تحديث إعدادات الربط المتقدم ✨
    if (isset($_POST['advanced_config'])) {
        // نستقبل مصفوفة الإعدادات من النموذج
        $advanced_config_data = $_POST['advanced_config'];
        // نحولها إلى نص JSON لحفظها في قاعدة البيانات
        $config_json = json_encode($advanced_config_data, JSON_UNESCAPED_UNICODE);
        
        $config_sql = "UPDATE lookup_options SET advanced_config = ? WHERE id = ?";
        $config_stmt = $pdo->prepare($config_sql);
        $config_stmt->execute([$config_json, $_POST['id']]);
    } else {
        // إذا لم يتم إرسال أي إعدادات، نحفظ قيمة فارغة (NULL)
        $config_sql = "UPDATE lookup_options SET advanced_config = NULL WHERE id = ?";
        $config_stmt = $pdo->prepare($config_sql);
        $config_stmt->execute([$_POST['id']]);
    }
    
    $pdo->commit();
    $response = ['success' => true, 'message' => 'تم تحديث الخيار بنجاح.'];
}


// ملاحظة: معالجات الحذف (delete) التي تعتمد على إعادة التوجيه header() تبقى في index.php أو في handler منفصل للعمليات غير الـAJAX.
?>