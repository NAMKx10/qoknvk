<?php
/**
 * handlers/documents_handler.php
 * 
 * جميع معالجات AJAX الخاصة بإدارة الوثائق، بما في ذلك الربط والحفظ والتعديل.
 */

if (!defined('IS_HANDLER')) { die('Direct access not allowed.'); }

// --- معالج إضافة وثيقة (يتبع النمط القياسي) ---
if ($page === 'documents/handle_add') {
    $pdo->beginTransaction();
    
    $details_json = isset($_POST['details']) ? json_encode($_POST['details'], JSON_UNESCAPED_UNICODE) : null;
    
    $sql_doc = "INSERT INTO documents (document_type, document_name, document_number, issue_date, expiry_date, status, notes, details) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_doc = $pdo->prepare($sql_doc);
    $stmt_doc->execute([
        $_POST['document_type'], $_POST['document_name'], $_POST['document_number'],
        $_POST['issue_date'] ?: null, $_POST['expiry_date'] ?: null,
        $_POST['status'], $_POST['notes'], $details_json
    ]);
    $new_doc_id = $pdo->lastInsertId();

    if (isset($_POST['links']) && is_array($_POST['links'])) {
        $sql_link = "INSERT INTO entity_documents (document_id, entity_type, entity_id) VALUES (?, ?, ?)";
        $stmt_link = $pdo->prepare($sql_link);
        
        foreach ($_POST['links'] as $link) {
            if (!empty($link['entity_type']) && !empty($link['entity_id'])) {
                $stmt_link->execute([$new_doc_id, $link['entity_type'], $link['entity_id']]);
            }
        }
    }
    
    $pdo->commit();
    $response = ['success' => true, 'message' => 'تمت إضافة الوثيقة وربطها بنجاح.'];
}

// --- معالج تعديل وثيقة (يتبع النمط القياسي) ---
elseif ($page === 'documents/handle_edit') {
    $details_json = isset($_POST['details']) ? json_encode($_POST['details'], JSON_UNESCAPED_UNICODE) : null;
                
    $sql = "UPDATE documents SET document_name = ?, document_number = ?, issue_date = ?, expiry_date = ?, details = ?, status = ?, notes = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $_POST['document_name'], $_POST['document_number'],
        $_POST['issue_date'] ?: null, $_POST['expiry_date'] ?: null,
        $details_json, $_POST['status'], $_POST['notes'],
        $_POST['id']
    ]);
    
    $response = ['success' => true, 'message' => 'تم تحديث الوثيقة بنجاح.'];
}

// --- معالج إضافة رابط (يتبع النمط القياسي) ---
elseif ($page === 'documents/add_link_ajax') {
    $sql = "INSERT INTO entity_documents (document_id, entity_type, entity_id) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_POST['doc_id'], $_POST['entity_type'], $_POST['entity_id']]);
    $response = ['success' => true];
}

// --- معالج حذف رابط (يتبع النمط القياسي) ---
elseif ($page === 'documents/delete_link_ajax') {
    $sql = "DELETE FROM entity_documents WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_POST['link_id']]);
    $response = ['success' => true];
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

?>