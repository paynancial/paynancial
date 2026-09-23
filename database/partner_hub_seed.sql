-- =====================================================================
-- Paynancial Partner Hub — seed data
-- Run after partner_hub_schema.sql. Safe to re-run (ON DUPLICATE / IGNORE).
-- =====================================================================

-- ---------------------------------------------------------------------
-- Partner team roles
-- ---------------------------------------------------------------------
INSERT INTO partner_roles (slug, name) VALUES
  ('owner',          'Partner Owner'),
  ('admin',          'Partner Admin'),
  ('sales_manager',  'Sales Manager'),
  ('sales_executive','Sales Executive'),
  ('finance',        'Finance User'),
  ('support',        'Support User'),
  ('viewer',         'Viewer')
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Owner + Admin: full access. Others: scoped per module.
INSERT IGNORE INTO partner_role_permissions (role_id, module, can_view, can_edit)
SELECT r.id, m.module, 1, 1
FROM partner_roles r
CROSS JOIN (
  SELECT 'customers' AS module UNION ALL SELECT 'applications' UNION ALL SELECT 'transactions'
  UNION ALL SELECT 'settlements' UNION ALL SELECT 'commission' UNION ALL SELECT 'reports'
  UNION ALL SELECT 'support' UNION ALL SELECT 'team' UNION ALL SELECT 'documents'
) m
WHERE r.slug IN ('owner', 'admin');

INSERT IGNORE INTO partner_role_permissions (role_id, module, can_view, can_edit) VALUES
  ((SELECT id FROM partner_roles WHERE slug = 'sales_manager'),  'customers',     1, 1),
  ((SELECT id FROM partner_roles WHERE slug = 'sales_manager'),  'applications',  1, 1),
  ((SELECT id FROM partner_roles WHERE slug = 'sales_manager'),  'reports',       1, 0),
  ((SELECT id FROM partner_roles WHERE slug = 'sales_manager'),  'support',       1, 1),
  ((SELECT id FROM partner_roles WHERE slug = 'sales_executive'),'customers',     1, 1),
  ((SELECT id FROM partner_roles WHERE slug = 'sales_executive'),'applications',  1, 1),
  ((SELECT id FROM partner_roles WHERE slug = 'finance'),        'transactions',  1, 0),
  ((SELECT id FROM partner_roles WHERE slug = 'finance'),        'settlements',   1, 0),
  ((SELECT id FROM partner_roles WHERE slug = 'finance'),        'commission',    1, 0),
  ((SELECT id FROM partner_roles WHERE slug = 'finance'),        'reports',       1, 0),
  ((SELECT id FROM partner_roles WHERE slug = 'support'),        'support',       1, 1),
  ((SELECT id FROM partner_roles WHERE slug = 'support'),        'customers',     1, 0),
  ((SELECT id FROM partner_roles WHERE slug = 'viewer'),         'customers',     1, 0),
  ((SELECT id FROM partner_roles WHERE slug = 'viewer'),         'transactions',  1, 0),
  ((SELECT id FROM partner_roles WHERE slug = 'viewer'),         'reports',       1, 0);

-- ---------------------------------------------------------------------
-- Solution catalog (matches the products already described on the
-- public marketing site's /products page)
-- ---------------------------------------------------------------------
INSERT INTO products (slug, name, category, short_description, complexity, commission_eligible, sort_order) VALUES
  ('payment-gateway',    'Payment Gateway',    'payment_acceptance', 'Accept cards, UPI, netbanking and wallets through a single integration.', 'medium', 1, 10),
  ('payment-links',      'Payment Links',      'payment_acceptance', 'Generate a secure, shareable payment link in seconds — no code required.', 'low',    1, 20),
  ('payment-pages',      'Payment Pages',      'payment_acceptance', 'Branded, no-code checkout pages for campaigns or one-off collections.', 'low',    1, 30),
  ('ecommerce-payments', 'E-Commerce Payments','payment_acceptance', 'Storefront-ready checkout for online retail businesses.', 'medium', 1, 40),
  ('business-dashboard', 'Business Dashboard', 'payment_management', 'A single console for collections, settlements, refunds and analytics.', 'low', 0, 50),
  ('payment-analytics',  'Payment Analytics',  'payment_management', 'Real-time dashboards and exportable reports on transaction performance.', 'low', 0, 60),
  ('refund-management',  'Refund Management',  'payment_management', 'Initiate and track refunds through to settlement.', 'low', 0, 70),
  ('payouts',            'Business Payouts',   'payouts',            'Send payments to vendors, employees and partners from one dashboard.', 'medium', 1, 80),
  ('settlement-mgmt',    'Settlement Management','payouts',          'Track settlement cycles and reconciliation in one place.', 'low', 0, 90),
  ('payment-apis',       'Payment APIs',       'integration',        'REST APIs for payments, payouts, refunds and transactions.', 'high', 1, 100),
  ('webhooks',           'Webhooks',           'integration',        'Real-time event notifications for payment and settlement status changes.', 'medium', 0, 110),
  ('custom-integration', 'Custom Integration', 'integration',        'Tailored integration work for non-standard platforms.', 'high', 1, 120),
  ('enterprise-solutions','Enterprise Payment Solutions','business_solutions','Custom infrastructure, dedicated support and volume-ready architecture.', 'high', 1, 130),
  ('multi-platform',     'Multi-Platform Payment Solutions','business_solutions','Unified payment flows across web, mobile and in-store channels.', 'high', 1, 140),
  ('payment-insights',   'Payment Insights',   'ai_intelligence',    'Pattern recognition that surfaces useful signals from transaction data.', 'low', 0, 150),
  ('anomaly-detection',  'Transaction Anomaly Insights','ai_intelligence','Surfaces unusual transaction patterns for review.', 'medium', 0, 160)
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO product_features (product_id, feature, sort_order)
SELECT p.id, f.feature, f.sort_order FROM products p
JOIN (
  SELECT 'payment-gateway' AS slug, 'Cards, UPI, netbanking & wallets' AS feature, 1 AS sort_order UNION ALL
  SELECT 'payment-gateway', 'Real-time settlement reporting', 2 UNION ALL
  SELECT 'payment-gateway', 'Encrypted by default', 3 UNION ALL
  SELECT 'payment-links', 'No code required', 1 UNION ALL
  SELECT 'payment-links', 'Share via link, email or WhatsApp', 2 UNION ALL
  SELECT 'payment-apis', 'Sandbox environment', 1 UNION ALL
  SELECT 'payment-apis', 'PHP, JavaScript & Python SDKs', 2 UNION ALL
  SELECT 'payment-apis', 'Webhook event delivery', 3 UNION ALL
  SELECT 'payouts', 'Bulk and single payouts', 1 UNION ALL
  SELECT 'payouts', 'Vendor & employee disbursement', 2
) f ON f.slug = p.slug
WHERE NOT EXISTS (SELECT 1 FROM product_features WHERE product_id = p.id);

-- ---------------------------------------------------------------------
-- Recommendation rules (admin-editable; this is a starting set)
-- ---------------------------------------------------------------------
INSERT INTO recommendation_rules (condition_key, condition_value, product_id, reason_text)
SELECT c.condition_key, c.condition_value, p.id, c.reason_text
FROM (
  SELECT 'customer_type' AS condition_key, 'ecommerce' AS condition_value, 'payment-gateway' AS slug, 'E-commerce businesses need a checkout that supports the payment methods their customers already use.' AS reason_text UNION ALL
  SELECT 'customer_type', 'ecommerce', 'ecommerce-payments', 'Purpose-built checkout for online retail storefronts.' UNION ALL
  SELECT 'customer_type', 'ecommerce', 'payment-analytics', 'Real-time visibility into checkout and payment performance.' UNION ALL
  SELECT 'requirement', 'api_integration', 'payment-apis', 'Selected API integration as a requirement.' UNION ALL
  SELECT 'requirement', 'payouts', 'payouts', 'Selected payouts as a requirement.' UNION ALL
  SELECT 'requirement', 'payment_links', 'payment-links', 'Selected payment links as a requirement.' UNION ALL
  SELECT 'requirement', 'payment_analytics', 'payment-analytics', 'Selected payment analytics as a requirement.' UNION ALL
  SELECT 'requirement', 'business_dashboard', 'business-dashboard', 'Selected business dashboard as a requirement.' UNION ALL
  SELECT 'requirement', 'custom_integration', 'custom-integration', 'Selected custom integration as a requirement.' UNION ALL
  SELECT 'no_website', '1', 'payment-links', 'No website yet — payment links need no storefront to start collecting.' UNION ALL
  SELECT 'no_website', '1', 'payment-pages', 'A hosted payment page stands in for a checkout page.' UNION ALL
  SELECT 'is_international', '1', 'payment-apis', 'Cross-border flows are easiest to manage through the API layer.' UNION ALL
  SELECT 'is_enterprise', '1', 'enterprise-solutions', 'Enterprise customers get dedicated, volume-ready infrastructure.' UNION ALL
  SELECT 'is_enterprise', '1', 'custom-integration', 'Enterprise integrations typically need tailored work.' UNION ALL
  SELECT 'is_enterprise', '1', 'payment-analytics', 'Enterprise customers need reporting depth from day one.'
) c
JOIN products p ON p.slug = c.slug
ON DUPLICATE KEY UPDATE reason_text = VALUES(reason_text);

-- ---------------------------------------------------------------------
-- Configurable partner-type document requirements (no country assumed)
-- ---------------------------------------------------------------------
INSERT INTO partner_document_requirements (partner_type, doc_type, label, is_required, sort_order) VALUES
  ('individual',          'signatory_id',        'Government-issued ID', 1, 1),
  ('individual',          'address_proof',       'Address Proof', 1, 2),
  ('individual',          'bank_details',        'Bank Details', 1, 3),
  ('company',              'company_registration','Company Registration', 1, 1),
  ('company',              'tax_registration',    'Tax Registration', 1, 2),
  ('company',              'gst_vat',              'GST / VAT Registration (if applicable)', 0, 3),
  ('company',              'signatory_id',         'Authorized Signatory ID', 1, 4),
  ('company',              'bank_details',         'Bank Details', 1, 5),
  ('agency',               'business_license',    'Business License', 1, 1),
  ('agency',               'tax_registration',     'Tax Registration', 1, 2),
  ('agency',               'bank_details',         'Bank Details', 1, 3),
  ('technology_partner',   'company_registration', 'Company Registration', 1, 1),
  ('technology_partner',   'tax_registration',      'Tax Registration', 1, 2),
  ('technology_partner',   'bank_details',          'Bank Details', 1, 3),
  ('reseller',             'company_registration',  'Company Registration', 1, 1),
  ('reseller',              'tax_registration',      'Tax Registration', 1, 2),
  ('reseller',              'bank_details',           'Bank Details', 1, 3),
  ('consultant',            'signatory_id',           'Government-issued ID', 1, 1),
  ('consultant',            'bank_details',           'Bank Details', 1, 2),
  ('distributor',           'company_registration',   'Company Registration', 1, 1),
  ('distributor',           'tax_registration',        'Tax Registration', 1, 2),
  ('distributor',           'bank_details',            'Bank Details', 1, 3),
  ('enterprise_partner',    'company_registration',    'Company Registration', 1, 1),
  ('enterprise_partner',    'tax_registration',         'Tax Registration', 1, 2),
  ('enterprise_partner',    'bank_details',             'Bank Details', 1, 3),
  ('other',                 'signatory_id',             'Government-issued ID', 1, 1),
  ('other',                 'bank_details',             'Bank Details', 1, 2)
ON DUPLICATE KEY UPDATE label = VALUES(label);

-- ---------------------------------------------------------------------
-- Commission rules — admin-configurable starting point (not invented
-- production rates; adjust freely from /admin/commission-rules).
-- ---------------------------------------------------------------------
INSERT INTO commission_rules (name, rule_type, product_id, rate_percent, is_active)
SELECT 'Default product commission', 'product_based', p.id, 0.00, 1
FROM products p
WHERE p.commission_eligible = 1
  AND NOT EXISTS (SELECT 1 FROM commission_rules cr WHERE cr.product_id = p.id AND cr.rule_type = 'product_based');
