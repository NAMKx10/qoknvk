<?php

/**
 * src/core/functions.php
 *
 * يحتوي على الدوال المساعدة العامة للمشروع.
 */

/**
 * دالة لتوليد جدول الدفعات تلقائياً لعقد معين.
 */
function generate_payment_schedule($pdo, $contract_id, $contract_type, $start_date_str, $end_date_str, $total_amount, $cycle) {
    
    if (empty($cycle)) {
        $cycle = 'دفعة واحدة';
    }

    $start_date = new DateTime($start_date_str);
    $end_date = new DateTime($end_date_str);

    $interval_months = 0;
    switch ($cycle) {
        case 'شهري':
            $interval_months = 1;
            break;
        case 'ربع سنوي':
            $interval_months = 3;
            break;
        case 'نصف سنوي':
            $interval_months = 6;
            break;
        case 'سنوي':
            $interval_months = 12;
            break;
        default: 
            $sql = "INSERT INTO payment_schedules (contract_type, contract_id, due_date, amount_due) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$contract_type, $contract_id, $start_date->format('Y-m-d'), $total_amount]);
            return;
    }

    $total_months = $start_date->diff($end_date)->m + ($start_date->diff($end_date)->y * 12);
    if ($total_months == 0) $total_months = 1;
    
    $number_of_payments = ceil($total_months / $interval_months);

    if ($number_of_payments <= 0) return; 

    $payment_amount = round($total_amount / $number_of_payments, 2);

    $sql = "INSERT INTO payment_schedules (contract_type, contract_id, due_date, amount_due) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);

    $current_due_date = clone $start_date;

    for ($i = 0; $i < $number_of_payments; $i++) {
        $stmt->execute([$contract_type, $contract_id, $current_due_date->format('Y-m-d'), $payment_amount]);
        if ($i < $number_of_payments - 1) {
            $current_due_date->add(new DateInterval("P{$interval_months}M"));
        }
    }
}


/**
 * دالة مركزية للتحقق من صلاحية المستخدم الحالي.
 * @param string $permission_key المفتاح البرمجي للصلاحية.
 * @return bool
 */
function has_permission($permission_key) {
    if (!isset($_SESSION['user_permissions']) || !is_array($_SESSION['user_permissions'])) {
        return false;
    }
    
    // الحل: التحقق من اسم الدور مباشرة من الجلسة
    if (isset($_SESSION['user_role_name']) && $_SESSION['user_role_name'] === 'Super Admin') {
        return true;
    }
    
    // التحقق من الصلاحية المحددة
    if (in_array($permission_key, $_SESSION['user_permissions'])) {
        return true;
    }

    return false;
}


/**
 * دالة لعرض نظام ترقيم صفحات ذكي واحترافي.
 *
 * @param int $current_page  الصفحة الحالية.
 * @param int $total_pages   إجمالي عدد الصفحات.
 * @param array $params      مصفوفة بالباراميترات الحالية في الرابط للحفاظ على الفرز.
 * @param int $links_to_show عدد الروابط المراد عرضها حول الصفحة الحالية.
 */
function render_smart_pagination($current_page, $total_pages, $params = [], $links_to_show = 5) {
    if ($total_pages <= 1) {
        return; 
    }

    echo '<nav aria-label="Page navigation"><ul class="pagination mb-0">';

    if ($current_page > 1) {
        echo '<li class="page-item"><a class="page-link" href="?' . http_build_query(array_merge($params, ['p' => $current_page - 1])) . '">السابق</a></li>';
    } else {
        echo '<li class="page-item disabled"><span class="page-link">السابق</span></li>';
    }

    $start = max(1, $current_page - floor($links_to_show / 2));
    $end = min($total_pages, $start + $links_to_show - 1);
    
    $start = max(1, $end - $links_to_show + 1);

    if ($start > 1) {
        echo '<li class="page-item"><a class="page-link" href="?' . http_build_query(array_merge($params, ['p' => 1])) . '">1</a></li>';
        if ($start > 2) {
            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }

    for ($i = $start; $i <= $end; $i++) {
        $active_class = ($i == $current_page) ? 'active' : '';
        echo '<li class="page-item ' . $active_class . '"><a class="page-link" href="?' . http_build_query(array_merge($params, ['p' => $i])) . '">' . $i . '</a></li>';
    }

    if ($end < $total_pages) {
        if ($end < $total_pages - 1) {
            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        echo '<li class="page-item"><a class="page-link" href="?' . http_build_query(array_merge($params, ['p' => $total_pages])) . '">' . $total_pages . '</a></li>';
    }

    if ($current_page < $total_pages) {
        echo '<li class="page-item"><a class="page-link" href="?' . http_build_query(array_merge($params, ['p' => $current_page + 1])) . '">التالي</a></li>';
    } else {
        echo '<li class="page-item disabled"><span class="page-link">التالي</span></li>';
    }

    echo '</ul></nav>';
}

/**
 * دالة مركزية لبناء شرط الفروع بناءً على صلاحيات المستخدم.
 * @param string $main_table_alias  - الاسم المستعار للجدول الرئيسي.
 * @param array &$params_ref        - مرجع لمصفوفة المتغيرات.
 * @return string                   - شرط SQL جاهز للإضافة.
 */
function build_branches_query_condition($main_table_alias, &$params_ref) {
    
    if (!isset($_SESSION['user_branch_ids'])) {
        return " AND 1=0 "; 
    }

    $user_branches = $_SESSION['user_branch_ids'];

    if ($user_branches === 'ALL') {
        return ""; 
    }

    if (is_array($user_branches) && empty($user_branches)) {
        return " AND 1=0 ";
    }

    if (is_array($user_branches) && !empty($user_branches)) {
        $placeholders = implode(',', array_fill(0, count($user_branches), '?'));
        
        foreach ($user_branches as $branch_id) {
            $params_ref[] = $branch_id;
        }

        return " AND {$main_table_alias}.branch_id IN ({$placeholders}) ";
    }
    
    return " AND 1=0 ";
}


/**
 * دالة مركزية لتنسيق وعرض العملات بشكل ديناميكي.
 *
 * @param float $amount المبلغ المراد تنسيقه.
 * @param string|null $currency_code رمز العملة (مثل 'SAR'). إذا ترك فارغًا، سيتم استخدام العملة الافتراضية.
 * @return string النص المنسق بالكامل (مثال: "SAR 1,250.00").
 */
function formatCurrency($amount, $currency_code = null) {
    global $pdo;
    static $currencies = []; // للتخزين المؤقت وتجنب تكرار الاستعلام

    // إذا لم نحدد عملة، ابحث عن العملة الافتراضية
    if ($currency_code === null) {
        $currency_code = 'SAR'; // قيمة افتراضية مؤقتة، يمكن جلبها من الإعدادات لاحقًا
    }

    // جلب بيانات العملة من قاعدة البيانات (أو من الذاكرة المؤقتة)
    if (!isset($currencies[$currency_code])) {
        $stmt = $pdo->prepare("SELECT * FROM currencies WHERE currency_code = ? LIMIT 1");
        $stmt->execute([$currency_code]);
        $currency_data = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$currency_data) return (float)$amount; // إذا لم يتم العثور على العملة
        $currencies[$currency_code] = $currency_data;
    }

    $config = $currencies[$currency_code];

    $formatted_amount = number_format((float)$amount, (int)$config['decimal_places']);
    $symbol = $config['symbol_html'];

    if ($config['symbol_position'] === 'before') {
        return $symbol . ' ' . $formatted_amount;
    } else {
        return $formatted_amount . ' ' . $symbol;
    }
}

?>