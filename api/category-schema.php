<?php
/**
 * Returns the metadata field schema for a given category.
 * Used by the admin document form to render dynamic fields.
 */
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');
requireAdmin();

$categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
if ($categoryId <= 0) {
    jsonResponse(['error' => 'Missing category_id'], 400);
}

$schema = getCategoryMetadataSchema($categoryId);

$fields = array_map(function ($f) {
    return [
        'field_key'    => $f['field_key'],
        'field_label'  => $f['field_label'],
        'field_type'   => $f['field_type'],
        'field_options' => $f['field_options'] ? json_decode($f['field_options'], true) : null,
        'is_required'  => (bool) $f['is_required'],
    ];
}, $schema);

jsonResponse(['success' => true, 'fields' => $fields]);
