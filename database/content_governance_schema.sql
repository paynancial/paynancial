-- =====================================================================
-- Paynancial content governance — schema extension
-- Run AFTER schema.sql. Not applied automatically.
--
-- Approved decisions (23 Sep 2026): regulatory claims are public only when
-- source-verified; unconfirmed capabilities and jurisdictions stay live but
-- noindex until approved; professional review is 'pending' until a
-- qualified professional has actually reviewed the content.
--
-- Today the authoritative gate is code (includes/content-governance.php,
-- includes/regulatory-context.php, includes/business-services.php), shown
-- read-only in the CMS at /admin/content-governance. When these tables are
-- wired in, a page's effective status must be the STRICTER of the code gate
-- and the database row, so a CMS edit can never make a page indexable on
-- its own: approval still needs approved_by + approved_on and every stage.
-- =====================================================================

-- Publishing workflow, in order (no stage may be skipped):
-- draft → source_verification → content_review → regulatory_review →
-- business_approval → seo_review → indexable → sitemap → published

CREATE TABLE IF NOT EXISTS content_readiness (
  path                VARCHAR(190) PRIMARY KEY,           -- e.g. /products/wallet-infrastructure
  content_type        ENUM('product_capability','ai_capability','jurisdiction','business_service','regulatory_page','other') NOT NULL,
  stage               ENUM('draft','source_verification','content_review','regulatory_review',
                           'business_approval','seo_review','indexable','sitemap','published') NOT NULL DEFAULT 'draft',
  indexable           TINYINT(1) NOT NULL DEFAULT 0,
  sitemap             TINYINT(1) NOT NULL DEFAULT 0,
  service_promotion   TINYINT(1) NOT NULL DEFAULT 0,
  service_enabled     TINYINT(1) NOT NULL DEFAULT 0,      -- jurisdictions: business evidence approved
  professional_review ENUM('pending','completed','not_applicable') NOT NULL DEFAULT 'pending',
  approval_evidence   TEXT NULL,                           -- internal evidence supplied for approval
  approved_by         BIGINT UNSIGNED NULL,
  approved_on         DATETIME NULL,
  reason              VARCHAR(300) NULL,
  updated_by          BIGINT UNSIGNED NULL,
  updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT chk_index_needs_approval CHECK (indexable = 0 OR (approved_by IS NOT NULL AND approved_on IS NOT NULL)),
  CONSTRAINT chk_sitemap_needs_index  CHECK (sitemap = 0 OR indexable = 1),
  CONSTRAINT chk_promo_needs_index    CHECK (service_promotion = 0 OR indexable = 1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS regulators (
  id             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name           VARCHAR(190) NOT NULL,
  authority_type ENUM('regulator','ministry','registrar','tax_authority','statute','licensing','aml','other') NOT NULL,
  jurisdiction   VARCHAR(80) NOT NULL,                     -- India, UAE, Singapore, Hong Kong, United Kingdom…
  official_url   VARCHAR(255) NOT NULL,
  UNIQUE KEY uq_regulator (name, jurisdiction)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS regulatory_references (
  id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ref_key             VARCHAR(80) NOT NULL UNIQUE,          -- matches includes/regulatory-context.php ids
  regulator_id        BIGINT UNSIGNED NULL,
  instrument_type     VARCHAR(60) NULL,                     -- Act, Master Direction, circular, notification…
  reference_number    VARCHAR(160) NULL,
  title               VARCHAR(255) NOT NULL,
  issue_date          DATE NULL,
  effective_date      DATE NULL,
  effective_note      VARCHAR(60) NULL,                     -- e.g. 'not applicable'
  superseded_date     DATE NULL,
  jurisdiction        VARCHAR(80) NOT NULL,
  applicable_sector   VARCHAR(160) NULL,
  applicable_product  VARCHAR(160) NULL,
  applicable_service  VARCHAR(160) NULL,
  applicability       TEXT NULL,
  customer_impact     TEXT NULL,
  summary             TEXT NOT NULL,
  paynancial_relevance TEXT NULL,
  official_source     VARCHAR(190) NULL,
  source_url          VARCHAR(255) NULL,
  source_document     VARCHAR(255) NULL,
  last_verified       DATE NULL,
  next_review         DATE NULL,
  reviewer_status     VARCHAR(120) NOT NULL DEFAULT 'Professional review: Pending',
  status              ENUM('draft','source_verification_pending','under_review','verified','superseded','not_applicable','archived')
                      NOT NULL DEFAULT 'source_verification_pending',
  review_required     TINYINT(1) NOT NULL DEFAULT 0,
  source_updated      TINYINT(1) NOT NULL DEFAULT 0,
  updated_by          BIGINT UNSIGNED NULL,
  updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_ref_regulator FOREIGN KEY (regulator_id) REFERENCES regulators(id),
  -- A reference cannot be 'verified' without every required source field.
  CONSTRAINT chk_verified_complete CHECK (status <> 'verified' OR (
    official_source IS NOT NULL AND source_url IS NOT NULL AND reference_number IS NOT NULL
    AND issue_date IS NOT NULL AND (effective_date IS NOT NULL OR effective_note IS NOT NULL)
    AND applicability IS NOT NULL AND last_verified IS NOT NULL))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Reference ↔ where it is used (product, service, jurisdiction, page).
CREATE TABLE IF NOT EXISTS regulatory_reference_links (
  reference_id BIGINT UNSIGNED NOT NULL,
  target_type  ENUM('product','service','jurisdiction','page') NOT NULL,
  target_key   VARCHAR(160) NOT NULL,
  PRIMARY KEY (reference_id, target_type, target_key),
  CONSTRAINT fk_link_ref FOREIGN KEY (reference_id) REFERENCES regulatory_references(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Manual review queue (§45): references due for review or flagged.
CREATE OR REPLACE VIEW regulatory_review_queue AS
  SELECT ref_key, title, status, last_verified, next_review, review_required, source_updated
  FROM regulatory_references
  WHERE status IN ('draft','source_verification_pending','under_review')
     OR review_required = 1 OR source_updated = 1
     OR (next_review IS NOT NULL AND next_review <= CURRENT_DATE);
