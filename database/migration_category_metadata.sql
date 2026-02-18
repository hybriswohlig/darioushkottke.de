-- Migration: Category-specific metadata schemas
-- Each category defines its own set of structured metadata fields for documents.
-- document_metadata stores key-value pairs where meta_key matches field_key here.

-- Widen meta_value to hold JSON arrays (multi-select, freetext_multi)
ALTER TABLE document_metadata MODIFY meta_value TEXT;

-- Schema definition table
CREATE TABLE IF NOT EXISTS category_metadata_fields (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    field_key VARCHAR(100) NOT NULL,
    field_label VARCHAR(150) NOT NULL,
    field_type ENUM(
        'text',
        'dropdown_single',
        'dropdown_multi',
        'boolean',
        'number_unit',
        'range',
        'date_from_doc',
        'freetext_multi'
    ) NOT NULL DEFAULT 'text',
    field_options JSON DEFAULT NULL,
    display_order INT DEFAULT 0,
    is_required TINYINT(1) DEFAULT 0,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    UNIQUE KEY uk_category_field (category_id, field_key),
    INDEX idx_category (category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- LCA Reports
-- ============================================================
INSERT INTO category_metadata_fields (category_id, field_key, field_label, field_type, field_options, display_order)
SELECT id, 'done_by',       'Done by',       'text',          NULL, 1 FROM categories WHERE slug = 'lca-reports'
UNION ALL
SELECT id, 'co2e_savings',  'CO₂e Savings',  'text',          NULL, 2 FROM categories WHERE slug = 'lca-reports'
UNION ALL
SELECT id, 'date',          'Date',           'date_from_doc', NULL, 3 FROM categories WHERE slug = 'lca-reports'
UNION ALL
SELECT id, 'lca_status',    'Status',         'text',          NULL, 4 FROM categories WHERE slug = 'lca-reports';

-- ============================================================
-- Certifications
-- ============================================================
INSERT INTO category_metadata_fields (category_id, field_key, field_label, field_type, field_options, display_order)
SELECT id, 'standard',      'Standard',       'freetext_multi', NULL, 1 FROM categories WHERE slug = 'certifications'
UNION ALL
SELECT id, 'done_by',       'Done by',        'text',           NULL, 2 FROM categories WHERE slug = 'certifications'
UNION ALL
SELECT id, 'issuing_date',  'Issuing Date',   'date_from_doc',  NULL, 3 FROM categories WHERE slug = 'certifications'
UNION ALL
SELECT id, 'renewal',       'Renewal',        'number_unit',    '{"unit":"Years"}', 4 FROM categories WHERE slug = 'certifications';

-- ============================================================
-- Impact Studies  (no metadata fields for now)
-- ============================================================

-- ============================================================
-- Technical Documentation
-- ============================================================
INSERT INTO category_metadata_fields (category_id, field_key, field_label, field_type, field_options, display_order)
SELECT id, 'material',       'Material',       'dropdown_single', '["PVC","PBAT","LDPE","Plastic (unspecified)"]', 1
  FROM categories WHERE slug = 'technical-docs'
UNION ALL
SELECT id, 'product_type',   'Product Type',   'dropdown_single', '["Cling wrap","Film / sheet","Packaging film","Food trays","Trash bags"]', 2
  FROM categories WHERE slug = 'technical-docs'
UNION ALL
SELECT id, 'doc_type_meta',  'Document Type',  'dropdown_single', '["Technical Data Sheet (TDS)","Safety Data Sheet (SDS / MSDS)","Technical Specification","Product Data Sheet"]', 3
  FROM categories WHERE slug = 'technical-docs'
UNION ALL
SELECT id, 'issuer',         'Issuer',         'dropdown_single', '["N&E Innovations","SGS","TÜV Austria","Intertek","CTI"]', 4
  FROM categories WHERE slug = 'technical-docs'
UNION ALL
SELECT id, 'date_of_issue',  'Date of Issue',  'date_from_doc',   NULL, 5
  FROM categories WHERE slug = 'technical-docs'
UNION ALL
SELECT id, 'applies_to',     'Applies To',     'dropdown_single', '["Final product","Material Formulation","Additive / masterbatch"]', 6
  FROM categories WHERE slug = 'technical-docs'
UNION ALL
SELECT id, 'thickness',      'Thickness',      'number_unit',     '{"unit":"μm"}', 7
  FROM categories WHERE slug = 'technical-docs'
UNION ALL
SELECT id, 'temp_resistance', 'Temperature Resistance', 'range', '{"unit":"°C"}', 8
  FROM categories WHERE slug = 'technical-docs';

-- ============================================================
-- Compliance & Standards
-- ============================================================
INSERT INTO category_metadata_fields (category_id, field_key, field_label, field_type, field_options, display_order)
SELECT id, 'compliance_area',    'Compliance Area',        'dropdown_single',  '["Food contact","Chemical compliance","Antimicrobial / performance claims","Environmental / sustainability","Packaging compliance","Product safety"]', 1
  FROM categories WHERE slug = 'compliance'
UNION ALL
SELECT id, 'regulation_standard','Regulation / Standard',  'freetext_multi',   NULL, 2
  FROM categories WHERE slug = 'compliance'
UNION ALL
SELECT id, 'market_jurisdiction','Market / Jurisdiction',  'dropdown_multi',   '["European Union","United States","United Kingdom","Thailand","Singapore","Global"]', 3
  FROM categories WHERE slug = 'compliance'
UNION ALL
SELECT id, 'material',           'Material',               'dropdown_single',  '["PVC","PBAT","LDPE","Composite plastic","Plastic (unspecified)"]', 4
  FROM categories WHERE slug = 'compliance'
UNION ALL
SELECT id, 'product_type',       'Product Type',           'dropdown_single',  '["Cling wrap","Film / sheet","Packaging film"]', 5
  FROM categories WHERE slug = 'compliance'
UNION ALL
SELECT id, 'doc_type_meta',      'Document Type',          'dropdown_single',  '["Test report","Certification","Compliance declaration","Regulatory opinion"]', 6
  FROM categories WHERE slug = 'compliance'
UNION ALL
SELECT id, 'issuing_body',       'Issuing Body',           'dropdown_single',  '["SGS","SGS Thailand","SGS Singapore","TÜV Austria","Intertek","CTI"]', 7
  FROM categories WHERE slug = 'compliance'
UNION ALL
SELECT id, 'pass_fail_status',   'Pass / Fail Status',     'dropdown_single',  '["Pass","Conditional pass","Informational only","Fail"]', 8
  FROM categories WHERE slug = 'compliance'
UNION ALL
SELECT id, 'date_of_issue',      'Date of Issue',          'date_from_doc',    NULL, 9
  FROM categories WHERE slug = 'compliance'
UNION ALL
SELECT id, 'scope_of_validity',  'Scope of Validity',      'dropdown_single',  '["Final product only","Material formulation only","Additive within final product","Informational / reference only"]', 10
  FROM categories WHERE slug = 'compliance'
UNION ALL
SELECT id, 'key_parameters',     'Key Parameters Covered', 'dropdown_multi',   '["Overall migration","Specific migration (metals)","Vinyl chloride","Primary aromatic amines","Pathogenic microorganisms","Antibacterial efficacy","Biodegradation","SVHC screening"]', 11
  FROM categories WHERE slug = 'compliance'
UNION ALL
SELECT id, 'customer_facing',    'Customer-facing',        'boolean',          NULL, 12
  FROM categories WHERE slug = 'compliance'
UNION ALL
SELECT id, 'notes_limitations',  'Notes / Limitations',    'text',             NULL, 13
  FROM categories WHERE slug = 'compliance';
