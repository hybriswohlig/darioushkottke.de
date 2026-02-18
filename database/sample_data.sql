-- Sample data for N&E Innovations Compliance Portal
-- This file populates the database with existing documents from the current site.
-- IMPORTANT: Run schema.sql and migration_category_metadata.sql FIRST.
-- Category IDs below assume the default categories were inserted with IDs 1–5.

-- ============================================================
-- LCA Reports (category_id = 1)
-- ============================================================
INSERT INTO documents (category_id, title, description, file_url, status, date_published, featured, tag) VALUES
(1, 'PVC + Vikang Assessment (Based on PHA)', 'Comprehensive environmental impact analysis comparing PVC + Vikang cling wrap against conventional PVC products.', 'environmental-impact-report.html', 'published', '2025-10-01', TRUE, NULL),
(1, 'Carbon Footprint Reduction Report - Vikang Composite', 'Carbon footprint reduction analysis of Vikang PVC clingfilms based on cashew shell numbers.', 'report-2b.html', 'under_review', '2025-10-10', FALSE, NULL),
(1, 'LCA Report - Liquid & Powder Disinfectants', 'Greenly LCA Report for Liquid & Powder Disinfectants', 'report-lca-disinfectants.html', 'published', '2024-01-19', FALSE, NULL);

INSERT INTO document_metadata (document_id, meta_key, meta_value, display_order) VALUES
(1, 'co2e_savings', '59%', 1),
(1, 'lca_status',   'Third-party certified', 2),

(2, 'lca_status',   'Under Review', 1),

(3, 'done_by',      'Greenly', 1),
(3, 'lca_status',   'Published', 2);

-- ============================================================
-- Certifications (category_id = 2)
-- ============================================================
INSERT INTO documents (category_id, title, description, status, featured, tag) VALUES
(2, 'ISO 14001 Environmental Management', 'Certification for environmental management systems and sustainable practices.', 'pending', FALSE, NULL),
(2, 'Food Contact Safety Certification', 'Approved for direct food contact applications with compliance to international standards.', 'published', TRUE, NULL);

INSERT INTO document_metadata (document_id, meta_key, meta_value, display_order) VALUES
(4, 'standard', '["ISO 14001"]', 1),
(4, 'done_by',  'ISO', 2),

(5, 'standard', '["FDA"]', 1),
(5, 'renewal',  '1', 2);

-- ============================================================
-- Impact Studies (category_id = 3) — no schema-defined metadata
-- ============================================================
INSERT INTO documents (category_id, title, description, status, featured, tag) VALUES
(3, 'VIKANG-Based Plasticizer Impact Study', 'Analysis of environmental benefits from using vikang additives versus traditional additives.', 'published', TRUE, NULL),
(3, 'Cashew Shell Utilization Study', 'Environmental and economic analysis of utilizing cashew shell waste in PVC production.', 'in_progress', FALSE, NULL),
(3, 'Foodwaste reduction study', 'Environmental and economic analysis of the reduction of foodwaste with the use of Vikang additives', 'planned', FALSE, NULL);

-- ============================================================
-- Technical Documentation (category_id = 4)
-- ============================================================
INSERT INTO documents (category_id, title, description, file_url, version, status, date_published, featured, tag) VALUES
(4, 'Product Specifications - Vikang PVC', 'Complete technical specifications including material composition, performance characteristics, and application guidelines.', NULL, '3.2', 'published', '2025-10-01', TRUE, NULL),
(4, 'Product Specifications - Vi-Kang BOPP', 'Technical data sheet detailing performance characteristics such as tensile strength, elongation at break, coefficient of friction, thermal shrinkage, haze, gloss, surface tension, WVTR, OTR, and thickness for the antibacterial BOPP film.', 'vikang-bopp.html', 'N/A', 'published', '2025-10-01', FALSE, NULL),
(4, 'Testing Protocols & Methods', 'Standardized testing procedures for quality control and environmental impact assessment.', NULL, 'N/A', 'published', NULL, FALSE, NULL);

INSERT INTO document_metadata (document_id, meta_key, meta_value, display_order) VALUES
(9,  'material',       'PVC', 1),
(9,  'product_type',   'Cling wrap', 2),
(9,  'doc_type_meta',  'Technical Data Sheet (TDS)', 3),
(9,  'issuer',         'N&E Innovations', 4),
(9,  'thickness',      '10', 5),

(10, 'doc_type_meta',  'Technical Data Sheet (TDS)', 1),
(10, 'issuer',         'N&E Innovations', 2),

(11, 'doc_type_meta',  'Technical Specification', 1);

-- ============================================================
-- Compliance & Standards (category_id = 5)
-- ============================================================
INSERT INTO documents (category_id, title, description, file_url, status, date_published, featured, tag) VALUES
(5, 'Regulatory Compliance Report', 'Comprehensive report on compliance with FDA food contact regulations.', 'https://drive.google.com/file/d/1iHbGGEtKS03mCoG2xCoaafwXFk6sMaAv/view?usp=drive_link', 'published', '2025-09-01', TRUE, NULL),
(5, 'REACH Compliance Documentation', 'European REACH regulation compliance for chemical substances and materials used in production.', NULL, 'published', NULL, TRUE, NULL);

INSERT INTO document_metadata (document_id, meta_key, meta_value, display_order) VALUES
(12, 'compliance_area',     'Food contact', 1),
(12, 'regulation_standard', '["FDA 21 CFR 175.300"]', 2),
(12, 'market_jurisdiction',  '["Global"]', 3),
(12, 'pass_fail_status',    'Pass', 4),

(13, 'compliance_area',     'Chemical compliance', 1),
(13, 'regulation_standard', '["REACH"]', 2),
(13, 'market_jurisdiction',  '["European Union"]', 3),
(13, 'scope_of_validity',   'Material formulation only', 4);
