-- =====================================================================
-- Paynancial Technology Pvt. Ltd. — Platform database schema
-- Engine: MySQL 8+ / MariaDB 10.4+   Charset: utf8mb4
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- Access control
-- ---------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS roles (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug          VARCHAR(50) NOT NULL UNIQUE,   -- customer, partner, employee, hr, admin, super_admin
  name          VARCHAR(100) NOT NULL,
  description   VARCHAR(255) NULL,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS permissions (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug          VARCHAR(100) NOT NULL UNIQUE,  -- e.g. customers.view, transactions.export
  name          VARCHAR(150) NOT NULL,
  module        VARCHAR(80) NOT NULL,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS role_permissions (
  role_id        INT UNSIGNED NOT NULL,
  permission_id  INT UNSIGNED NOT NULL,
  PRIMARY KEY (role_id, permission_id),
  CONSTRAINT fk_rp_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
  CONSTRAINT fk_rp_perm FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Per-employee overrides on top of role permissions (grant or revoke).
CREATE TABLE IF NOT EXISTS user_permissions (
  user_id        BIGINT UNSIGNED NOT NULL,
  permission_id  INT UNSIGNED NOT NULL,
  effect         ENUM('grant','revoke') NOT NULL DEFAULT 'grant',
  PRIMARY KEY (user_id, permission_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Core identity — one users table backs every login role.
-- Role-specific profile tables (customers, partners, employees, hr_users)
-- hold the domain data and 1:1 reference users.id.
-- ---------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS users (
  id                    BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  uuid                  CHAR(36) NOT NULL UNIQUE,
  role_id               INT UNSIGNED NOT NULL,
  full_name             VARCHAR(150) NOT NULL,
  email                 VARCHAR(190) NOT NULL UNIQUE,
  mobile                VARCHAR(20) NULL,
  password_hash         VARCHAR(255) NOT NULL,
  status                ENUM('active','inactive','suspended','pending') NOT NULL DEFAULT 'pending',
  email_verified_at     DATETIME NULL,
  mobile_verified_at    DATETIME NULL,
  two_factor_enabled    TINYINT(1) NOT NULL DEFAULT 0,
  last_login_at         DATETIME NULL,
  last_login_ip         VARCHAR(45) NULL,
  failed_login_count    INT UNSIGNED NOT NULL DEFAULT 0,
  locked_until          DATETIME NULL,
  created_at            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id),
  INDEX idx_users_role (role_id),
  INDEX idx_users_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS customers (
  id                BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id           BIGINT UNSIGNED NOT NULL UNIQUE,
  customer_code     VARCHAR(30) NOT NULL UNIQUE,
  company_name      VARCHAR(150) NULL,
  kyc_status        ENUM('not_started','pending','verified','rejected') NOT NULL DEFAULT 'not_started',
  billing_address   TEXT NULL,
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_customers_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS partners (
  id                BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id           BIGINT UNSIGNED NOT NULL UNIQUE,
  partner_code      VARCHAR(30) NOT NULL UNIQUE,
  business_name     VARCHAR(150) NOT NULL,
  partner_type      VARCHAR(60) NULL,           -- reseller, referral, technology
  kyc_status        ENUM('not_started','pending','verified','rejected') NOT NULL DEFAULT 'not_started',
  commission_rate   DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  onboarded_at      DATETIME NULL,
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_partners_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS partner_documents (
  id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  partner_id    BIGINT UNSIGNED NOT NULL,
  doc_type      VARCHAR(80) NOT NULL,
  file_path     VARCHAR(255) NOT NULL,
  status        ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  uploaded_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_pdocs_partner FOREIGN KEY (partner_id) REFERENCES partners(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS employees (
  id                BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id           BIGINT UNSIGNED NOT NULL UNIQUE,
  employee_code     VARCHAR(30) NOT NULL UNIQUE,
  department        VARCHAR(100) NULL,
  designation       VARCHAR(100) NULL,
  reporting_to      BIGINT UNSIGNED NULL,
  joining_date      DATE NULL,
  employment_status ENUM('active','on_leave','resigned','terminated') NOT NULL DEFAULT 'active',
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_employees_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_employees_manager FOREIGN KEY (reporting_to) REFERENCES employees(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- HRMS-specific identity (an employee may also hold HR portal access).
CREATE TABLE IF NOT EXISTS hr_users (
  id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id       BIGINT UNSIGNED NOT NULL UNIQUE,
  employee_id   BIGINT UNSIGNED NULL,
  hr_role       VARCHAR(60) NOT NULL DEFAULT 'hr_executive',
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_hr_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_hr_employee FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Session / auth security
-- ---------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS sessions (
  id            VARCHAR(128) PRIMARY KEY,
  user_id       BIGINT UNSIGNED NOT NULL,
  ip_address    VARCHAR(45) NULL,
  user_agent    VARCHAR(255) NULL,
  last_activity DATETIME NOT NULL,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_sessions_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_sessions_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS login_attempts (
  id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  identifier    VARCHAR(190) NOT NULL,       -- email/mobile/employee id attempted
  role_slug     VARCHAR(50) NULL,
  ip_address    VARCHAR(45) NOT NULL,
  successful    TINYINT(1) NOT NULL DEFAULT 0,
  user_agent    VARCHAR(255) NULL,
  attempted_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_attempts_identifier (identifier),
  INDEX idx_attempts_ip (ip_address, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS password_resets (
  id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id       BIGINT UNSIGNED NOT NULL,
  token_hash    VARCHAR(255) NOT NULL,
  expires_at    DATETIME NOT NULL,
  used_at       DATETIME NULL,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_pwreset_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_pwreset_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS otp_verifications (
  id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id       BIGINT UNSIGNED NULL,
  channel       ENUM('email','mobile') NOT NULL,
  destination   VARCHAR(190) NOT NULL,
  otp_hash      VARCHAR(255) NOT NULL,
  purpose       VARCHAR(60) NOT NULL DEFAULT 'login',   -- login, verify_email, verify_mobile, reset_password
  expires_at    DATETIME NOT NULL,
  consumed_at   DATETIME NULL,
  attempts      TINYINT UNSIGNED NOT NULL DEFAULT 0,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_otp_dest (destination, purpose)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS audit_logs (
  id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id       BIGINT UNSIGNED NULL,
  action        VARCHAR(100) NOT NULL,
  entity_type   VARCHAR(80) NULL,
  entity_id     BIGINT UNSIGNED NULL,
  ip_address    VARCHAR(45) NULL,
  meta_json     JSON NULL,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_audit_user (user_id),
  INDEX idx_audit_action (action)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Payments domain
-- ---------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS transactions (
  id                 BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  transaction_ref    VARCHAR(40) NOT NULL UNIQUE,
  customer_id        BIGINT UNSIGNED NULL,
  partner_id         BIGINT UNSIGNED NULL,
  amount             DECIMAL(14,2) NOT NULL,
  currency           CHAR(3) NOT NULL DEFAULT 'INR',
  payment_method     VARCHAR(40) NULL,           -- card, upi, netbanking, wallet
  status             ENUM('initiated','pending','success','failed','refunded') NOT NULL DEFAULT 'initiated',
  gateway_reference  VARCHAR(100) NULL,
  description        VARCHAR(255) NULL,
  created_at         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_txn_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
  CONSTRAINT fk_txn_partner FOREIGN KEY (partner_id) REFERENCES partners(id) ON DELETE SET NULL,
  INDEX idx_txn_status (status),
  INDEX idx_txn_customer (customer_id),
  INDEX idx_txn_partner (partner_id),
  INDEX idx_txn_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS payments (
  id                BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  transaction_id    BIGINT UNSIGNED NOT NULL,
  payer_name        VARCHAR(150) NULL,
  payer_contact     VARCHAR(150) NULL,
  captured_at       DATETIME NULL,
  fee_amount        DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  net_amount        DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_payments_txn FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS refunds (
  id                BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  refund_ref        VARCHAR(40) NOT NULL UNIQUE,
  transaction_id    BIGINT UNSIGNED NOT NULL,
  amount            DECIMAL(14,2) NOT NULL,
  reason            VARCHAR(255) NULL,
  status            ENUM('requested','processing','completed','rejected') NOT NULL DEFAULT 'requested',
  requested_by      BIGINT UNSIGNED NULL,
  processed_at      DATETIME NULL,
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_refunds_txn FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS settlements (
  id                BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  settlement_ref    VARCHAR(40) NOT NULL UNIQUE,
  partner_id        BIGINT UNSIGNED NULL,
  customer_id       BIGINT UNSIGNED NULL,
  period_start      DATE NOT NULL,
  period_end        DATE NOT NULL,
  gross_amount      DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  fee_amount        DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  net_amount        DECIMAL(14,2) NOT NULL DEFAULT 0.00,
  status            ENUM('pending','processing','settled','on_hold') NOT NULL DEFAULT 'pending',
  settled_at        DATETIME NULL,
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_settlement_partner FOREIGN KEY (partner_id) REFERENCES partners(id) ON DELETE SET NULL,
  CONSTRAINT fk_settlement_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS commissions (
  id                BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  partner_id        BIGINT UNSIGNED NOT NULL,
  transaction_id    BIGINT UNSIGNED NULL,
  amount            DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  rate_applied      DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  status            ENUM('accrued','paid','reversed') NOT NULL DEFAULT 'accrued',
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_commission_partner FOREIGN KEY (partner_id) REFERENCES partners(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS payment_links (
  id                BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  link_ref          VARCHAR(40) NOT NULL UNIQUE,
  created_by        BIGINT UNSIGNED NOT NULL,     -- users.id (customer or partner)
  title             VARCHAR(150) NOT NULL,
  amount            DECIMAL(14,2) NULL,           -- null = customer enters amount
  currency          CHAR(3) NOT NULL DEFAULT 'INR',
  status            ENUM('active','paid','expired','disabled') NOT NULL DEFAULT 'active',
  expires_at        DATETIME NULL,
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS api_keys (
  id                BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  owner_user_id     BIGINT UNSIGNED NOT NULL,
  key_prefix        VARCHAR(16) NOT NULL,
  key_hash          VARCHAR(255) NOT NULL,
  environment       ENUM('sandbox','live') NOT NULL DEFAULT 'sandbox',
  label             VARCHAR(100) NULL,
  last_used_at      DATETIME NULL,
  revoked_at        DATETIME NULL,
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_apikeys_user FOREIGN KEY (owner_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS webhooks (
  id                BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  owner_user_id     BIGINT UNSIGNED NOT NULL,
  target_url        VARCHAR(255) NOT NULL,
  event_types       VARCHAR(255) NOT NULL,        -- comma-separated
  secret_hash       VARCHAR(255) NOT NULL,
  is_active         TINYINT(1) NOT NULL DEFAULT 1,
  last_triggered_at DATETIME NULL,
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_webhooks_user FOREIGN KEY (owner_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Sales / support
-- ---------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS enquiries (
  id                BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  enquiry_code      VARCHAR(30) NOT NULL UNIQUE,   -- PAY-ENQ-2026-000001
  type              ENUM('sales','partner','support','general','career') NOT NULL,
  name              VARCHAR(150) NOT NULL,
  company           VARCHAR(150) NULL,
  email             VARCHAR(190) NOT NULL,
  mobile             VARCHAR(20) NULL,
  subject           VARCHAR(190) NULL,
  message           TEXT NOT NULL,
  status            ENUM('new','in_progress','responded','closed') NOT NULL DEFAULT 'new',
  assigned_to       BIGINT UNSIGNED NULL,
  ip_address        VARCHAR(45) NULL,
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_enquiries_type (type),
  INDEX idx_enquiries_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS support_tickets (
  id                BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ticket_code       VARCHAR(30) NOT NULL UNIQUE,
  user_id           BIGINT UNSIGNED NULL,
  subject           VARCHAR(190) NOT NULL,
  description       TEXT NOT NULL,
  priority          ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
  status            ENUM('open','in_progress','resolved','closed') NOT NULL DEFAULT 'open',
  assigned_to       BIGINT UNSIGNED NULL,
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_tickets_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS notifications (
  id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id       BIGINT UNSIGNED NOT NULL,
  title         VARCHAR(190) NOT NULL,
  body          VARCHAR(500) NULL,
  is_read       TINYINT(1) NOT NULL DEFAULT 0,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_notifications_user (user_id, is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- HRMS
-- ---------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS job_posts (
  id                BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title             VARCHAR(150) NOT NULL,
  department        VARCHAR(100) NULL,
  location          VARCHAR(120) NULL,
  employment_type   VARCHAR(60) NULL,     -- full_time, part_time, contract, internship
  description       TEXT NOT NULL,
  status            ENUM('open','closed','draft') NOT NULL DEFAULT 'draft',
  posted_by         BIGINT UNSIGNED NULL,
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS job_applications (
  id                BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  application_code  VARCHAR(30) NOT NULL UNIQUE,
  job_post_id       BIGINT UNSIGNED NOT NULL,
  full_name         VARCHAR(150) NOT NULL,
  email             VARCHAR(190) NOT NULL,
  mobile            VARCHAR(20) NULL,
  resume_path       VARCHAR(255) NOT NULL,
  cover_note        TEXT NULL,
  status            ENUM('applied','shortlisted','interview','offered','rejected','hired') NOT NULL DEFAULT 'applied',
  interview_at      DATETIME NULL,
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_apps_job FOREIGN KEY (job_post_id) REFERENCES job_posts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS employee_documents (
  id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  employee_id   BIGINT UNSIGNED NOT NULL,
  doc_type      VARCHAR(80) NOT NULL,
  file_path     VARCHAR(255) NOT NULL,
  uploaded_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_edocs_employee FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS attendance (
  id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  employee_id   BIGINT UNSIGNED NOT NULL,
  work_date     DATE NOT NULL,
  check_in      TIME NULL,
  check_out     TIME NULL,
  status        ENUM('present','absent','half_day','holiday','week_off') NOT NULL DEFAULT 'present',
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_attendance_employee FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
  UNIQUE KEY uq_attendance_employee_date (employee_id, work_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS leave_requests (
  id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  employee_id   BIGINT UNSIGNED NOT NULL,
  leave_type    VARCHAR(60) NOT NULL,       -- casual, sick, earned, unpaid
  start_date    DATE NOT NULL,
  end_date      DATE NOT NULL,
  reason        VARCHAR(255) NULL,
  status        ENUM('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
  approved_by   BIGINT UNSIGNED NULL,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_leave_employee FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Content / CMS
-- ---------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS cms_pages (
  id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  page_key      VARCHAR(80) NOT NULL UNIQUE,   -- home, about, products, solutions, pricing, faqs, contact, footer
  title         VARCHAR(190) NULL,
  content_json  JSON NULL,                     -- structured section content edited by admin
  meta_title       VARCHAR(190) NULL,
  meta_description VARCHAR(300) NULL,
  updated_by    BIGINT UNSIGNED NULL,
  updated_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS blog_posts (
  id                BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug              VARCHAR(190) NOT NULL UNIQUE,
  title             VARCHAR(190) NOT NULL,
  excerpt           VARCHAR(300) NULL,
  body_html         MEDIUMTEXT NULL,
  cover_image       VARCHAR(255) NULL,
  status            ENUM('draft','published') NOT NULL DEFAULT 'draft',
  author_id         BIGINT UNSIGNED NULL,
  published_at      DATETIME NULL,
  meta_title        VARCHAR(190) NULL,
  meta_description  VARCHAR(300) NULL,
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS contact_submissions (
  id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  enquiry_id    BIGINT UNSIGNED NULL,
  form_type     VARCHAR(40) NOT NULL,
  payload_json  JSON NOT NULL,
  ip_address    VARCHAR(45) NULL,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_contact_enquiry FOREIGN KEY (enquiry_id) REFERENCES enquiries(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS settings (
  setting_key    VARCHAR(100) PRIMARY KEY,
  setting_value  TEXT NULL,
  updated_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
