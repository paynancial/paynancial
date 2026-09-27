-- =====================================================================
-- ROLLBACK for 2026-09-28-phase1-admin-platform.sql
-- Removes ONLY what Phase 1 added. Take a backup first.
--
-- customers.user_id is restored to NOT NULL under STRICT mode: if any CRM
-- customer without a login exists, that statement FAILS (nothing is
-- converted or deleted). Export or link those customers first, then re-run.
-- =====================================================================
SET SESSION sql_mode = CONCAT('STRICT_ALL_TABLES,', @@SESSION.sql_mode);

-- First, so that a blocked rollback changes nothing at all.
ALTER TABLE customers MODIFY COLUMN user_id BIGINT UNSIGNED NOT NULL;

DROP VIEW IF EXISTS admin_documents_v;

ALTER TABLE customers
  DROP INDEX idx_customers_email,
  DROP COLUMN created_by,
  DROP COLUMN source,
  DROP COLUMN contact_mobile,
  DROP COLUMN contact_email,
  DROP COLUMN contact_name;

ALTER TABLE audit_logs
  DROP INDEX idx_audit_created,
  DROP COLUMN user_agent,
  DROP COLUMN actor_role;

DROP TABLE IF EXISTS user_preferences;

DELETE rp FROM role_permissions rp JOIN permissions p ON p.id = rp.permission_id WHERE p.slug IN (
  'dashboard.view','approvals.view','activity.view','enquiries.view','enquiries.create','enquiries.export','customers.create',
  'documents.view','documents.view_hr','products.view','products.manage','users.view','roles.view','roles.manage','audit.view',
  'security.view','security.manage','governance.view','system.health.view');
DELETE up FROM user_permissions up JOIN permissions p ON p.id = up.permission_id WHERE p.slug IN (
  'dashboard.view','approvals.view','activity.view','enquiries.view','enquiries.create','enquiries.export','customers.create',
  'documents.view','documents.view_hr','products.view','products.manage','users.view','roles.view','roles.manage','audit.view',
  'security.view','security.manage','governance.view','system.health.view');
DELETE FROM permissions WHERE slug IN (
  'dashboard.view','approvals.view','activity.view','enquiries.view','enquiries.create','enquiries.export','customers.create',
  'documents.view','documents.view_hr','products.view','products.manage','users.view','roles.view','roles.manage','audit.view',
  'security.view','security.manage','governance.view','system.health.view');
-- Grants added by Phase 1 for pre-existing permissions (cms.*, customers.*, …) to the new roles
-- disappear with the roles below (ON DELETE CASCADE). Pre-existing admin grants are untouched.

-- New roles: only removed when no user holds them (users.role_id has no cascade).
DELETE FROM roles WHERE slug IN ('operations_manager','sales_manager','consultant','incorporation_consultant','content_manager',
  'seo_manager','compliance_reviewer','finance_manager','developer','support')
  AND id NOT IN (SELECT role_id FROM (SELECT DISTINCT role_id FROM users) u);
UPDATE roles SET name = 'Admin' WHERE slug = 'admin';
