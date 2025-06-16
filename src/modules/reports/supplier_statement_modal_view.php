<?php
/*
 * الملف: src/modules/reports/supplier_statement_modal_view.php
 * الوظيفة: نموذج منبثق لاختيار شروط كشف حساب المورد.
*/
if (!isset($_GET['id'])) { die("Supplier ID is required."); }
$supplier_id = $_GET['id'];
?>
<form method="POST" action="index.php?page=reports/supplier_statement" target="_blank">
    <input type="hidden" name="supplier_id" value="<?php echo $supplier_id; ?>">
    <div class="row g-3">
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