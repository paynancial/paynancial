-- =====================================================================
-- Paynancial Partner Hub — schema extension
-- Run AFTER schema.sql and seed.sql (fresh installs only — this file
-- ALTERs the base `partners` and `payment_links` tables in place).
--
-- Design note: several capabilities requested for the Partner Hub map
-- directly onto tables the base platform already has, so this file does
-- not duplicate them. Reused as-is:
--   commissions        -> partner commission ledger (already partner_id +
--                          transaction_id scoped)
--   notifications       -> partner notification center (already user_id
--                          scoped)
--   audit_logs          -> partner_activity_logs
--   login_attempts       -> partner_login_logs
--   sessions             -> partner_sessions
--   support_tickets      -> partner support tickets (support_messages below
--                          adds threaded replies)
--   payment_links        -> partner payment link creation (extended below
--                          with partner_id / customer_application_id)
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- Partner lifecycle: application -> account
-- ---------------------------------------------------------------------

-- The onboarding wizard writes here BEFORE any login account exists.
-- On approval, an admin action creates the corresponding `users` +
-- `partners` rows and links them back via partners.application_id.
CREATE TABLE IF NOT EXISTS partner_applications (
  id                        BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  application_code          VARCHAR(40) NOT NULL UNIQUE,   -- PYN-PARTNER-2026-000001
  partner_type              VARCHAR(60) NOT NULL,          -- individual, company, agency, technology_partner, reseller, consultant, distributor, enterprise_partner, other
  business_name             VARCHAR(150) NOT NULL,
  contact_person            VARCHAR(150) NOT NULL,
  email                     VARCHAR(190) NOT NULL,
  mobile                    VARCHAR(20) NOT NULL,
  country                   VARCHAR(80) NULL,
  state                     VARCHAR(80) NULL,
  city                      VARCHAR(80) NULL,
  website                   VARCHAR(190) NULL,
  business_address          TEXT NULL,

  business_type             VARCHAR(100) NULL,
  industry                  VARCHAR(100) NULL,
  years_in_business         VARCHAR(30) NULL,
  employee_count            VARCHAR(30) NULL,
  existing_customer_base    VARCHAR(60) NULL,
  expected_monthly_volume   VARCHAR(60) NULL,
  current_payment_provider  VARCHAR(150) NULL,
  primary_markets           VARCHAR(190) NULL,
  countries_served          VARCHAR(190) NULL,

  engagement_model          VARCHAR(60) NULL,              -- referral, enrollment, reseller, technology_integration, api_partner, enterprise, strategic

  agreements_accepted       TINYINT(1) NOT NULL DEFAULT 0,
  agreements_accepted_at    DATETIME NULL,
  agreements_ip             VARCHAR(45) NULL,

  status                    ENUM('submitted','under_review','info_required','approved','rejected') NOT NULL DEFAULT 'submitted',
  status_note               VARCHAR(500) NULL,             -- e.g. what info is required
  assigned_manager_id       BIGINT UNSIGNED NULL,           -- users.id (Paynancial staff)
  created_partner_id        BIGINT UNSIGNED NULL,           -- set once approved -> partners.id

  submitted_at              DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  reviewed_at                DATETIME NULL,
  created_at                DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at                DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  CONSTRAINT fk_partner_app_manager FOREIGN KEY (assigned_manager_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_partner_app_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS partner_application_documents (
  id                BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  application_id    BIGINT UNSIGNED NOT NULL,
  doc_type          VARCHAR(80) NOT NULL,          -- company_registration, tax_registration, business_license, pan_tax_id, gst_vat, bank_details, signatory_id, address_proof, other
  file_path         VARCHAR(255) NOT NULL,
  status            ENUM('uploaded','under_review','approved','rejected') NOT NULL DEFAULT 'uploaded',
  uploaded_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_partner_app_doc FOREIGN KEY (application_id) REFERENCES partner_applications(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Configurable, per-partner-type document requirements (admin-managed —
-- no country/regulation assumptions hardcoded in the application).
CREATE TABLE IF NOT EXISTS partner_document_requirements (
  id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  partner_type  VARCHAR(60) NOT NULL,
  doc_type      VARCHAR(80) NOT NULL,
  label         VARCHAR(150) NOT NULL,
  is_required   TINYINT(1) NOT NULL DEFAULT 1,
  sort_order    SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  UNIQUE KEY uq_doc_req (partner_type, doc_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS partner_agreements (
  id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  partner_id      BIGINT UNSIGNED NULL,
  application_id  BIGINT UNSIGNED NULL,
  agreement_type  VARCHAR(60) NOT NULL,   -- partner_agreement, terms, privacy, commission_agreement, compliance_declaration
  version         VARCHAR(20) NOT NULL DEFAULT '1.0',
  accepted_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ip_address      VARCHAR(45) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Settlement bank details. account_number/routing are stored encrypted
-- (see includes/security.php encrypt_sensitive()/decrypt_sensitive());
-- only a masked value is ever rendered in the UI.
CREATE TABLE IF NOT EXISTS partner_bank_accounts (
  id                    BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  partner_id            BIGINT UNSIGNED NULL,
  application_id        BIGINT UNSIGNED NULL,
  bank_name             VARCHAR(150) NOT NULL,
  account_holder        VARCHAR(150) NOT NULL,
  account_number_enc    VARBINARY(512) NOT NULL,
  account_number_last4  VARCHAR(4) NOT NULL,
  routing_code          VARCHAR(60) NULL,       -- IFSC / routing / SWIFT — label is contextual, not assumed
  currency              CHAR(3) NOT NULL DEFAULT 'INR',
  settlement_preference VARCHAR(40) NOT NULL DEFAULT 'standard',
  created_at            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Extend the base `partners` table with Partner Hub fields.
-- NOTE: this file is meant to run ONCE, immediately after schema.sql on a
-- fresh install (same as seed.sql) — these ALTERs are not written to be
-- idempotent. Re-running this file on a database that already has these
-- columns will error; that's expected, not a bug.
ALTER TABLE partners
  ADD COLUMN application_id    BIGINT UNSIGNED NULL AFTER user_id,
  ADD COLUMN status            ENUM('active','suspended') NOT NULL DEFAULT 'active' AFTER kyc_status,
  ADD COLUMN manager_user_id   BIGINT UNSIGNED NULL AFTER status,
  ADD COLUMN website           VARCHAR(190) NULL AFTER business_name,
  ADD COLUMN country           VARCHAR(80) NULL AFTER website;

-- ---------------------------------------------------------------------
-- Partner team (sub-users under one partner organization)
-- ---------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS partner_roles (
  id      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug    VARCHAR(40) NOT NULL UNIQUE,   -- owner, admin, sales_manager, sales_executive, finance, support, viewer
  name    VARCHAR(80) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Module-level grants per partner role (customers, applications,
-- transactions, settlements, commission, reports, support, team, documents).
CREATE TABLE IF NOT EXISTS partner_role_permissions (
  role_id   INT UNSIGNED NOT NULL,
  module    VARCHAR(40) NOT NULL,
  can_view  TINYINT(1) NOT NULL DEFAULT 1,
  can_edit  TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (role_id, module),
  CONSTRAINT fk_partner_role_perm FOREIGN KEY (role_id) REFERENCES partner_roles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS partner_users (
  id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  partner_id    BIGINT UNSIGNED NOT NULL,
  user_id       BIGINT UNSIGNED NOT NULL UNIQUE,
  role_id       INT UNSIGNED NOT NULL,
  status        ENUM('invited','active','disabled') NOT NULL DEFAULT 'active',
  invited_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_partner_users_partner FOREIGN KEY (partner_id) REFERENCES partners(id) ON DELETE CASCADE,
  CONSTRAINT fk_partner_users_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_partner_users_role FOREIGN KEY (role_id) REFERENCES partner_roles(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Solution catalog + recommendation engine
-- ---------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS products (
  id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug                VARCHAR(80) NOT NULL UNIQUE,
  name                VARCHAR(150) NOT NULL,
  category            VARCHAR(60) NOT NULL,   -- payment_acceptance, payment_management, payouts, integration, business_solutions, ai_intelligence
  short_description   VARCHAR(300) NULL,
  complexity          ENUM('low','medium','high') NOT NULL DEFAULT 'medium',
  pricing_status      VARCHAR(60) NOT NULL DEFAULT 'talk_to_sales',
  commission_eligible TINYINT(1) NOT NULL DEFAULT 1,
  is_active           TINYINT(1) NOT NULL DEFAULT 1,
  sort_order          SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS product_features (
  id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id    BIGINT UNSIGNED NOT NULL,
  feature       VARCHAR(200) NOT NULL,
  sort_order    SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  CONSTRAINT fk_product_feature FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Simple, admin-editable condition -> product rules. A customer
-- application's stored attributes (customer_type, requirement flags,
-- volume band, has_website, is_international, ...) are matched against
-- condition_key/condition_value pairs; every matching, active rule's
-- product is surfaced as a recommendation with reason_text shown verbatim.
CREATE TABLE IF NOT EXISTS recommendation_rules (
  id               BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  condition_key    VARCHAR(60) NOT NULL,   -- customer_type, requirement, is_international, no_website, is_enterprise
  condition_value  VARCHAR(100) NOT NULL,
  product_id       BIGINT UNSIGNED NOT NULL,
  reason_text      VARCHAR(255) NOT NULL,
  is_active        TINYINT(1) NOT NULL DEFAULT 1,
  CONSTRAINT fk_reco_rule_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  UNIQUE KEY uq_reco_rule (condition_key, condition_value, product_id),
  INDEX idx_reco_condition (condition_key, condition_value)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Customer enrollment CRM (lead -> active)
-- ---------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS customer_applications (
  id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  application_code        VARCHAR(40) NOT NULL UNIQUE,   -- PYN-CUST-2026-000001
  partner_id              BIGINT UNSIGNED NOT NULL,
  customer_type           VARCHAR(60) NOT NULL,          -- individual, small_business, sme, enterprise, ecommerce, travel, education, healthcare, retail, hospitality, professional_services, other
  business_name           VARCHAR(150) NOT NULL,
  contact_person          VARCHAR(150) NOT NULL,
  email                   VARCHAR(190) NOT NULL,
  mobile                  VARCHAR(20) NOT NULL,
  website                 VARCHAR(190) NULL,
  country                 VARCHAR(80) NULL,
  address                 TEXT NULL,
  industry                VARCHAR(100) NULL,

  requirements_json       JSON NULL,   -- selected checkboxes: online_gateway, payment_links, ...

  monthly_gmv             VARCHAR(60) NULL,
  avg_transaction_value   VARCHAR(60) NULL,
  expected_txn_count      VARCHAR(60) NULL,
  is_international        TINYINT(1) NOT NULL DEFAULT 0,
  preferred_currencies    VARCHAR(190) NULL,
  settlement_frequency    VARCHAR(40) NULL,

  pipeline_stage          ENUM('new_lead','contacted','qualified','proposal_sent','documents_pending','kyc_submitted','under_review','approved','integration','active','lost','rejected') NOT NULL DEFAULT 'new_lead',
  assigned_customer_id    BIGINT UNSIGNED NULL,   -- customers.id, once activated with portal login

  created_at              DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at              DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  CONSTRAINT fk_cust_app_partner FOREIGN KEY (partner_id) REFERENCES partners(id) ON DELETE CASCADE,
  CONSTRAINT fk_cust_app_customer FOREIGN KEY (assigned_customer_id) REFERENCES customers(id) ON DELETE SET NULL,
  INDEX idx_cust_app_partner (partner_id),
  INDEX idx_cust_app_stage (pipeline_stage)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS customer_application_documents (
  id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  customer_application_id BIGINT UNSIGNED NOT NULL,
  doc_type                VARCHAR(80) NOT NULL,
  file_path                VARCHAR(255) NOT NULL,
  status                   ENUM('uploaded','under_review','approved','rejected') NOT NULL DEFAULT 'uploaded',
  uploaded_at              DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_cust_app_doc FOREIGN KEY (customer_application_id) REFERENCES customer_applications(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Products recommended/selected against a specific customer application.
CREATE TABLE IF NOT EXISTS customer_application_products (
  id                       BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  customer_application_id  BIGINT UNSIGNED NOT NULL,
  product_id               BIGINT UNSIGNED NOT NULL,
  status                   ENUM('recommended','selected','active') NOT NULL DEFAULT 'recommended',
  added_at                 DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_cap_application FOREIGN KEY (customer_application_id) REFERENCES customer_applications(id) ON DELETE CASCADE,
  CONSTRAINT fk_cap_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  UNIQUE KEY uq_cust_app_product (customer_application_id, product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- CRM notes / follow-ups against a customer application.
CREATE TABLE IF NOT EXISTS customer_application_notes (
  id                       BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  customer_application_id  BIGINT UNSIGNED NOT NULL,
  author_user_id           BIGINT UNSIGNED NOT NULL,
  note                     TEXT NOT NULL,
  follow_up_at             DATETIME NULL,
  created_at               DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_can_application FOREIGN KEY (customer_application_id) REFERENCES customer_applications(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Commission configuration (admin-controlled, never hardcoded in code)
-- ---------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS commission_rules (
  id               BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name             VARCHAR(150) NOT NULL,
  rule_type        ENUM('product_based','revenue_share','referral','tiered') NOT NULL,
  product_id       BIGINT UNSIGNED NULL,
  rate_percent     DECIMAL(5,2) NOT NULL,
  tier_min_volume  DECIMAL(14,2) NULL,
  tier_max_volume  DECIMAL(14,2) NULL,
  is_active        TINYINT(1) NOT NULL DEFAULT 1,
  created_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_commission_rule_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Proposal builder
-- ---------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS proposals (
  id                       BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  proposal_code            VARCHAR(40) NOT NULL UNIQUE,   -- PYN-PROP-2026-000001
  partner_id               BIGINT UNSIGNED NOT NULL,
  customer_application_id  BIGINT UNSIGNED NOT NULL,
  title                    VARCHAR(190) NOT NULL,
  implementation_notes     TEXT NULL,
  status                   ENUM('draft','sent','viewed','negotiation','accepted','rejected','expired') NOT NULL DEFAULT 'draft',
  valid_until              DATE NULL,
  sent_at                  DATETIME NULL,
  created_at               DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at               DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_proposal_partner FOREIGN KEY (partner_id) REFERENCES partners(id) ON DELETE CASCADE,
  CONSTRAINT fk_proposal_application FOREIGN KEY (customer_application_id) REFERENCES customer_applications(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS proposal_items (
  id             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  proposal_id    BIGINT UNSIGNED NOT NULL,
  product_id     BIGINT UNSIGNED NOT NULL,
  pricing_note   VARCHAR(190) NOT NULL DEFAULT 'Talk to Sales',
  sort_order     SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  CONSTRAINT fk_proposal_item_proposal FOREIGN KEY (proposal_id) REFERENCES proposals(id) ON DELETE CASCADE,
  CONSTRAINT fk_proposal_item_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Payment links — extend the base table with partner/CRM context
-- ---------------------------------------------------------------------

ALTER TABLE payment_links
  ADD COLUMN partner_id               BIGINT UNSIGNED NULL AFTER created_by,
  ADD COLUMN customer_application_id  BIGINT UNSIGNED NULL AFTER partner_id;

-- ---------------------------------------------------------------------
-- Support — threaded replies on the base support_tickets table
-- ---------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS support_messages (
  id                BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ticket_id         BIGINT UNSIGNED NOT NULL,
  sender_user_id    BIGINT UNSIGNED NOT NULL,
  message           TEXT NOT NULL,
  attachment_path   VARCHAR(255) NULL,
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_support_msg_ticket FOREIGN KEY (ticket_id) REFERENCES support_tickets(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Knowledge Center + Marketing Hub (admin-uploaded)
-- ---------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS partner_resources (
  id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category      VARCHAR(60) NOT NULL,   -- getting_started, product_guides, payment_gateway_guide, integration_guide, api_documentation, customer_onboarding_guide, kyc_guide, commission_guide, faq
  title         VARCHAR(190) NOT NULL,
  description   VARCHAR(300) NULL,
  file_path     VARCHAR(255) NULL,
  external_url  VARCHAR(255) NULL,
  is_active     TINYINT(1) NOT NULL DEFAULT 1,
  sort_order    SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS marketing_assets (
  id             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category       VARCHAR(60) NOT NULL,   -- brochure, presentation, social_creative, email_template, proposal_template, video, brand_asset
  title          VARCHAR(190) NOT NULL,
  description    VARCHAR(300) NULL,
  file_path      VARCHAR(255) NULL,
  thumbnail_path VARCHAR(255) NULL,
  is_active      TINYINT(1) NOT NULL DEFAULT 1,
  sort_order     SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
