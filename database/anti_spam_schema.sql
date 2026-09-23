-- =====================================================================
-- Paynancial — floating enquiry anti-spam (run AFTER schema.sql)
--
-- anti_spam_hits: server-side rate-limit store for the "Request a Callback"
-- form. Buckets are keyed HMAC-SHA256 hashes of (kind, value) — no raw IP
-- addresses, emails or phone numbers are stored. Rows older than a day are
-- pruned by the application. If this table is missing, includes/anti-spam.php
-- falls back to a file store under storage/anti-spam.
--
-- Settings live in the existing `settings` table (prefix fe_antispam_) and
-- are edited in Admin → Settings → Floating Enquiry Anti-Spam. The Turnstile
-- SECRET key is never stored in the database: set TURNSTILE_SECRET_KEY in
-- the server environment.
-- =====================================================================

CREATE TABLE IF NOT EXISTS anti_spam_hits (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  bucket      CHAR(64) NOT NULL,
  created_at  DATETIME NOT NULL,
  INDEX idx_anti_spam_bucket_time (bucket, created_at),
  INDEX idx_anti_spam_time (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
