<?php
$stmt = $pdo->query("SELECT * FROM permissions ORDER BY permission_group, id");
$permissions = $stmt->fetchAll();
?>
<div id="form-error-message" class="alert alert-danger" style="display:none;"></div>
<div class="row">
    <div class="col-md-8">
        <h5>الصلاحيات الحالية في النظام</h5>
        <table class="table table-sm table-bordered">
            <thead><tr><th>المجموعة</th><th>المفتاح البرمجي</th><th>الوصف</th></tr></thead>
            <tbody>
                <?php foreach($permissions as $perm): ?>
                <tr><td><?php echo htmlspecialchars($perm['permission_group']); ?></td><td><code><?php echo htmlspecialchars($perm['permission_key']); ?></code></td><td><?php echo htmlspecialchars($perm['description']); ?></td></tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="col-md-4">
        <h5>إضافة صلاحية جديدة</h5>
        <form method="POST" action="index.php?page=roles/handle_add_permission_ajax" class="ajax-form">
            <div class="mb-3"><label for="permission_key" class="form-label">المفتاح (انجليزي)</label><input type="text" class="form-control" name="permission_key" required></div>
            <div class="mb-3"><label for="permission_group" class="form-label">المجموعة</label><input type="text" class="form-control" name="permission_group" required></div>
            <div class="mb-3"><label for="description" class="form-label">الوصف</label><input type="text" class="form-control" name="description" required></div>
            <button type="submit" class="btn btn-success">إضافة الصلاحية</button>
        </form>
    </div>
</div>