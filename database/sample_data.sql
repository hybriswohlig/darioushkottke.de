-- Sample data for N&E Innovations Compliance Portal
-- This file populates the database with existing documents from the current site

-- Insert documents for LCA Reports category
INSERT INTO documents (category_id, title, description, file_url, status, date_published, featured) VALUES
(1, 'PVC + Vikang Assessment (Based on PHA)', 'Comprehensive environmental impact analysis comparing PVC + Vikang cling wrap against conventional PVC products.', 'environmental-impact-report.html', 'published', '2025-10-01', TRUE),
(1, 'Carbon Footprint Reduction Report - Vikang Composite', 'Carbon footprint reduction analysis of Vikang PVC clingfilms based on cashew shell numbers.', 'report-2b.html', 'under_review', '2025-10-10', FALSE),
(1, 'LCA Report - Liquid & Powder Disinfectants', 'Greenly LCA Report for Liquid & Powder Disinfectants', 'report-lca-disinfectants.html', 'published', '2024-01-19', FALSE);

-- Insert metadata for LCA Reports documents
INSERT INTO document_metadata (document_id, meta_key, meta_value, display_order) VALUES
(1, 'CO2e Savings', '59%', 1),
(1, 'Date', 'Oct 2025', 2),
(1, 'Status', 'Published', 3),

(2, 'Status', 'Under Review', 1),
(2, 'Date', '10 Oct 2025', 2),
(2, 'Version', '1.0', 3),

(3, 'Status', 'Finished by Greenly', 1),
(3, 'Date', '19 Jan 2024', 2),
(3, 'Version', 'Published', 3);

-- Insert documents for Certifications category
INSERT INTO documents (category_id, title, description, status, featured) VALUES
(2, 'ISO 14001 Environmental Management', 'Certification for environmental management systems and sustainable practices.', 'pending', FALSE),
(2, 'Food Contact Safety Certification', 'Approved for direct food contact applications with compliance to international standards.', 'published', TRUE);

-- Insert metadata for Certifications
INSERT INTO document_metadata (document_id, meta_key, meta_value, display_order) VALUES
(4, 'Issued', 'To be entered', 1),
(4, 'Valid Until', 'missing', 2),
(4, 'Certifying Body', 'ISO', 3),

(5, 'Standard', 'FDA', 1),
(5, 'Status', 'Active', 2),
(5, 'Renewal', 'Annual', 3);

-- Insert documents for Impact Studies category
INSERT INTO documents (category_id, title, description, status, featured) VALUES
(3, 'VIKANG-Based Plasticizer Impact Study', 'Analysis of environmental benefits from using vikang additives versus traditional additives.', 'published', TRUE),
(3, 'Cashew Shell Utilization Study', 'Environmental and economic analysis of utilizing cashew shell waste in PVC production.', 'in_progress', FALSE),
(3, 'Foodwaste reduction study', 'Environmental and economic analysis of the reduction of foodwaste with the use of Vikang additives', 'planned', FALSE);

-- Insert metadata for Impact Studies
INSERT INTO document_metadata (document_id, meta_key, meta_value, display_order) VALUES
(6, 'Reduction', 'xxx% CO2e', 1),
(6, 'Scope', 'Full LCA', 2),
(6, 'Date', 'Q4 2025', 3),

(7, 'Waste Reduction', 'TBD', 1),
(7, 'Status', 'TBD', 2),
(7, 'Impact', 'High', 3),

(8, 'Status', 'To be done', 1),
(8, 'Date', 'Not yet confirmed', 2),
(8, 'Version', 'N/a', 3);

-- Insert documents for Technical Documentation category
INSERT INTO documents (category_id, title, description, file_url, version, status, date_published, featured) VALUES
(4, 'Product Specifications - Vikang PVC', 'Complete technical specifications including material composition, performance characteristics, and application guidelines.', NULL, '3.2', 'published', '2025-10-01', TRUE),
(4, 'Product Specifications - Vi-Kang BOPP', 'Technical data sheet detailing performance characteristics such as tensile strength, elongation at break, coefficient of friction, thermal shrinkage, haze, gloss, surface tension, WVTR, OTR, and thickness for the antibacterial BOPP film.', 'vikang-bopp.html', 'N/A', 'published', '2025-10-01', FALSE),
(4, 'Testing Protocols & Methods', 'Standardized testing procedures for quality control and environmental impact assessment.', NULL, 'N/A', 'published', NULL, FALSE);

-- Insert metadata for Technical Documentation
INSERT INTO document_metadata (document_id, meta_key, meta_value, display_order) VALUES
(9, 'Version', '3.2', 1),
(9, 'Last Updated', 'Oct 2025', 2),
(9, 'Pages', '2', 3),

(10, 'Version', 'N/A', 1),
(10, 'Last Updated', 'Oct 2025', 2),
(10, 'Pages', '1', 3),

(11, 'Standards', 'ISO', 1),
(11, 'Tests', '(to be added by us)', 2),
(11, 'Frequency', 'Quarterly', 3);

-- Insert documents for Compliance & Standards category
INSERT INTO documents (category_id, title, description, file_url, status, date_published, featured) VALUES
(5, 'Regulatory Compliance Report', 'Comprehensive report on compliance with FDA food contact regulations.', 'https://drive.google.com/file/d/1iHbGGEtKS03mCoG2xCoaafwXFk6sMaAv/view?usp=drive_link', 'published', '2025-09-01', TRUE),
(5, 'REACH Compliance Documentation', 'European REACH regulation compliance for chemical substances and materials used in production.', NULL, 'published', NULL, TRUE);

-- Insert metadata for Compliance documents
INSERT INTO document_metadata (document_id, meta_key, meta_value, display_order) VALUES
(12, 'Regions', 'Global', 1),
(12, 'Status', 'Compliant', 2),
(12, 'Audit Date', 'Sep 2025', 3),

(13, 'Registration', 'Complete', 1),
(13, 'Status', 'Approved', 2),
(13, 'Valid Until', '2028', 3);

-- Note: REACH subdocuments can be added as separate document entries with a parent_id field if needed
-- For now, they're referenced in the original HTML
