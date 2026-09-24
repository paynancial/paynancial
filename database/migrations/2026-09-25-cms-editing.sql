-- =====================================================================
-- CMS editing layer (25 Sep 2026) — ADDITIVE migration.
-- MySQL 8.0.16+ / MariaDB 10.4+. Run ONCE, after schema.sql and seed.sql,
-- and only after a full database backup. Not yet run on production.
--
-- Adds, never removes:
--   * blog_posts  — editorial workflow statuses, structured content, SEO /
--                   indexing flags, approval + publish records and a frozen
--                   "published snapshot" (what the public site shows).
--   * cms_pages   — the same workflow for the homepage hero and per-page SEO.
--   * audit_logs  — an index for per-item approval history.
--   * permissions — ten cms.* permissions + default role grants.
--
-- Existing rows keep working: the old 'draft' / 'published' blog status
-- values stay valid, and nothing becomes public until the new CMS publishes
-- it (live = 1 with a published snapshot). Legacy cms_pages values
-- (content_json, meta_title, meta_description) are never read publicly.
-- =====================================================================

-- ---------------------------------------------------------------------
-- blog_posts
-- ---------------------------------------------------------------------
ALTER TABLE blog_posts
  MODIFY COLUMN status ENUM('draft','editorial_review','seo_review','business_legal_review','approved','published')
         NOT NULL DEFAULT 'draft',
  ADD COLUMN category        VARCHAR(60)  NULL AFTER title,
  ADD COLUMN content_json    JSON         NULL AFTER body_html,
  ADD COLUMN og_title        VARCHAR(190) NULL AFTER meta_description,
  ADD COLUMN og_description  VARCHAR(300) NULL AFTER og_title,
  ADD COLUMN og_image        VARCHAR(255) NULL AFTER og_description,
  ADD COLUMN indexable       TINYINT(1)   NOT NULL DEFAULT 0 AFTER og_image,
  ADD COLUMN in_sitemap      TINYINT(1)   NOT NULL DEFAULT 0 AFTER indexable,
  ADD COLUMN live            TINYINT(1)   NOT NULL DEFAULT 0 AFTER in_sitemap,
  ADD COLUMN source          ENUM('cms','file_import') NOT NULL DEFAULT 'cms' AFTER live,
  ADD COLUMN review_note     VARCHAR(500) NULL AFTER source,
  ADD COLUMN submitted_by    BIGINT UNSIGNED NULL AFTER review_note,
  ADD COLUMN submitted_at    DATETIME NULL AFTER submitted_by,
  ADD COLUMN approved_by     BIGINT UNSIGNED NULL AFTER submitted_at,
  ADD COLUMN approved_at     DATETIME NULL AFTER approved_by,
  ADD COLUMN published_by    BIGINT UNSIGNED NULL AFTER published_at,
  ADD COLUMN published_json  JSON NULL AFTER published_by,
  ADD COLUMN updated_by      BIGINT UNSIGNED NULL AFTER published_json,
  ADD COLUMN lock_version    INT UNSIGNED NOT NULL DEFAULT 0 AFTER updated_by,
  ADD INDEX idx_blog_status (status),
  ADD INDEX idx_blog_live (live),
  ADD CONSTRAINT chk_blog_live_snapshot CHECK (live = 0 OR published_json IS NOT NULL);

-- ---------------------------------------------------------------------
-- cms_pages (page_key 'home' = homepage hero, 'seo:/path' = page SEO)
-- ---------------------------------------------------------------------
ALTER TABLE cms_pages
  ADD COLUMN workflow_status ENUM('draft','editorial_review','seo_review','business_legal_review','approved','published')
             NULL AFTER content_json,
  ADD COLUMN live            TINYINT(1) NOT NULL DEFAULT 0 AFTER workflow_status,
  ADD COLUMN review_note     VARCHAR(500) NULL AFTER live,
  ADD COLUMN submitted_by    BIGINT UNSIGNED NULL AFTER review_note,
  ADD COLUMN submitted_at    DATETIME NULL AFTER submitted_by,
  ADD COLUMN approved_by     BIGINT UNSIGNED NULL AFTER submitted_at,
  ADD COLUMN approved_at     DATETIME NULL AFTER approved_by,
  ADD COLUMN published_json  JSON NULL AFTER approved_at,
  ADD COLUMN published_at    DATETIME NULL AFTER published_json,
  ADD COLUMN published_by    BIGINT UNSIGNED NULL AFTER published_at,
  ADD COLUMN lock_version    INT UNSIGNED NOT NULL DEFAULT 0 AFTER published_by,
  ADD INDEX idx_cms_live (live),
  ADD CONSTRAINT chk_cms_live_snapshot CHECK (live = 0 OR published_json IS NOT NULL);

-- ---------------------------------------------------------------------
-- audit_logs: per-item approval history
-- ---------------------------------------------------------------------
ALTER TABLE audit_logs ADD INDEX idx_audit_entity (entity_type, entity_id);

-- ---------------------------------------------------------------------
-- Permissions (enforced server-side by includes/permissions.php)
-- ---------------------------------------------------------------------
INSERT INTO permissions (slug, name, module) VALUES
  ('cms.view',             'CMS: view content and history',              'cms'),
  ('cms.create',           'CMS: create articles',                       'cms'),
  ('cms.edit',             'CMS: edit drafts',                           'cms'),
  ('cms.submit',           'CMS: submit drafts for review',              'cms'),
  ('cms.review.editorial', 'CMS: editorial review (pass / reject)',      'cms'),
  ('cms.review.seo',       'CMS: SEO / AEO review (pass / reject)',      'cms'),
  ('cms.approve',          'CMS: business / legal review and approval',  'cms'),
  ('cms.publish',          'CMS: publish approved content',              'cms'),
  ('cms.unpublish',        'CMS: unpublish live content',                'cms'),
  ('cms.seo.manage',       'CMS: indexing and sitemap flags',            'cms')
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Super admin: every CMS permission.
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT (SELECT id FROM roles WHERE slug = 'super_admin'), p.id FROM permissions p WHERE p.module = 'cms';

-- Admin: author only (view, create, edit, submit). Reviews, approval,
-- publishing and indexing are granted per person (Admin → CMS → Access).
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT (SELECT id FROM roles WHERE slug = 'admin'), p.id FROM permissions p
WHERE p.slug IN ('cms.view','cms.create','cms.edit','cms.submit');
