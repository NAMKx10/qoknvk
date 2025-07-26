<!-- src/modules/settings/settings_view.php (النسخة النهائية الديناميكية) -->

<?php
// مصفوفة لترجمة أسماء التبويبات إلى العربية
$tab_names = [
    'general' => 'عامة',
    'appearance' => 'المظهر',
    'operation' => 'التشغيل',
    'data' => 'البيانات والنسخ الاحتياطي',
    'security' => 'الأمان',
    'integrations' => 'التكاملات'
];
?>

<div class="page-header d-print-none"><div class="container-xl"><div class="row g-2 align-items-center"><div class="col"><h2 class="page-title">الإعدادات العامة</h2></div></div></div></div>
<div class="page-body"><div class="container-xl">
    <form action="index.php?page=settings/handle_update" method="POST" class="card" enctype="multipart/form-data">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" data-bs-toggle="tabs">
                <?php $is_first_tab = true; foreach (array_keys($grouped_settings) as $group_key): ?>
                    <li class="nav-item">
                        <a href="#tab-<?= $group_key ?>" class="nav-link <?= $is_first_tab ? 'active' : '' ?>" data-bs-toggle="tab">
                            <?= $tab_names[$group_key] ?? ucfirst($group_key) ?>
                        </a>
                    </li>
                <?php $is_first_tab = false; endforeach; ?>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content">
                <?php $is_first_tab = true; foreach ($grouped_settings as $group_key => $settings): ?>
                    <div class="tab-pane <?= $is_first_tab ? 'active' : '' ?>" id="tab-<?= $group_key ?>">
                        <div class="row">
                            <?php foreach ($settings as $setting): ?>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label"><?= htmlspecialchars($setting['description']) ?></label>
                                    <?php
                                    $key = htmlspecialchars($setting['setting_key']);
                                    $value = htmlspecialchars($setting['setting_value']);
                                    switch ($setting['type']) {
                                        case 'textarea':
                                            echo "<textarea name='{$key}' class='form-control' rows='3'>{$value}</textarea>";
                                            break;
                                        case 'toggle':
                                            $checked = $value == '1' ? 'checked' : '';
                                            echo "<label class='form-check form-switch'><input class='form-check-input' type='checkbox' name='{$key}' value='1' {$checked}></label>";
                                            break;
                                        case 'select':
                                            $options = json_decode($setting['options'], true);
                                            echo "<select name='{$key}' class='form-select'>";
                                            if (is_array($options)) {
                                                foreach ($options as $opt_key => $opt_val) {
                                                    $selected = ($value == $opt_key) ? 'selected' : '';
                                                    echo "<option value='".htmlspecialchars($opt_key)."' {$selected}>".htmlspecialchars($opt_val)."</option>";
                                                }
                                            }
                                            echo "</select>";
                                            break;
                                        case 'timezone':
                                            echo "<select name='{$key}' class='form-select select2-init'>";
                                            foreach ($timezones as $tz) {
                                                $selected = ($value == $tz) ? 'selected' : '';
                                                echo "<option value='{$tz}' {$selected}>{$tz}</option>";
                                            }
                                            echo "</select>";
                                            break;
                                        case 'password':
                                            echo "<input type='password' name='{$key}' class='form-control' value='{$value}'>";
                                            break;
                                        case 'text_readonly':
                                            echo "<input type='text' class='form-control' value='{$value}' readonly>";
                                            break;
                                        default:
                                            echo "<input type='text' name='{$key}' class='form-control' value='{$value}'>";
                                    }
                                    ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php $is_first_tab = false; endforeach; ?>
            </div>
        </div>
        <div class="card-footer text-end"><button type="submit" class="btn btn-primary">حفظ الإعدادات</button></div>
    </form>
</div></div>