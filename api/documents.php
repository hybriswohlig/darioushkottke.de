<?php
/**
 * Documents API Endpoint
 * CRUD operations for documents (admin only)
 */

require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// Require admin authentication for all operations
requireAdmin();

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'GET':
        handleGet();
        break;

    case 'POST':
        handleCreate($input);
        break;

    case 'PUT':
        handleUpdate($input);
        break;

    case 'DELETE':
        handleDelete($input);
        break;

    default:
        jsonResponse(['error' => 'Method not allowed'], 405);
}

/**
 * Get documents (optionally filtered)
 */
function handleGet() {
    $db = getDB();

    // Get single document by ID
    if (isset($_GET['id'])) {
        $doc = getDocument($_GET['id']);
        if ($doc) {
            jsonResponse(['success' => true, 'document' => $doc]);
        } else {
            jsonResponse(['error' => 'Document not found'], 404);
        }
    }

    // Get all documents with optional category filter
    $categoryId = $_GET['category_id'] ?? null;

    $sql = "SELECT d.*, c.name as category_name
            FROM documents d
            JOIN categories c ON d.category_id = c.id";

    if ($categoryId) {
        $sql .= " WHERE d.category_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$categoryId]);
    } else {
        $sql .= " ORDER BY d.created_at DESC";
        $stmt = $db->query($sql);
    }

    $documents = $stmt->fetchAll();
    jsonResponse(['success' => true, 'documents' => $documents]);
}

/**
 * Create new document
 */
function handleCreate($input) {
    // Validate required fields
    $required = ['category_id', 'title', 'description'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            jsonResponse(['error' => "Missing required field: $field"], 400);
        }
    }

    $db = getDB();

    try {
        $db->beginTransaction();

        // Insert document
        $stmt = $db->prepare("
            INSERT INTO documents (category_id, title, description, file_url, document_type, file_path,
                                   thumbnail_url, status, version, date_published, featured, tag)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $documentType = $input['document_type'] ?? 'link';
        $filePath = null;
        $fileUrl = $input['file_url'] ?? null;

        if ($documentType === 'pdf') {
            $filePath = $input['file_path'] ?? null;
            $fileUrl = null; // PDFs don't use external URLs
            if (empty($filePath)) {
                jsonResponse(['error' => 'PDF document requires a file_path'], 400);
            }
        }

        $stmt->execute([
            $input['category_id'],
            $input['title'],
            $input['description'],
            $fileUrl,
            $documentType,
            $filePath,
            $input['thumbnail_url'] ?? null,
            $input['status'] ?? 'published',
            $input['version'] ?? null,
            $input['date_published'] ?? null,
            isset($input['featured']) ? (int)$input['featured'] : 0,
            $input['tag'] ?? null
        ]);

        $documentId = $db->lastInsertId();

        // Insert metadata if provided (skip date_from_doc sentinel — those are resolved at display time)
        if (!empty($input['metadata']) && is_array($input['metadata'])) {
            $metaStmt = $db->prepare("
                INSERT INTO document_metadata (document_id, meta_key, meta_value, display_order)
                VALUES (?, ?, ?, ?)
            ");

            foreach ($input['metadata'] as $index => $meta) {
                if (($meta['value'] ?? '') === '__date_from_doc__') continue;
                $metaStmt->execute([
                    $documentId,
                    $meta['key'],
                    $meta['value'],
                    $index
                ]);
            }
        }

        $db->commit();

        // Log activity
        logActivity('create_document', 'document', $documentId, "Created document: {$input['title']}");

        jsonResponse([
            'success' => true,
            'message' => 'Document created successfully',
            'document_id' => $documentId
        ], 201);

    } catch (PDOException $e) {
        $db->rollBack();
        error_log("Document creation error: " . $e->getMessage());
        jsonResponse(['error' => 'Failed to create document'], 500);
    }
}

/**
 * Update existing document
 */
function handleUpdate($input) {
    if (empty($input['id'])) {
        jsonResponse(['error' => 'Missing document ID'], 400);
    }

    $db = getDB();

    try {
        $db->beginTransaction();

        // Build update query dynamically
        $updateFields = [];
        $params = [];

        $allowedFields = ['category_id', 'title', 'description', 'file_url', 'document_type',
                          'file_path', 'thumbnail_url', 'status', 'version', 'date_published',
                          'featured', 'tag'];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $input)) {
                $updateFields[] = "$field = ?";
                $params[] = $input[$field];
            }
        }

        if (empty($updateFields)) {
            jsonResponse(['error' => 'No fields to update'], 400);
        }

        $params[] = $input['id'];

        $sql = "UPDATE documents SET " . implode(', ', $updateFields) . " WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        // Update metadata if provided (skip date_from_doc sentinel)
        if (isset($input['metadata']) && is_array($input['metadata'])) {
            $db->prepare("DELETE FROM document_metadata WHERE document_id = ?")->execute([$input['id']]);

            $metaStmt = $db->prepare("
                INSERT INTO document_metadata (document_id, meta_key, meta_value, display_order)
                VALUES (?, ?, ?, ?)
            ");

            foreach ($input['metadata'] as $index => $meta) {
                if (($meta['value'] ?? '') === '__date_from_doc__') continue;
                $metaStmt->execute([
                    $input['id'],
                    $meta['key'],
                    $meta['value'],
                    $index
                ]);
            }
        }

        $db->commit();

        // Log activity
        logActivity('update_document', 'document', $input['id'], "Updated document");

        jsonResponse([
            'success' => true,
            'message' => 'Document updated successfully'
        ]);

    } catch (PDOException $e) {
        $db->rollBack();
        error_log("Document update error: " . $e->getMessage());
        jsonResponse(['error' => 'Failed to update document'], 500);
    }
}

/**
 * Delete document
 */
function handleDelete($input) {
    if (empty($input['id'])) {
        jsonResponse(['error' => 'Missing document ID'], 400);
    }

    $db = getDB();

    try {
        // Get document for logging and file cleanup
        $doc = getDocument($input['id']);
        if (!$doc) {
            jsonResponse(['error' => 'Document not found'], 404);
        }

        // Delete PDF file from disk if this is a PDF document
        if (($doc['document_type'] ?? '') === 'pdf' && !empty($doc['file_path'])) {
            $fullPath = __DIR__ . '/../' . $doc['file_path'];
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }

        $stmt = $db->prepare("DELETE FROM documents WHERE id = ?");
        $stmt->execute([$input['id']]);

        // Log activity
        logActivity('delete_document', 'document', $input['id'], "Deleted document: {$doc['title']}");

        jsonResponse([
            'success' => true,
            'message' => 'Document deleted successfully'
        ]);

    } catch (PDOException $e) {
        error_log("Document deletion error: " . $e->getMessage());
        jsonResponse(['error' => 'Failed to delete document'], 500);
    }
}
