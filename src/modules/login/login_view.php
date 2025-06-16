<div class="login-container">
    <div class="card shadow-sm">
        <div class="card-body p-4">
            <h3 class="card-title text-center mb-4"><?php echo htmlspecialchars($settings['site_name'] ?? 'نظام إدارة الأملاك'); ?></h3>
            <?php if (isset($_SESSION['login_error'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['login_error']; unset($_SESSION['login_error']); ?></div>
            <?php endif; ?>
            <form method="POST" action="index.php?page=handle_login">
                <div class="mb-3"><label for="username" class="form-label">اسم المستخدم</label><input type="text" class="form-control" id="username" name="username" required></div>
                <div class="mb-3"><label for="password" class="form-label">كلمة المرور</label><input type="password" class="form-control" id="password" name="password" required></div>
                <div class="d-grid"><button type="submit" class="btn btn-primary">دخول</button></div>
            </form>
        </div>
    </div>
</div>