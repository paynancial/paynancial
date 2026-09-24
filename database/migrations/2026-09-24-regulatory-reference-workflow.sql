-- Regulatory reference: reviewer + publishing workflow (24 Sep 2026).
-- Only for databases where content_governance_schema.sql was applied BEFORE
-- 24 Sep 2026 (new installs get these columns from that file directly).
-- Take a database backup first. Not yet run anywhere.
ALTER TABLE regulatory_references
  ADD COLUMN reviewer VARCHAR(190) NULL AFTER next_review,
  ADD COLUMN workflow_stage ENUM('draft','source_verification','editorial_review','seo_aeo_review',
                                 'legal_regulatory_review','approval','published')
             NOT NULL DEFAULT 'source_verification' AFTER reviewer_status,
  ADD COLUMN approved_by BIGINT UNSIGNED NULL AFTER workflow_stage,
  ADD COLUMN approved_on DATETIME NULL AFTER approved_by,
  ADD CONSTRAINT chk_published_approved CHECK (workflow_stage <> 'published' OR (
    status = 'verified' AND reviewer IS NOT NULL AND approved_by IS NOT NULL AND approved_on IS NOT NULL));

CREATE OR REPLACE VIEW regulatory_review_queue AS
  SELECT ref_key, title, status, workflow_stage, last_verified, next_review, review_required, source_updated
  FROM regulatory_references
  WHERE status IN ('draft','source_verification_pending','under_review') OR workflow_stage <> 'published'
     OR review_required = 1 OR source_updated = 1
     OR (next_review IS NOT NULL AND next_review <= CURRENT_DATE);
