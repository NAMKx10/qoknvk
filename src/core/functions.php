<?php

/**
 * دالة لتوليد جدول الدفعات تلقائياً لعقد معين.
 */
function generate_payment_schedule($pdo, $contract_id, $contract_type, $start_date_str, $end_date_str, $total_amount, $cycle) {
    
    // إذا لم تكن هناك دورة سداد، نعتبرها دفعة واحدة
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
        default: // دفعة واحدة أو أي قيمة أخرى
            $sql = "INSERT INTO payment_schedules (contract_type, contract_id, due_date, amount_due) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$contract_type, $contract_id, $start_date->format('Y-m-d'), $total_amount]);
            return;
    }

    // حساب عدد الدفعات الإجمالي
    $total_months = $start_date->diff($end_date)->m + ($start_date->diff($end_date)->y * 12);
    if ($total_months == 0) $total_months = 1;
    
    $number_of_payments = ceil($total_months / $interval_months);

    if ($number_of_payments <= 0) return; 

    // حساب قيمة الدفعة الواحدة
    $payment_amount = round($total_amount / $number_of_payments, 2);

    $sql = "INSERT INTO payment_schedules (contract_type, contract_id, due_date, amount_due) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);

    $current_due_date = clone $start_date;

    for ($i = 0; $i < $number_of_payments; $i++) {
        $stmt->execute([$contract_type, $contract_id, $current_due_date->format('Y-m-d'), $payment_amount]);
        // حساب تاريخ الاستحقاق التالي
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
    // Super Admin يملك كل الصلاحيات
    if (isset($_SESSION['user_permissions']) && $_SESSION['user_permissions'][0] === 'SUPER_ADMIN') {
        return true;
    }
    
    // التحقق إذا كانت الصلاحية موجودة في مصفوفة صلاحيات المستخدم
    if (isset($_SESSION['user_permissions']) && in_array($permission_key, $_SESSION['user_permissions'])) {
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
        return; // لا تعرض أي شيء إذا كانت هناك صفحة واحدة فقط
    }

    echo '<nav aria-label="Page navigation"><ul class="pagination mb-0">';

    // زر "السابق"
    if ($current_page > 1) {
        echo '<li class="page-item"><a class="page-link" href="?' . http_build_query(array_merge($params, ['p' => $current_page - 1])) . '">السابق</a></li>';
    } else {
        echo '<li class="page-item disabled"><span class="page-link">السابق</span></li>';
    }

    // حساب بداية ونهاية الروابط المرقمة
    $start = max(1, $current_page - floor($links_to_show / 2));
    $end = min($total_pages, $start + $links_to_show - 1);
    
    // التأكد من أننا نعرض العدد المطلوب من الروابط إذا كنا قرب النهاية
    $start = max(1, $end - $links_to_show + 1);

    // زر "الأول" و "..." إذا كنا بعيدين عن البداية
    if ($start > 1) {
        echo '<li class="page-item"><a class="page-link" href="?' . http_build_query(array_merge($params, ['p' => 1])) . '">1</a></li>';
        if ($start > 2) {
            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }

    // عرض الروابط المرقمة
    for ($i = $start; $i <= $end; $i++) {
        $active_class = ($i == $current_page) ? 'active' : '';
        echo '<li class="page-item ' . $active_class . '"><a class="page-link" href="?' . http_build_query(array_merge($params, ['p' => $i])) . '">' . $i . '</a></li>';
    }

    // زر "الأخير" و "..." إذا كنا بعيدين عن النهاية
    if ($end < $total_pages) {
        if ($end < $total_pages - 1) {
            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        echo '<li class="page-item"><a class="page-link" href="?' . http_build_query(array_merge($params, ['p' => $total_pages])) . '">' . $total_pages . '</a></li>';
    }

    // زر "التالي"
    if ($current_page < $total_pages) {
        echo '<li class="page-item"><a class="page-link" href="?' . http_build_query(array_merge($params, ['p' => $current_page + 1])) . '">التالي</a></li>';
    } else {
        echo '<li class="page-item disabled"><span class="page-link">التالي</span></li>';
    }

    echo '</ul></nav>';
}

/**
 * =================================================================
 * دالة مركزية لبناء شرط الفروع بناءً على صلاحيات المستخدم
 * =================================================================
 * @param string $main_table_alias  - الاسم المستعار للجدول الرئيسي الذي يحتوي على branch_id (مثل 'p' لـ properties)
 * @param array &$params_ref        - مرجع لمصفوفة المتغيرات ليتم إضافة branch_ids إليها
 * @return string                   - شرط SQL جاهز للإضافة (e.g., " AND p.branch_id IN (?,?) ")
 */
function build_branches_query_condition($main_table_alias, &$params_ref) {
    
    // إذا لم يكن المستخدم مسجلاً أو لا توجد صلاحيات فروع، لا ترجع أي شيء (للأمان)
    if (!isset($_SESSION['user_branch_ids'])) {
        // هذا يمنع عرض أي بيانات إذا لم يتم تحديد الصلاحيات
        return " AND 1=0 "; 
    }

    $user_branches = $_SESSION['user_branch_ids'];

    // إذا كان المستخدم هو المدير الخارق، اسمح له برؤية كل شيء
    if ($user_branches === 'ALL') {
        return ""; // لا تقم بإضافة أي شرط
    }

    // إذا كان المستخدم مخصصًا لفروع ولكن القائمة فارغة، لا تعرض له أي شيء
    if (is_array($user_branches) && empty($user_branches)) {
        return " AND 1=0 ";
    }

    // إذا كان لديه فروع مخصصة، قم ببناء الشرط
    if (is_array($user_branches) && !empty($user_branches)) {
        // إنشاء علامات استفهام بناءً على عدد الفروع المسموح بها
        $placeholders = implode(',', array_fill(0, count($user_branches), '?'));
        
        // إضافة أرقام الفروع إلى مصفوفة المتغيرات الرئيسية
        foreach ($user_branches as $branch_id) {
            $params_ref[] = $branch_id;
        }

        // إرجاع شرط SQL النهائي
        return " AND {$main_table_alias}.branch_id IN ({$placeholders}) ";
    }
    
    // في أي حالة أخرى، لا تعرض أي شيء كإجراء أمان افتراضي
    return " AND 1=0 ";
}