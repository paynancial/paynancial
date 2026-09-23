-- ---------------------------------------------------------------------
-- Paynancial — Security & Onboarding extension
-- Additive migration: device recognition (OTP-on-new-device), sensitive
-- change-request maker-checker workflow, and self-service customer
-- signup/eKYC (distinct from the partner-referred customer_applications
-- table in partner_hub_schema.sql — that one always has a partner_id;
-- this covers a customer who signs up directly, with no partner).
-- Run once on top of schema.sql + partner_hub_schema.sql.
-- ---------------------------------------------------------------------

SET FOREIGN_KEY_CHECKS = 0;

-- Recognized devices per user, used to decide whether a login needs an
-- OTP step (unrecognized device) or can proceed on password alone.
CREATE TABLE IF NOT EXISTS known_devices (
  id                BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id           BIGINT UNSIGNED NOT NULL,
  device_token_hash VARCHAR(255) NOT NULL,
  label             VARCHAR(150) NULL,
  ip_address        VARCHAR(45) NULL,
  last_seen_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_known_device_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  UNIQUE KEY uq_device_user_token (user_id, device_token_hash),
  INDEX idx_known_device_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bumping this invalidates every other active session for the user —
-- the mechanism behind "log out of all other devices".
ALTER TABLE users
  ADD COLUMN session_version INT UNSIGNED NOT NULL DEFAULT 1 AFTER two_factor_enabled;

-- Maker-checker: no sensitive field is ever edited directly. A request
-- is raised, requires a reason, and needs a second authorized approver.
CREATE TABLE IF NOT EXISTS change_requests (
  id               BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  request_code     VARCHAR(40) NOT NULL UNIQUE,
  target_user_id   BIGINT UNSIGNED NOT NULL,
  field_name       VARCHAR(60) NOT NULL,
  old_value_masked VARCHAR(190) NULL,
  new_value        VARCHAR(190) NOT NULL,
  reason           TEXT NOT NULL,
  status           ENUM('pending','approved','rejected','applied') NOT NULL DEFAULT 'pending',
  requested_by     BIGINT UNSIGNED NOT NULL,
  decided_by       BIGINT UNSIGNED NULL,
  decision_note    VARCHAR(300) NULL,
  created_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  decided_at       DATETIME NULL,
  CONSTRAINT fk_change_req_target FOREIGN KEY (target_user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_change_req_requester FOREIGN KEY (requested_by) REFERENCES users(id),
  CONSTRAINT fk_change_req_decider FOREIGN KEY (decided_by) REFERENCES users(id),
  INDEX idx_change_req_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Self-service customer business profile (1:1 with customers). Kept
-- separate from the base `customers` table so onboarding-specific
-- fields don't clutter the account record.
CREATE TABLE IF NOT EXISTS customer_kyc_profiles (
  id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  customer_id             BIGINT UNSIGNED NOT NULL UNIQUE,
  legal_business_name     VARCHAR(190) NOT NULL,
  display_name            VARCHAR(150) NULL,
  business_type           VARCHAR(60) NULL,
  industry                VARCHAR(100) NULL,
  registered_address      TEXT NULL,
  website_url             VARCHAR(190) NULL,
  monthly_volume_band     VARCHAR(60) NULL,
  requested_products_json JSON NULL,
  signatory_name          VARCHAR(150) NULL,
  signatory_designation   VARCHAR(100) NULL,
  pan                     VARCHAR(20) NULL,
  gstin                   VARCHAR(20) NULL,
  onboarding_step         TINYINT UNSIGNED NOT NULL DEFAULT 1,
  created_at              DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at              DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_ckyc_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS customer_kyc_documents (
  id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  customer_id   BIGINT UNSIGNED NOT NULL,
  doc_type      VARCHAR(80) NOT NULL,
  file_path     VARCHAR(255) NOT NULL,
  status        ENUM('uploaded','under_review','info_required','verified','rejected') NOT NULL DEFAULT 'uploaded',
  status_note   VARCHAR(300) NULL,
  uploaded_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  reviewed_at   DATETIME NULL,
  CONSTRAINT fk_ckyc_doc_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS customer_bank_accounts (
  id                    BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  customer_id           BIGINT UNSIGNED NOT NULL,
  bank_name             VARCHAR(150) NOT NULL,
  account_holder        VARCHAR(150) NOT NULL,
  account_number_enc    VARBINARY(512) NOT NULL,
  account_number_last4  VARCHAR(4) NOT NULL,
  ifsc                  VARCHAR(20) NULL,
  status                ENUM('under_review','verified','rejected') NOT NULL DEFAULT 'under_review',
  created_at            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_cbank_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS customer_product_activations (
  id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  customer_id   BIGINT UNSIGNED NOT NULL,
  product_slug  VARCHAR(60) NOT NULL,
  status        ENUM('requested','active','pending_kyc','pending_delivery') NOT NULL DEFAULT 'requested',
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_cpa_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
  UNIQUE KEY uq_customer_product (customer_id, product_slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Account lifecycle distinct from document kyc_status (a customer can be
-- fully verified but still suspended, etc).
ALTER TABLE customers
  ADD COLUMN status ENUM('pending_verification','active','suspended') NOT NULL DEFAULT 'pending_verification' AFTER kyc_status;

SET FOREIGN_KEY_CHECKS = 1;
