<?php
// جلب قائمة العملاء والموردين والعقارات للتقارير
$clients_stmt = $pdo->query("SELECT DISTINCT c.id, c.client_name FROM clients c JOIN contracts_rental cr ON c.id = cr.client_id ORDER BY c.client_name ASC");
$clients_list = $clients_stmt->fetchAll();

$suppliers_stmt = $pdo->query("SELECT DISTINCT s.id, s.supplier_name FROM suppliers s JOIN contracts_supply cs ON s.id = cs.supplier_id ORDER BY s.supplier_name ASC");
$suppliers_list = $suppliers_stmt->fetchAll();

$properties_stmt = $pdo->query("SELECT id, property_name FROM properties WHERE status = 'نشط' ORDER BY property_name ASC");
$properties_list = $properties_stmt->fetchAll();

// تفعيل Select2 للقوائم في هذه الصفحة
$page_scripts = <<<JS
<script>
$(document).ready(function() {
    // استهداف كل القوائم المنسدلة في هذه الصفحة
    $('#client_id, #supplier_id, #property_id_client, #property_id_supplier').select2({
        theme: 'bootstrap-5',
        dir: "rtl",
        placeholder: "ابحث أو اختر..."
    });
});
</script>
JS;
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-chart-pie ms-2"></i>التقارير</h1>
</div>

<!-- تقارير الدفعات المتأخرة -->
<h4 class="mb-3">تقارير المتأخرات</h4>
<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-danger text-white"><h5 class="mb-0">تقرير متأخرات الإيجار (العملاء)</h5></div>
            <div class="card-body">
                <form method="POST" action="index.php?page=reports/late_rentals" target="_blank">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="property_id_client" class="form-label">فلترة حسب العقار</label>
                            <select class="form-select" id="property_id_client" name="property_id">
                                <option value="">كل العقارات</option>
                                <?php foreach ($properties_list as $property): ?>
                                    <option value="<?php echo $property['id']; ?>"><?php echo htmlspecialchars($property['property_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="as_of_date_client" class="form-label">عرض المتأخرات حتى تاريخ:</label>
                            <input type="date" class="form-control" id="as_of_date_client" name="as_of_date" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-12">
                             <button type="submit" class="btn btn-danger w-100">عرض التقرير</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-warning text-dark"><h5 class="mb-0">تقرير متأخرات التوريد (الموردين)</h5></div>
            <div class="card-body">
                <form method="POST" action="index.php?page=reports/late_supplies" target="_blank">
                    <div class="row g-3">
                         <div class="col-12">
                            <label for="property_id_supplier" class="form-label">فلترة حسب العقار</label>
                            <select class="form-select" id="property_id_supplier" name="property_id">
                                <option value="">كل العقارات</option>
                                <?php foreach ($properties_list as $property): ?>
                                    <option value="<?php echo $property['id']; ?>"><?php echo htmlspecialchars($property['property_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                         <div class="col-12">
                            <label for="as_of_date_supplier" class="form-label">عرض المتأخرات حتى تاريخ:</label>
                            <input type="date" class="form-control" id="as_of_date_supplier" name="as_of_date" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-warning w-100">عرض التقرير</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- كشوفات الحساب -->
<hr class="my-4">
<h4 class="mb-3">كشوفات الحساب</h4>
<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">كشف حساب عميل</h5></div>
            <div class="card-body">
                <form method="POST" action="index.php?page=reports/client_statement" target="_blank">
                     <div class="row g-3">
                        <div class="col-12 mb-2"><label for="client_id" class="form-label">اختر العميل</label><select class="form-select" id="client_id" name="client_id" required><option value="">-- يرجى الاختيار --</option><?php foreach ($clients_list as $client): ?><option value="<?php echo $client['id']; ?>"><?php echo htmlspecialchars($client['client_name']); ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-6"><label for="start_date_client" class="form-label">من تاريخ</label><input type="date" class="form-control" id="start_date_client" name="start_date"></div>
                        <div class="col-md-6"><label for="end_date_client" class="form-label">إلى تاريخ</label><input type="date" class="form-control" id="end_date_client" name="end_date" value="<?php echo date('Y-m-d'); ?>"></div>
                        <div class="col-12 mt-3"><div class="form-check"><input class="form-check-input" type="checkbox" name="show_opening_balance" value="1" id="show_opening_balance_client" checked><label class="form-check-label" for="show_opening_balance_client">إظهار الرصيد الافتتاحي</label></div></div>
                        <div class="col-12 mt-3"><button type="submit" class="btn btn-primary w-100">عرض التقرير</button></div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">كشف حساب مورد</h5></div>
            <div class="card-body">
                <form method="POST" action="index.php?page=reports/supplier_statement" target="_blank">
                    <div class="row g-3">
                        <div class="col-12 mb-2"><label for="supplier_id" class="form-label">اختر المورد</label><select class="form-select" id="supplier_id" name="supplier_id" required><option value="">-- يرجى الاختيار --</option><?php foreach ($suppliers_list as $supplier): ?><option value="<?php echo $supplier['id']; ?>"><?php echo htmlspecialchars($supplier['supplier_name']); ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-6"><label for="start_date_supplier" class="form-label">من تاريخ</label><input type="date" class="form-control" id="start_date_supplier" name="start_date"></div>
                        <div class="col-md-6"><label for="end_date_supplier" class="form-label">إلى تاريخ</label><input type="date" class="form-control" id="end_date_supplier" name="end_date" value="<?php echo date('Y-m-d'); ?>"></div>
                        <div class="col-12 mt-3"><div class="form-check"><input class="form-check-input" type="checkbox" name="show_opening_balance" value="1" id="show_opening_balance_supplier" checked><label class="form-check-label" for="show_opening_balance_supplier">إظهار الرصيد الافتتاحي</label></div></div>
                        <div class="col-12 mt-3"><button type="submit" class="btn btn-primary w-100">عرض التقرير</button></div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>