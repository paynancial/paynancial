-- =====================================================================
-- Paynancial — baseline seed data
-- Run after schema.sql. Safe to re-run (uses INSERT IGNORE / ON DUPLICATE).
-- =====================================================================

INSERT INTO roles (slug, name, description) VALUES
  ('customer',    'Customer',     'Businesses/individuals collecting or making payments'),
  ('partner',     'Partner',      'Reseller / referral / technology partners'),
  ('employee',    'Employee',     'Internal staff'),
  ('hr',          'HR / HRMS',    'Human resources team with HRMS access'),
  ('admin',       'Admin',        'Restricted, configurable administrative access'),
  ('super_admin', 'Super Admin',  'Full platform access')
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Baseline permissions (extend freely; role_permissions wires them up).
INSERT INTO permissions (slug, name, module) VALUES
  ('customers.view',      'View customers',      'customers'),
  ('customers.manage',    'Manage customers',    'customers'),
  ('partners.view',       'View partners',       'partners'),
  ('partners.manage',     'Manage partners',     'partners'),
  ('transactions.view',   'View transactions',   'transactions'),
  ('transactions.export', 'Export transactions', 'transactions'),
  ('enquiries.manage',    'Manage enquiries',    'enquiries'),
  ('support.manage',      'Manage support tickets','support'),
  ('cms.manage',          'Manage CMS content',  'cms'),
  ('hrms.manage',         'Manage HRMS module',  'hrms'),
  ('users.manage',        'Manage platform users','users'),
  ('settings.manage',     'Manage system settings','settings')
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Super admin gets every permission.
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT (SELECT id FROM roles WHERE slug = 'super_admin'), p.id FROM permissions p;

-- Admin gets everything except settings.manage (kept restricted by default).
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT (SELECT id FROM roles WHERE slug = 'admin'), p.id
FROM permissions p WHERE p.slug <> 'settings.manage';

-- HR gets hrms + support.
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT (SELECT id FROM roles WHERE slug = 'hr'), p.id
FROM permissions p WHERE p.slug IN ('hrms.manage','support.manage');

-- Employee gets read/support access.
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT (SELECT id FROM roles WHERE slug = 'employee'), p.id
FROM permissions p WHERE p.slug IN ('customers.view','transactions.view','support.manage','enquiries.manage');

-- ---------------------------------------------------------------------
-- Default Super Admin account.
-- Username: superadmin@paynancial.com   Password: ChangeMe@2026
-- CHANGE THIS PASSWORD IMMEDIATELY AFTER FIRST LOGIN.
-- Hash below = password_hash('ChangeMe@2026', PASSWORD_DEFAULT) example (bcrypt).
-- Regenerate with: php -r "echo password_hash('YourNewPassword', PASSWORD_DEFAULT);"
-- ---------------------------------------------------------------------
INSERT INTO users (uuid, role_id, full_name, email, password_hash, status, email_verified_at)
SELECT UUID(), (SELECT id FROM roles WHERE slug = 'super_admin'),
       'Paynancial Super Admin', 'superadmin@paynancial.com',
       '$2y$12$6rXmbOAyZwIYeoTX55RZ1Ot9X3eKzOEz3NLMaFpaOMZDlgXEwgx66', -- ChangeMe@2026
       'active', NOW()
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'superadmin@paynancial.com');

-- ---------------------------------------------------------------------
-- CMS baseline (editable from /admin/cms without touching code).
-- ---------------------------------------------------------------------
INSERT INTO cms_pages (page_key, title, content_json, meta_title, meta_description)
VALUES (
  'home', 'Home',
  JSON_OBJECT(
    'hero_title', 'Powering Smarter Payments. Built for the Future.',
    'hero_subtitle', 'Secure, intelligent and seamless payment technology designed to help businesses collect, manage and grow.'
  ),
  'Paynancial | Smarter Payment Infrastructure for Modern Businesses',
  'Paynancial helps businesses accept, manage and analyze payments through secure, intelligent and scalable technology.'
)
ON DUPLICATE KEY UPDATE title = VALUES(title);

-- ---------------------------------------------------------------------
-- Baseline settings (pricing stays "Talk to Sales" until configured here).
-- ---------------------------------------------------------------------
INSERT INTO settings (setting_key, setting_value) VALUES
  ('company_legal_name', 'Paynancial Technology Pvt. Ltd.'),
  ('company_cin', 'U66190BR2024PTC067929'),
  ('support_email', 'hello@paynancial.com'),
  ('support_mobile', '+91 7066 820 820'),
  ('support_phone', '+91 612 2999 382'),
  ('pricing_mode', 'talk_to_sales')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);
