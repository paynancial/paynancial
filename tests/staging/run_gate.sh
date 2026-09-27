#!/usr/bin/env bash
# =====================================================================
# Phase 1 staging gate — one command, mandated order. STAGING ONLY.
#
# Run ON the staging host (the session helpers write PHP session files
# the staging web server must be able to read). Never point at production.
#
# Required environment:
#   STAGING_URL      https://staging.example            (the staging site)
#   CODE_ROOT        /path/to/staging/docroot/parent     (deployed code; has config/ public/ includes/)
#   PYN_MYSQL        "mysql -h … -u … -p… stagingdb"     (CLI for the staging database)
#   PYN_MYSQLDUMP    "mysqldump -h … -u … -p… --single-transaction --routines --triggers stagingdb"
#   PYN_MYSQL_ADMIN  "mysql -h … -u … -p…"               (no db name; may CREATE/DROP the two drill databases)
#   DB_NAME          stagingdb
# Optional:
#   DEPLOY_CMD       command that deploys commit c238a25 to CODE_ROOT and reloads PHP-FPM
#                    (resets OPcache). If unset, the script pauses for a manual deploy.
#   OTP_RESULTS      file with 12 lines "role PASS|FAIL|NOT_VERIFIED <note>" for the real
#                    email-OTP sign-ins. If unset, the script asks interactively.
#   RELEASE_DIR      code containing database/migrations (default: this repository)
#   OUT_DIR          evidence directory (default: ./phase1-gate-YYYYmmdd-HHMMSS)
#   KEEP_FIXTURES=1  keep the *@stg.invalid fixtures afterwards
#
# Order (BEFORE snapshot is taken before any change, so the comparison means something):
#   backup → fixtures → snapshot BEFORE → run-once check → 25 Sep CMS migration (if required)
#   → drill dump (pre) → Phase 1 migration → drill dump (post) → deploy code + OPcache
#   → hosting-stack checks → live_qa → rbac_matrix → security_probes → 12 email-OTP sign-ins
#   → snapshot AFTER → compare → rollback drill on restored copies → gate table
# =====================================================================
set -uo pipefail
HERE="$(cd "$(dirname "$0")" && pwd)"; REPO="$(cd "$HERE/../.." && pwd)"
RELEASE_DIR="${RELEASE_DIR:-$REPO}"; MIG="$RELEASE_DIR/database/migrations"
OUT_DIR="${OUT_DIR:-$PWD/phase1-gate-$(date +%Y%m%d-%H%M%S)}"; mkdir -p "$OUT_DIR"
LOG="$OUT_DIR/gate.log"; RES="$OUT_DIR/results.tsv"; : > "$RES"
for v in STAGING_URL CODE_ROOT PYN_MYSQL PYN_MYSQLDUMP PYN_MYSQL_ADMIN DB_NAME; do [ -n "${!v:-}" ] || { echo "Missing $v"; exit 2; }; done
say() { echo "[$(date +%H:%M:%S)] $*" | tee -a "$LOG"; }
record() { printf '%s\t%s\t%s\n' "$1" "$2" "$3" >> "$RES"; say "RESULT $1: $2 $3"; }
q() { $PYN_MYSQL -N -e "$1"; }
stop() { record "$1" FAIL "$2"; say "STOP: $2"; write_table; exit 1; }
export PYN_MYSQL STAGING_FIXTURES=1

write_table() {
  local f="$OUT_DIR/gate-results.md"
  { echo "# Phase 1 staging gate — $(date '+%d %b %Y %H:%M')"; echo; echo "Site: $STAGING_URL · code: $CODE_ROOT · database: $DB_NAME"; echo
    echo "| Item | Result | Evidence |"; echo "|---|---|---|"
    while IFS=$'\t' read -r k r e; do echo "| $k | **$r** | $e |"; done < "$RES"
    echo; if grep -qP '\t(FAIL|NOT VERIFIED)\t' "$RES"; then echo "**Status: PRODUCTION NOT READY**"; else echo "**All items PASS — eligible for: READY FOR PRODUCTION AUTHORIZATION**"; fi
  } > "$f"; say "Gate table: $f"
}

# ------------------------------------------------------------ 0. preflight
ENV=$(php -r "require '$CODE_ROOT/config/config.php'; echo APP_ENV;" 2>/dev/null)
[ "$ENV" = "production" ] && { echo "Refusing: $CODE_ROOT has APP_ENV=production"; exit 2; }
curl -s -o /dev/null -w '%{http_code}' --max-time 15 "$STAGING_URL/" | grep -qE '^(200|30.)$' || { echo "Staging not reachable: $STAGING_URL"; exit 2; }
say "Preflight OK (APP_ENV=$ENV)"

# ------------------------------------------------------------ 1. backup
$PYN_MYSQLDUMP > "$OUT_DIR/backup-pre-gate.sql" 2>>"$LOG" || stop "Backup" "mysqldump failed"
tables=$(grep -c '^CREATE TABLE' "$OUT_DIR/backup-pre-gate.sql")
[ "$tables" -gt 30 ] || stop "Backup" "dump has only $tables tables"
tar -czf "$OUT_DIR/code-pre-gate.tgz" -C "$CODE_ROOT" . 2>>"$LOG" || stop "Backup" "code archive failed"
record "Backup" PASS "database dump ($tables tables, $(du -h "$OUT_DIR/backup-pre-gate.sql" | cut -f1)) + code archive"

# ------------------------------------------------------------ 2. fixtures + snapshot BEFORE
php "$HERE/fixtures.php" "$CODE_ROOT" base >>"$LOG" 2>&1 || stop "Fixtures" "base fixtures failed"
python3 "$HERE/page_snapshot.py" "$STAGING_URL" "$OUT_DIR/html-before" "$CODE_ROOT" "$HERE/mksession.php" >>"$LOG" 2>&1 || stop "Snapshot BEFORE" "failed"
record "Snapshot BEFORE" PASS "$(ls "$OUT_DIR/html-before" | wc -l) pages captured before any change"

# ------------------------------------------------------------ 3. run-once check (never rerun blindly)
applied=$(q "SELECT COUNT(*) FROM permissions WHERE slug='dashboard.view'")
[ "$applied" = "0" ] || stop "Run-once check" "Phase 1 migration already applied (dashboard.view exists) — not re-running"
record "Run-once check" PASS "Phase 1 migration not yet applied"

# ------------------------------------------------------------ 4. 25 Sep CMS migration (if required)
cms=$(q "SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='blog_posts' AND column_name='published_json'")
if [ "$cms" = "0" ]; then
  $PYN_MYSQL < "$MIG/2026-09-25-cms-editing.sql" >>"$LOG" 2>&1 || stop "25 Sep CMS migration" "failed"
  record "25 Sep CMS migration" PASS "applied"
else record "25 Sep CMS migration" PASS "already present — skipped"; fi

# ------------------------------------------------------------ 5. Phase 1 migration (with drill dumps either side)
$PYN_MYSQLDUMP > "$OUT_DIR/drill-pre-phase1.sql" 2>>"$LOG" || stop "Phase 1 migration" "pre-migration dump failed"
$PYN_MYSQL < "$MIG/2026-09-28-phase1-admin-platform.sql" >>"$LOG" 2>&1 || stop "Phase 1 migration" "migration failed — restore backup-pre-gate.sql"
$PYN_MYSQLDUMP > "$OUT_DIR/drill-post-phase1.sql" 2>>"$LOG"
v=$(q "SELECT CONCAT((SELECT COUNT(*) FROM roles WHERE slug IN ('operations_manager','sales_manager','consultant','incorporation_consultant','content_manager','seo_manager','compliance_reviewer','finance_manager','developer','support')),'/',(SELECT COUNT(*) FROM permissions WHERE slug IN ('dashboard.view','approvals.view','activity.view','enquiries.view','enquiries.create','enquiries.export','customers.create','documents.view','documents.view_hr','products.view','products.manage','users.view','roles.view','roles.manage','audit.view','security.view','security.manage','governance.view','system.health.view')),'/',(SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name IN ('user_preferences','admin_documents_v')),'/',(SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND ((table_name='audit_logs' AND column_name IN ('actor_role','user_agent')) OR (table_name='customers' AND column_name='user_id' AND is_nullable='YES'))))")
[ "$v" = "10/19/2/3" ] && record "Phase 1 migration" PASS "10 roles / 19 permissions / preferences + documents view / audit + customer columns" \
                      || record "Phase 1 migration" FAIL "unexpected objects: $v (expected 10/19/2/3)"

# ------------------------------------------------------------ 6. deploy code + OPcache
if [ -n "${DEPLOY_CMD:-}" ]; then bash -c "$DEPLOY_CMD" >>"$LOG" 2>&1 || stop "Deploy Phase 1 code" "DEPLOY_CMD failed"
else read -r -p ">>> Deploy commit c238a25 to $CODE_ROOT now and reload PHP-FPM (OPcache). Press Enter when done… " _; fi
grep -q "ADMIN_STAFF_ROLES" "$CODE_ROOT/includes/auth.php" && [ -f "$CODE_ROOT/includes/admin/registry.php" ] \
  && record "Deploy Phase 1 code" PASS "Phase 1 files present in $CODE_ROOT" || stop "Deploy Phase 1 code" "Phase 1 files not found in CODE_ROOT"
php "$HERE/fixtures.php" "$CODE_ROOT" cms >>"$LOG" 2>&1 || record "Fixtures (CMS)" FAIL "cms fixture failed"

# ------------------------------------------------------------ 7. hosting-stack checks (evidence or NOT VERIFIED)
H=$(curl -s -D - -o /dev/null --max-time 15 "$STAGING_URL/")
case "$STAGING_URL" in https://*) record "HTTPS" PASS "site served over HTTPS";; *) record "HTTPS" "NOT VERIFIED" "STAGING_URL is not https";; esac
echo "$H" | grep -qi '^set-cookie:.*paynancial_session.*; *secure' && record "Secure cookie" PASS "session cookie has Secure" || record "Secure cookie" FAIL "session cookie without Secure: $(echo "$H" | grep -i '^set-cookie' | head -1 | tr -d '\r')"
echo "$H" | grep -qi '^x-powered-by: *php' && record "PHP version exposure" FAIL "X-Powered-By sent: $(echo "$H" | grep -i '^x-powered-by' | tr -d '\r')" || record "PHP version exposure" PASS "no X-Powered-By header"
echo "$H" | grep -qi '^server: *apache' && record "Apache configuration" PASS "$(echo "$H" | grep -i '^server' | tr -d '\r')" || record "Apache configuration" "NOT VERIFIED" "Server header: $(echo "$H" | grep -i '^server' | tr -d '\r' || echo none)"
probe="$CODE_ROOT/public/uploads/gate-probe-$$.php"; echo '<?php echo "EXEC"."UTED";' > "$probe"
b=$(curl -s --max-time 15 "$STAGING_URL/uploads/$(basename "$probe")"); rm -f "$probe"
c=$(curl -s -o /dev/null -w '%{http_code}' --max-time 15 "$STAGING_URL/uploads/.htaccess")
{ [[ "$b" != *EXECUTED* ]] && [[ "$c" =~ ^(403|404)$ ]]; } && record "Uploads .htaccess" PASS "PHP in uploads not executed; .htaccess not served ($c)" || record "Uploads .htaccess" FAIL "uploads executed PHP or served .htaccess (body='${b:0:20}', .htaccess=$c)"
dbv=$(q "SELECT VERSION()"); [[ "$dbv" =~ ^8\. ]] && ! [[ "$dbv" =~ -MariaDB ]] && record "MySQL 8 compatibility" PASS "$dbv (migrations applied)" || record "MySQL 8 compatibility" "NOT VERIFIED" "server is $dbv"
SID=$(php "$HERE/mksession.php" "$CODE_ROOT" stg-super@stg.invalid 2>/dev/null)
HP=$(curl -s --max-time 30 -b "paynancial_session=$SID" "$STAGING_URL/admin/system-health")
echo "$HP" > "$OUT_DIR/system-health.html"
state() { echo "$HP" | tr '\n' ' ' | grep -oP "$1</strong>.*?adm-pill--\K[a-z]+" | head -1; }
echo "$HP" | grep -q 'adm-hc' && record "System Health page" PASS "readable; $(echo "$HP" | grep -o 'adm-pill--[a-z]*' | sort | uniq -c | tr -s ' ' | tr '\n' ' ')" \
  || record "System Health page" "NOT VERIFIED" "not readable with a test session (are CLI and web sessions on the same save path?)"
op=$(state 'PHP OPcache'); ts=$(state 'Anti-spam \(Turnstile\)'); em=$(state 'Email transport')
[ "$op" = "green" ] && record "OPcache" PASS "System Health: operational (web SAPI)" || record "OPcache" "NOT VERIFIED" "System Health state: ${op:-unreadable}"
[ "$ts" = "green" ] && record "Turnstile configuration" PASS "keys configured (System Health)" || record "Turnstile configuration" "NOT VERIFIED" "System Health state: ${ts:-unreadable}"

# ------------------------------------------------------------ 8. automated QA
python3 "$REPO/tests/live/live_qa.py" "$STAGING_URL" > "$OUT_DIR/live_qa.txt" 2>&1 && record "live_qa.py" PASS "$(grep 'checks passed' "$OUT_DIR/live_qa.txt")" || record "live_qa.py" FAIL "$(grep 'checks passed' "$OUT_DIR/live_qa.txt")"
python3 "$HERE/rbac_matrix.py" "$STAGING_URL" "$CODE_ROOT" > "$OUT_DIR/rbac_matrix.txt" 2>&1
l=$(grep 'RBAC matrix:' "$OUT_DIR/rbac_matrix.txt"); [[ "$l" == *" 0 failed"* ]] && record "RBAC matrix (12 roles, 29/29 routes)" PASS "$l" || record "RBAC matrix (12 roles, 29/29 routes)" FAIL "${l:-did not complete}"
python3 "$HERE/security_probes.py" "$STAGING_URL" "$CODE_ROOT" > "$OUT_DIR/security_probes.txt" 2>&1
l=$(grep 'Security probes:' "$OUT_DIR/security_probes.txt"); [[ "$l" == *" 0 failed"* ]] && record "Security probes" PASS "$l" || record "Security probes" FAIL "${l:-did not complete}"

# ------------------------------------------------------------ 9. 12 real email-OTP sign-ins (people, real inboxes)
roles="super_admin admin operations_manager sales_manager consultant incorporation_consultant content_manager seo_manager compliance_reviewer finance_manager developer support"
ok=0; nv=0; bad=0
for r in $roles; do
  if [ -n "${OTP_RESULTS:-}" ]; then line=$(grep -E "^$r " "$OTP_RESULTS" | head -1); res=$(echo "$line" | awk '{print $2}'); note=$(echo "$line" | cut -d' ' -f3-)
  else read -r -p ">>> $r: sign in at $STAGING_URL with a real account + emailed OTP; reached the Command Center with the right sidebar? [PASS/FAIL/NOT_VERIFIED] " res; note="manual"; fi
  case "$res" in PASS) ok=$((ok+1));; FAIL) bad=$((bad+1));; *) nv=$((nv+1));; esac
  echo "$r ${res:-NOT_VERIFIED} ${note:-}" >> "$OUT_DIR/otp-signins.txt"
done
if [ $ok -eq 12 ]; then record "Real email OTP delivery" PASS "12 OTP emails received (System Health email check: ${em:-unreadable})"; else record "Real email OTP delivery" "NOT VERIFIED" "$ok/12 OTP emails confirmed (System Health email check: ${em:-unreadable})"; fi
if [ $ok -eq 12 ]; then record "12 real email-OTP sign-ins" PASS "12/12"; elif [ $bad -gt 0 ]; then record "12 real email-OTP sign-ins" FAIL "$ok pass, $bad fail, $nv not verified"; else record "12 real email-OTP sign-ins" "NOT VERIFIED" "$ok/12 confirmed"; fi

# ------------------------------------------------------------ 10. snapshot AFTER + compare
python3 "$HERE/page_snapshot.py" "$STAGING_URL" "$OUT_DIR/html-after" "$CODE_ROOT" "$HERE/mksession.php" >>"$LOG" 2>&1
d=$(diff -rq "$OUT_DIR/html-before" "$OUT_DIR/html-after" | tee "$OUT_DIR/snapshot-diff.txt" | wc -l)
[ "$d" = "0" ] && record "Before/after snapshot" PASS "$(ls "$OUT_DIR/html-after" | wc -l) pages identical" || record "Before/after snapshot" FAIL "$d files differ — see snapshot-diff.txt (test-created data can cause portal diffs; review each)"

# ------------------------------------------------------------ 11. rollback drill on restored copies
PRE="${DB_NAME}_gate_pre"; RB="${DB_NAME}_gate_rb"
$PYN_MYSQL_ADMIN -e "DROP DATABASE IF EXISTS \`$PRE\`; DROP DATABASE IF EXISTS \`$RB\`; CREATE DATABASE \`$PRE\`; CREATE DATABASE \`$RB\`;" 2>>"$LOG" || record "Rollback drill" FAIL "cannot create drill databases"
$PYN_MYSQL_ADMIN "$PRE" < "$OUT_DIR/drill-pre-phase1.sql" 2>>"$LOG"; $PYN_MYSQL_ADMIN "$RB" < "$OUT_DIR/drill-post-phase1.sql" 2>>"$LOG"
$PYN_MYSQL_ADMIN "$RB" < "$MIG/2026-09-28-phase1-admin-platform-rollback.sql" >>"$LOG" 2>&1; rbrc=$?
schema() { $PYN_MYSQL_ADMIN -N -e "SELECT table_name, column_name, column_type, is_nullable, column_default FROM information_schema.columns WHERE table_schema='$1' ORDER BY 1,2"; }
sums() { $PYN_MYSQL_ADMIN -N -e "SELECT table_name FROM information_schema.tables WHERE table_schema='$1' AND table_type='BASE TABLE' ORDER BY 1" | while read -r t; do echo "$t $($PYN_MYSQL_ADMIN -N -e "SELECT COUNT(*) FROM \`$1\`.\`$t\`") $($PYN_MYSQL_ADMIN -N -e "CHECKSUM TABLE \`$1\`.\`$t\`" | awk '{print $2}')"; done; }
if [ $rbrc -eq 0 ] && diff <(schema "$PRE") <(schema "$RB") >"$OUT_DIR/drill-schema.diff" && diff <(sums "$PRE") <(sums "$RB") >"$OUT_DIR/drill-data.diff"; then
  $PYN_MYSQL_ADMIN "$RB" < "$MIG/2026-09-28-phase1-admin-platform.sql" >>"$LOG" 2>&1 && diff <(schema "$DB_NAME") <(schema "$RB") >/dev/null \
    && record "Rollback drill" PASS "rollback = exact pre-Phase-1 schema + data; re-apply = live schema" || record "Rollback drill" FAIL "re-apply after rollback differs"
else record "Rollback drill" FAIL "rollback rc=$rbrc; see drill-schema.diff / drill-data.diff"; fi
$PYN_MYSQL_ADMIN -e "DROP DATABASE IF EXISTS \`$PRE\`; DROP DATABASE IF EXISTS \`$RB\`;" 2>>"$LOG"

# ------------------------------------------------------------ 12. cleanup + gate table
[ "${KEEP_FIXTURES:-0}" = "1" ] || php "$HERE/fixtures.php" "$CODE_ROOT" cleanup >>"$LOG" 2>&1
grep -qP '^Rollback drill\tPASS' "$RES" && record "Backups and rollback ready" PASS "backup-pre-gate.sql + code-pre-gate.tgz in $OUT_DIR; rollback drilled" \
  || record "Backups and rollback ready" FAIL "backup taken, but the rollback drill did not pass"
write_table
