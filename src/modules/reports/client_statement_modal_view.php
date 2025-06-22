<?php
/*
 * الملف: src/modules/reports/client_statement_modal_view.php
 * الوظيفة: نموذج منبثق لاختيار شروط كشف حساب العميل.
*/

// --- 1. التأسيس والتحقق ---
if (!isset($_GET['id'])) { die("Client ID is required."); }


$client_id = $_GET['id'];

// --- 2. جلب عقود العميل مع تفاصيلها للفلترة ---
$contracts_stmt = $pdo->prepare("
    SELECT 
        cr.id, 
        cr.contract_number,
        p.property_name,
        GROUP_CONCAT(u.unit_name SEPARATOR ', ') as unit_names
    FROM contracts_rental cr
    LEFT JOIN contract_units cu ON cr.id = cu.contract_id
    LEFT JOIN units u ON cu.unit_id = u.id
    LEFT JOIN properties p ON u.property_id = p.id
    WHERE cr.client_id = ? AND cr.deleted_at IS NULL
    GROUP BY cr.id
    ORDER BY cr.id DESC
");
$contracts_stmt->execute([$client_id]);
$contracts_list = $contracts_stmt->fetchAll();

?>
<form method="POST" action="index.php?page=reports/client_statement" target="_blank">
    <input type="hidden" name="client_id" value="<?php echo $client_id; ?>">
    <div class="row g-3">
        <div class="col-12">
            <label for="contract_id_filter" class="form-label">فلترة حسب العقد (اختياري)</label>
            <select class="form-select select2-init" name="contract_id" id="contract_id_filter" data-placeholder="ابحث برقم العقد أو اسم العقار...">
                <option></option> <!-- للحفاظ على عمل الـ placeholder -->
                <option value="all">-- كل العقود --</option> <!-- <<-- هذا هو الخيار الجديد والمهم -->
                <?php foreach ($contracts_list as $contract): ?>
                    <?php
                        // بناء النص المعروض
                        $display_text = htmlspecialchars($contract['contract_number']);
                        if (!empty($contract['property_name'])) {
                            $display_text .= ' - ' . htmlspecialchars($contract['property_name']);
                        }
                        if (!empty($contract['unit_names'])) {
                            $display_text .= ' (' . htmlspecialchars($contract['unit_names']) . ')';
                        }
                    ?>
                    <option value="<?php echo $contract['id']; ?>"><?php echo $display_text; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6"><label for="start_date_modal" class="form-label">من تاريخ</label><input type="date" class="form-control" id="start_date_modal" name="start_date"></div>
        <div class="col-md-6"><label for="end_date_modal" class="form-label">إلى تاريخ</label><input type="date" class="form-control" id="end_date_modal" name="end_date" value="<?php echo date('Y-m-d'); ?>"></div>
        <div class="col-12 mt-3"><div class="form-check"><input class="form-check-input" type="checkbox" name="show_opening_balance" value="1" id="show_opening_balance_modal" checked><label class="form-check-label" for="show_opening_balance_modal">إظهار الرصيد الافتتاحي</label></div></div>
    </div>
    <hr class="my-4">
    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-secondary ms-2" data-bs-dismiss="modal">إلغاء</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-file-alt ms-1"></i> عرض التقرير</button>
    </div>
</form>