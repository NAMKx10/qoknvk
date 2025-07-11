<?php

// handlers/documents_handler.php (النسخة النهائية الصحيحة بالكامل)

if (!defined('IS_HANDLER')) { die('Direct access not allowed.'); }

// --- معالج إضافة وثيقة (تم تطويره) ---
if ($page === 'documents/handle_add') {
    $pdo->beginTransaction();
    try {
        // 1. حفظ بيانات الوثيقة الأساسية
        $details_json = isset($_POST['details']) ? json_encode($_POST['details']) : null;
        $sql_doc = "INSERT INTO documents (document_type, document_name, document_number, issue_date, expiry_date, status, notes, details) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_doc = $pdo->prepare($sql_doc);
        $stmt_doc->execute([
            $_POST['document_type'], $_POST['document_name'], $_POST['document_number'],
            empty($_POST['issue_date']) ? null : $_POST['issue_date'],
            empty($_POST['expiry_date']) ? null : $_POST['expiry_date'],
            $_POST['status'], $_POST['notes'], $details_json
        ]);
        $doc_id = $pdo->lastInsertId();

        // 2. معالجة الربط المتقدم
        $property_id = $_POST['linked_property_id'] ?? null;
        $owner_ids = $_POST['linked_owner_ids'] ?? [];

        // 2a. ربط العقار بالوثيقة
        if ($property_id) {
            $stmt_link = $pdo->prepare("INSERT INTO entity_documents (document_id, entity_type, entity_id) VALUES (?, 'property', ?)");
            $stmt_link->execute([$doc_id, $property_id]);
        }

        // 2b. ربط الملاك بالوثيقة وبالعقار
        if (!empty($owner_ids) && $property_id) {
            $stmt_link_owner = $pdo->prepare("INSERT INTO entity_documents (document_id, entity_type, entity_id) VALUES (?, 'owner', ?)");
            $stmt_prop_owner = $pdo->prepare("INSERT INTO property_owners (property_id, owner_id) VALUES (?, ?)");
            foreach ($owner_ids as $owner_id) {
                $stmt_link_owner->execute([$doc_id, $owner_id]);
                $stmt_prop_owner->execute([$property_id, $owner_id]);
            }
        }
        
        $pdo->commit();
        $response = ['success' => true, 'message' => 'تمت إضافة الوثيقة وربطها بنجاح.'];
    } catch (Exception $e) {
        $pdo->rollBack();
        $response = ['success' => false, 'message' => 'حدث خطأ: ' . $e->getMessage()];
    }
}

// --- معالج تعديل وثيقة (تم تطويره بالكامل) ---
// --- معالج تعديل وثيقة (النسخة النهائية المصححة) ---
elseif ($page === 'documents/handle_edit') {
    $pdo->beginTransaction();
    try {
        $doc_id = $_POST['id'];

        // 1. تحديث بيانات الوثيقة الأساسية
        $details_json = isset($_POST['details']) ? json_encode($_POST['details'], JSON_UNESCAPED_UNICODE) : null;
        $sql = "UPDATE documents SET document_name = ?, document_number = ?, issue_date = ?, expiry_date = ?, status = ?, notes = ?, details = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST['document_name'], $_POST['document_number'],
            empty($_POST['issue_date']) ? null : $_POST['issue_date'],
            empty($_POST['expiry_date']) ? null : $_POST['expiry_date'],
            $_POST['status'], $_POST['notes'],
            $details_json, $doc_id
        ]);

        // 2. معالجة الربط المتقدم
        $property_id = !empty($_POST['linked_property_id']) ? $_POST['linked_property_id'] : null;
        $owner_ids = $_POST['linked_owner_ids'] ?? [];

        // 3. حذف الروابط القديمة لضمان عدم وجود تكرار
        $pdo->prepare("DELETE FROM entity_documents WHERE document_id = ?")->execute([$doc_id]);
        if($property_id) {
             // فقط نحذف ملاك العقار المحدد لنعيد بناءهم
             $pdo->prepare("DELETE FROM property_owners WHERE property_id = ?")->execute([$property_id]);
        }

        // 4. إضافة الروابط الجديدة بناء على النموذج
        if ($property_id) {
            $stmt_link = $pdo->prepare("INSERT INTO entity_documents (document_id, entity_type, entity_id) VALUES (?, 'property', ?)");
            $stmt_link->execute([$doc_id, $property_id]);
        }
        if (!empty($owner_ids) && $property_id) {
            $stmt_link_owner = $pdo->prepare("INSERT INTO entity_documents (document_id, entity_type, entity_id) VALUES (?, 'owner', ?)");
            $stmt_prop_owner = $pdo->prepare("INSERT INTO property_owners (property_id, owner_id) VALUES (?, ?)");
            foreach ($owner_ids as $owner_id) {
                if (!empty($owner_id)) {
                    $stmt_link_owner->execute([$doc_id, $owner_id]);
                    $stmt_prop_owner->execute([$property_id, $owner_id]);
                }
            }
        }
        
        $pdo->commit();
        $response = ['success' => true, 'message' => 'تم تحديث الوثيقة والروابط بنجاح.'];

    } catch (Exception $e) {
        $pdo->rollBack();
        $response = ['success' => false, 'message' => 'حدث خطأ: ' . $e->getMessage()];
    }
}


// === المعالجات المساعدة (Helper Handlers) التي لها استجابة خاصة ===
// هذه المعالجات ستخرج مباشرة لأنها لا تتبع نمط الاستجابة القياسي

elseif ($page === 'documents/get_entities_for_linking_ajax') {
    ob_end_clean(); // نظف أي مخرجات سابقة
    header('Content-Type: application/json; charset=utf-8');

    $type = $_GET['type'] ?? '';
    $branch_id = $_GET['branch_id'] ?? null;
    $data = [];
    $params = [];
    $sql = '';

    if ($branch_id) { $params[] = $branch_id; }

    switch ($type) {
        case 'property': 
            $sql = "SELECT id, property_name as text FROM properties WHERE deleted_at IS NULL ";
            if ($branch_id) { $sql .= ' AND branch_id = ?'; }
            $sql .= " ORDER BY text"; 
            break;
        case 'owner':
            $sql = "SELECT DISTINCT o.id, o.owner_name as text FROM owners o ";
            if ($branch_id) { $sql .= " JOIN owner_branches ob ON o.id = ob.owner_id WHERE o.deleted_at IS NULL AND ob.branch_id = ?"; }
            else { $sql .= " WHERE o.deleted_at IS NULL"; }
            $sql .= " ORDER BY text";
            break;
        // ... أضف حالات أخرى هنا مثل client, supplier
    }
    
    if ($sql) { 
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    echo json_encode($data);
    exit();
}
      
elseif ($page === 'documents/get_linked_entities_ajax') {
    ob_end_clean(); // نظف أي مخرجات سابقة
    header('Content-Type: text/html; charset=utf-8');
    
    $doc_id = $_GET['doc_id'] ?? 0;
    
    $sql = "SELECT ed.id, ed.entity_type, ed.entity_id, 
                   CASE 
                       WHEN ed.entity_type = 'property' THEN p.property_name
                       WHEN ed.entity_type = 'owner' THEN o.owner_name
                       WHEN ed.entity_type = 'client' THEN c.client_name
                       WHEN ed.entity_type = 'supplier' THEN s.supplier_name
                       WHEN ed.entity_type = 'branch' THEN b.branch_name
                       ELSE 'N/A' 
                   END as entity_name
            FROM entity_documents ed
            LEFT JOIN properties p ON ed.entity_id = p.id AND ed.entity_type = 'property'
            LEFT JOIN owners o ON ed.entity_id = o.id AND ed.entity_type = 'owner'
            LEFT JOIN clients c ON ed.entity_id = c.id AND ed.entity_type = 'client'
            LEFT JOIN suppliers s ON ed.entity_id = s.id AND ed.entity_type = 'supplier'
            LEFT JOIN branches b ON ed.entity_id = b.id AND ed.entity_type = 'branch'
            WHERE ed.document_id = ?";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$doc_id]);
    $links = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $entity_type_names = ['property' => 'عقار', 'owner' => 'مالك', 'client' => 'عميل', 'supplier' => 'مورد', 'branch' => 'فرع'];

    echo '<table class="table table-sm table-hover table-striped mb-0">';
    echo '<thead><tr><th>نوع الكيان</th><th>اسم الكيان</th><th class="w-1"></th></tr></thead><tbody>';

    if (empty($links)) {
        echo '<tr><td colspan="3" class="text-center text-muted p-3">لم يتم ربط أي كيانات.</td></tr>';
    } else {
        foreach ($links as $link) {
            echo '<tr>';
            echo '<td><span class="badge bg-secondary-lt">' . htmlspecialchars($entity_type_names[$link['entity_type']] ?? $link['entity_type']) . '</span></td>';
            echo '<td>' . htmlspecialchars($link['entity_name'] ?: "غير معروف (ID: {$link['entity_id']})") . '</td>';
            echo '<td><a href="#" class="btn btn-sm btn-ghost-danger delete-link-btn" data-link-id="' . $link['id'] . '">حذف</a></td>';
            echo '</tr>';
        }
    }

    echo '</tbody></table>';
    exit();
}


// --- المعالج الجديد لجلب إعدادات نوع الوثيقة ---
elseif ($page === 'documents/get_type_config_ajax') {

    $document_type_key = $_GET['document_type_key'] ?? '';
    
    // إعداد استجابة افتراضية
    $config_response = [
        'success' => false,
        'config' => null,
        'custom_fields' => []
    ];

    if (!empty($document_type_key)) {
        // نبحث عن الخيار المطابق في جدول الإعدادات
        $stmt = $pdo->prepare("
            SELECT custom_fields_schema, advanced_config 
            FROM lookup_options 
            WHERE group_key = 'documents_type' AND option_key = ?
        ");
        $stmt->execute([$document_type_key]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $config_response['success'] = true;
            // نقوم بفك ترميز بيانات JSON المخزنة في قاعدة البيانات
            $config_response['config'] = json_decode($result['advanced_config'] ?? '[]', true);
            $config_response['custom_fields'] = json_decode($result['custom_fields_schema'] ?? '[]', true);
        }
    }
    
    // نرجع استجابة JSON مباشرة ونوقف التنفيذ
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($config_response);
    exit();
}

?>