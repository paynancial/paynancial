-- =====================================================================
-- Enterprise admin platform — PHASE 1 (28 Sep 2026). ADDITIVE migration.
-- MySQL 8.0.16+ / MariaDB 10.4+. Run ONCE, after the 2026-09-25 CMS
-- migration, and only after a full database backup.
-- Rollback: 2026-09-28-phase1-admin-platform-rollback.sql
-- Not yet run on staging or production.
--
-- Adds, never removes or renames:
--   1. roles        — 10 staff roles (12-role model with admin + super_admin);
--                     'admin' is displayed as "Administrator".
--   2. permissions  — module-level catalogue (view / create / manage /
--                     export / approve …) + default role grants.
--   3. user_preferences — per-user UI state (sidebar, hero, quick actions).
--   4. audit_logs   — actor_role + user_agent columns.
--   5. customers    — a CRM customer no longer requires a login (D4):
--                     user_id becomes NULLable; contact columns added.
--                     Existing rows are unchanged.
--   6. admin_documents_v — read-only VIEW normalising the five existing
--                     document tables (D5). No data is moved or copied.
-- =====================================================================

-- ---------------------------------------------------------------------
-- 1. Roles (existing 'admin' and 'super_admin' are reused)
-- ---------------------------------------------------------------------
INSERT INTO roles (slug, name, description) VALUES
  ('operations_manager',      'Operations Manager',          'Runs enquiries, customers and operational queues'),
  ('sales_manager',           'Sales Manager',               'Leads, enquiries and customer pipeline'),
  ('consultant',              'Consultant',                  'Works assigned enquiries and customers'),
  ('incorporation_consultant','Incorporation Consultant',    'Business-services and incorporation operations'),
  ('content_manager',         'Content Manager',             'Writes content and performs editorial review'),
  ('seo_manager',             'SEO Manager',                 'SEO / AEO review and indexing decisions'),
  ('compliance_reviewer',     'Legal / Compliance Reviewer', 'Business / legal review and approval'),
  ('finance_manager',         'Finance Manager',             'Read-only finance and payment records'),
  ('developer',               'Developer',                   'System health and developer operations'),
  ('support',                 'Support',                     'Customer support and enquiries')
ON DUPLICATE KEY UPDATE name = VALUES(name);
UPDATE roles SET name = 'Administrator' WHERE slug = 'admin';

-- ---------------------------------------------------------------------
-- 2. Permission catalogue (existing slugs are reused, not duplicated)
-- ---------------------------------------------------------------------
INSERT INTO permissions (slug, name, module) VALUES
  ('dashboard.view',      'View the Command Center',                 'dashboard'),
  ('approvals.view',      'View the approvals inbox',                'approvals'),
  ('activity.view',       'View all staff activity',                 'activity'),
  ('enquiries.view',      'View enquiries',                          'enquiries'),
  ('enquiries.create',    'Create enquiries',                        'enquiries'),
  ('enquiries.export',    'Export enquiries',                        'enquiries'),
  ('customers.create',    'Create CRM customers',                    'customers'),
  ('documents.view',      'View customer / partner documents',       'documents'),
  ('documents.view_hr',   'View employee (HR) documents',            'documents'),
  ('products.view',       'View the solution catalogue',             'products'),
  ('products.manage',     'Manage the solution catalogue',           'products'),
  ('users.view',          'View platform users',                     'users'),
  ('roles.view',          'View roles and permissions',              'roles'),
  ('roles.manage',        'Change roles and permissions',            'roles'),
  ('audit.view',          'View audit logs',                         'audit'),
  ('security.view',       'View security controls',                  'security'),
  ('security.manage',     'Decide security change requests',         'security'),
  ('governance.view',     'View content governance',                 'governance'),
  ('system.health.view',  'View system health',                      'system')
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Default grants. super_admin passes every check in code; it is granted
-- everything here as well so the matrix reads correctly.
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT (SELECT id FROM roles WHERE slug = 'super_admin'), p.id FROM permissions p;

-- Administrator: today's effective access, minus role management,
-- system settings and HR documents (super admin only by default).
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT (SELECT id FROM roles WHERE slug = 'admin'), p.id FROM permissions p WHERE p.slug IN (
  'dashboard.view','approvals.view','activity.view','enquiries.view','enquiries.create','enquiries.manage','enquiries.export',
  'customers.view','customers.create','customers.manage','partners.view','partners.manage','products.view','products.manage',
  'transactions.view','transactions.export','documents.view','users.view','users.manage','roles.view','audit.view',
  'security.view','security.manage','governance.view','system.health.view','support.manage');

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON (
     (r.slug = 'operations_manager' AND p.slug IN ('dashboard.view','activity.view','enquiries.view','enquiries.create','enquiries.manage','enquiries.export',
                                                   'customers.view','customers.create','customers.manage','partners.view','transactions.view','documents.view'))
  OR (r.slug = 'sales_manager'      AND p.slug IN ('dashboard.view','activity.view','enquiries.view','enquiries.create','enquiries.manage','enquiries.export',
                                                   'customers.view','customers.create','customers.manage','partners.view','products.view'))
  OR (r.slug = 'consultant'         AND p.slug IN ('dashboard.view','enquiries.view','enquiries.create','customers.view','customers.create','products.view'))
  OR (r.slug = 'incorporation_consultant' AND p.slug IN ('dashboard.view','enquiries.view','customers.view','documents.view'))
  OR (r.slug = 'content_manager'    AND p.slug IN ('dashboard.view','approvals.view','cms.view','cms.create','cms.edit','cms.submit','cms.review.editorial','governance.view'))
  OR (r.slug = 'seo_manager'        AND p.slug IN ('dashboard.view','approvals.view','cms.view','cms.review.seo','cms.seo.manage','governance.view'))
  OR (r.slug = 'compliance_reviewer' AND p.slug IN ('dashboard.view','approvals.view','activity.view','cms.view','cms.approve','governance.view','audit.view',
                                                   'documents.view','customers.view'))
  OR (r.slug = 'finance_manager'    AND p.slug IN ('dashboard.view','transactions.view','transactions.export','customers.view','partners.view','products.view'))
  OR (r.slug = 'developer'          AND p.slug IN ('dashboard.view','system.health.view','products.view'))
  OR (r.slug = 'support'            AND p.slug IN ('dashboard.view','enquiries.view','enquiries.create','enquiries.manage','customers.view','support.manage'))
);

-- ---------------------------------------------------------------------
-- 3. Per-user UI preferences
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS user_preferences (
  user_id     BIGINT UNSIGNED NOT NULL,
  pref_key    VARCHAR(64) NOT NULL,
  value_json  JSON NOT NULL,
  updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, pref_key),
  CONSTRAINT fk_prefs_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- 4. Audit context
-- ---------------------------------------------------------------------
ALTER TABLE audit_logs
  ADD COLUMN actor_role VARCHAR(50)  NULL AFTER user_id,
  ADD COLUMN user_agent VARCHAR(255) NULL AFTER ip_address,
  ADD INDEX idx_audit_created (created_at);

-- ---------------------------------------------------------------------
-- 5. CRM customer without a login (D4). The portal login stays optional
--    and separate: customers.user_id → users.id only when one exists.
-- ---------------------------------------------------------------------
ALTER TABLE customers
  MODIFY COLUMN user_id BIGINT UNSIGNED NULL,
  ADD COLUMN contact_name   VARCHAR(150) NULL AFTER company_name,
  ADD COLUMN contact_email  VARCHAR(190) NULL AFTER contact_name,
  ADD COLUMN contact_mobile VARCHAR(20)  NULL AFTER contact_email,
  ADD COLUMN source         VARCHAR(40)  NULL AFTER contact_mobile,
  ADD COLUMN created_by     BIGINT UNSIGNED NULL AFTER source,
  ADD INDEX idx_customers_email (contact_email);

-- ---------------------------------------------------------------------
-- 6. Unified, read-only document view over the five existing tables (D5).
--    File paths are exposed to the admin query layer only, never listed.
-- ---------------------------------------------------------------------
CREATE OR REPLACE VIEW admin_documents_v AS
  SELECT CONCAT('ckyc-', d.id) AS doc_key, 'customer_kyc' AS source_table, d.id AS source_id,
         'customer' AS owner_type, d.customer_id AS owner_id,
         COALESCE(c.company_name, c.customer_code) AS owner_name,
         d.doc_type, d.status AS raw_status,
         CASE d.status WHEN 'verified' THEN 'verified' WHEN 'approved' THEN 'verified' WHEN 'rejected' THEN 'rejected' WHEN 'under_review' THEN 'under_review' WHEN 'pending' THEN 'under_review' WHEN 'info_required' THEN 'pending' ELSE 'uploaded' END AS status,
         d.uploaded_at, d.reviewed_at
  FROM customer_kyc_documents d LEFT JOIN customers c ON c.id = d.customer_id
  UNION ALL
  SELECT CONCAT('capp-', d.id), 'customer_application', d.id, 'customer_application', d.customer_application_id,
         a.business_name, d.doc_type, d.status,
         CASE d.status WHEN 'verified' THEN 'verified' WHEN 'approved' THEN 'verified' WHEN 'rejected' THEN 'rejected' WHEN 'under_review' THEN 'under_review' WHEN 'pending' THEN 'under_review' WHEN 'info_required' THEN 'pending' ELSE 'uploaded' END,
         d.uploaded_at, NULL
  FROM customer_application_documents d LEFT JOIN customer_applications a ON a.id = d.customer_application_id
  UNION ALL
  SELECT CONCAT('papp-', d.id), 'partner_application', d.id, 'partner_application', d.application_id,
         a.business_name, d.doc_type, d.status,
         CASE d.status WHEN 'verified' THEN 'verified' WHEN 'approved' THEN 'verified' WHEN 'rejected' THEN 'rejected' WHEN 'under_review' THEN 'under_review' WHEN 'pending' THEN 'under_review' WHEN 'info_required' THEN 'pending' ELSE 'uploaded' END,
         d.uploaded_at, NULL
  FROM partner_application_documents d LEFT JOIN partner_applications a ON a.id = d.application_id
  UNION ALL
  SELECT CONCAT('pdoc-', d.id), 'partner', d.id, 'partner', d.partner_id,
         p.business_name, d.doc_type, d.status,
         CASE d.status WHEN 'verified' THEN 'verified' WHEN 'approved' THEN 'verified' WHEN 'rejected' THEN 'rejected' WHEN 'under_review' THEN 'under_review' WHEN 'pending' THEN 'under_review' WHEN 'info_required' THEN 'pending' ELSE 'uploaded' END,
         d.uploaded_at, NULL
  FROM partner_documents d LEFT JOIN partners p ON p.id = d.partner_id
  UNION ALL
  SELECT CONCAT('emp-', d.id), 'employee', d.id, 'employee', d.employee_id,
         e.employee_code, d.doc_type, 'uploaded', 'uploaded', d.uploaded_at, NULL
  FROM employee_documents d LEFT JOIN employees e ON e.id = d.employee_id;
