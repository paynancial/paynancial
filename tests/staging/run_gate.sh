#!/usr/bin/env bash
# =====================================================================
# Phase 1 staging gate: one command, mandated order. STAGING ONLY.
#
# Run it ON the staging host: the session helpers write PHP session files
# that the staging web server must be able to read. It refuses production.
#
# Required environment:
#   STAGING_URL      https://staging.example            (the staging site)
#   CODE_ROOT        /path/to/deployed/code              (has config/ public/ includes/)
#   PYN_MYSQL        "mysql -h … -u … -p… stagingdb"     (CLI for the staging database)
#   PYN_MYSQLDUMP    "mysqldump -h … -u … -p… --single-transaction --routines --triggers stagingdb"
#   PYN_MYSQL_ADMIN  "mysql -h … -u … -p…"               (no db name; may CREATE/DROP the drill databases)
#   DB_NAME          stagingdb
# Optional:
#   DEPLOY_CMD       deploys EXPECTED_COMMIT to CODE_ROOT, reloads PHP-FPM (resets OPcache) and clears caches.
#                    If unset, the script pauses for a manual deployment.
#   EXPECTED_COMMIT  commit the deployed application files must match (default c238a25)
#   OTP_RESULTS      file with 12 lines "role PASS|FAIL|NOT_VERIFIED email note" for the real email-OTP
#                    sign-ins (one real staff account per role). If unset, the script asks for each role.
#   PRODUCTION_HOSTS extra production hostnames to refuse (comma or space separated)
#   ALREADY_MIGRATED=1  only when the runbook states this staging database already has the Phase 1
#                    migration: the run-once check then records that instead of stopping. Never re-runs it.
#   RELEASE_DIR      git checkout holding database/migrations and EXPECTED_COMMIT (default: this repository)
#   OUT_DIR          evidence directory (default: ./phase1-gate-YYYYmmdd-HHMMSS)
#   KEEP_FIXTURES=1  keep the fixtures (debugging only: the AFTER snapshot is then skipped)
#
# Order (the BEFORE snapshot is taken immediately after the backup, before migrations, deployment
# or fixture creation; fixtures are temporary and removed before the AFTER snapshot):
#    1 backup                     2 BEFORE snapshot            3 run-once migration check
#    4 25 Sep CMS migration (if required)                      5 Phase 1 migration
#    6 create / verify staging fixtures                        7 deploy Phase 1 code
#    8 hosting checks             9 live_qa.py                10 rbac_matrix.py
#   11 security_probes.py        12 12 real email-OTP sign-ins
#   13 clean up fixtures (and verify nothing remains)         14 AFTER snapshot
#   15 BEFORE/AFTER comparison   16 rollback drill (restored copies only)   17 gate table
# If the script stops early, the fixtures are still removed and the gate table is still written.
# =====================================================================
set -uo pipefail
HERE="$(cd "$(dirname "$0")" && pwd)"; REPO="$(cd "$HERE/../.." && pwd)"
RELEASE_DIR="${RELEASE_DIR:-$REPO}"; MIG="$RELEASE_DIR/database/migrations"; EXPECTED_COMMIT="${EXPECTED_COMMIT:-c238a25}"
OUT_DIR="${OUT_DIR:-$PWD/phase1-gate-$(date +%Y%m%d-%H%M%S)}"; mkdir -p "$OUT_DIR"
LOG="$OUT_DIR/gate.log"; RES="$OUT_DIR/results.tsv"; OTP_TSV="$OUT_DIR/otp-signins.tsv"; : > "$RES"; : > "$OTP_TSV"
for v in STAGING_URL CODE_ROOT PYN_MYSQL PYN_MYSQLDUMP PYN_MYSQL_ADMIN DB_NAME; do [ -n "${!v:-}" ] || { echo "Missing $v"; exit 2; }; done
say() { echo "[$(date +%H:%M:%S)] $*" | tee -a "$LOG"; }
record() { printf '%s\t%s\t%s\n' "$1" "$2" "$3" >> "$RES"; say "RESULT $1: $2 $3"; }
q() { $PYN_MYSQL -N -e "$1"; }
stop() { record "$1" FAIL "$2"; say "STOP: $2"; exit 1; }
export PYN_MYSQL STAGING_FIXTURES=1
FIX=0; STARTED=0

REQUIRED=("Backup" "Snapshot BEFORE" "Run-once check" "25 Sep CMS migration" "Phase 1 migration" "Staging fixtures" "Deploy Phase 1 code"
  "HTTPS" "Secure cookie" "Apache configuration" "Uploads .htaccess" "PHP version exposure" "OPcache" "MySQL 8 compatibility"
  "Turnstile configuration" "System Health page" "live_qa.py" "RBAC matrix (12 roles, 29/29 routes)" "Security probes (55)"
  "Email transport (real OTP delivery)" "12 real email-OTP sign-ins" "Fixture cleanup" "Snapshot AFTER" "Before/after comparison"
  "Rollback drill (exact, pre-Phase-1 copy)" "Rollback drill (current staging copy)" "Backups and rollback ready")

write_table() {
  local f="$OUT_DIR/gate-results.md" status="READY FOR PRODUCTION AUTHORIZATION" k
  for k in "${REQUIRED[@]}"; do grep -qP "^\Q$k\E\tPASS\t" "$RES" || status="PRODUCTION NOT READY"; done
  grep -qP '\t(FAIL|NOT VERIFIED)\t' "$RES" && status="PRODUCTION NOT READY"
  { echo "# Phase 1 staging gate: $(date '+%d %b %Y %H:%M')"; echo
    echo "Site: $STAGING_URL · code: $CODE_ROOT · database: $DB_NAME · expected commit: $EXPECTED_COMMIT"; echo
    echo "| Item | Result | Evidence |"; echo "|---|---|---|"
    while IFS=$'\t' read -r k r e; do echo "| $k | **$r** | $e |"; done < "$RES"
    for k in "${REQUIRED[@]}"; do grep -qP "^\Q$k\E\t" "$RES" || echo "| $k | **NOT VERIFIED** | not reached (the gate stopped earlier) |"; done
    if [ -s "$OTP_TSV" ]; then echo; echo "## Real email-OTP sign-ins (per role)"; echo
      echo "| Role | Operator result | Account | auth.login audit row since gate start | Result | Note |"; echo "|---|---|---|---|---|---|"
      while IFS=$'\t' read -r a b c d e f; do echo "| $a | $b | $c | $d | **$e** | $f |"; done < "$OTP_TSV"; fi
    echo; echo "**Status: $status**"
  } > "$f"; say "Gate table: $f"; echo; echo "$status"
}
cleanup_fixtures() {   # remove every fixture and prove nothing remains
  php "$HERE/fixtures.php" "$CODE_ROOT" cleanup >>"$LOG" 2>&1
  php "$HERE/fixtures.php" "$CODE_ROOT" check-clean > "$OUT_DIR/fixture-cleanup.txt" 2>&1; local rc=$?
  cat "$OUT_DIR/fixture-cleanup.txt" >> "$LOG"; FIX=0; return $rc
}
finish() {
  [ "$STARTED" = "1" ] || return
  if [ "$FIX" = "1" ] && [ "${KEEP_FIXTURES:-0}" != "1" ]; then
    if cleanup_fixtures; then record "Fixture cleanup" PASS "removed after an early stop; nothing left ($(tr '\n' ';' < "$OUT_DIR/fixture-cleanup.txt"))"
    else record "Fixture cleanup" FAIL "records remain after an early stop: $(grep LEFT "$OUT_DIR/fixture-cleanup.txt" | tr '\n' ';')"; fi
  fi
  write_table
}
trap finish EXIT
trap 'exit 130' INT TERM

# ------------------------------------------------------------ 0. preflight: never production
PRODUCTION_HOSTS="${PRODUCTION_HOSTS:-}"
host=$(echo "$STAGING_URL" | sed -E 's#^[A-Za-z]+://##; s#[/:?].*##' | tr 'A-Z' 'a-z')
for p in paynancial.com www.paynancial.com crm.paynancial.com ${PRODUCTION_HOSTS//,/ }; do
  [ "$host" = "$(echo "$p" | tr 'A-Z' 'a-z')" ] && { echo "Refusing: $host is a production hostname. Production deployment is outside this script."; exit 2; }
done
ENV=$(php -r "require '$CODE_ROOT/config/config.php'; echo APP_ENV;" 2>/dev/null)
[ -n "$ENV" ] || { echo "Cannot read APP_ENV from $CODE_ROOT/config/config.php"; exit 2; }
[ "$ENV" = "production" ] && { echo "Refusing: $CODE_ROOT has APP_ENV=production"; exit 2; }
curl -s -o /dev/null -w '%{http_code}' --max-time 15 "$STAGING_URL/" | grep -qE '^(200|30.)$' || { echo "Staging not reachable: $STAGING_URL"; exit 2; }
AUDIT_START=$(q "SELECT COALESCE(MAX(id),0) FROM audit_logs") || { echo "Staging database not reachable with PYN_MYSQL"; exit 2; }
STARTED=1
say "Preflight OK (host=$host, APP_ENV=$ENV, audit_logs start id=$AUDIT_START)"

# ------------------------------------------------------------ 1. BACKUP
$PYN_MYSQLDUMP > "$OUT_DIR/backup-pre-gate.sql" 2>>"$LOG" || stop "Backup" "mysqldump failed"
tables=$(grep -c '^CREATE TABLE' "$OUT_DIR/backup-pre-gate.sql")
[ "$tables" -gt 30 ] || stop "Backup" "dump has only $tables tables"
tar -czf "$OUT_DIR/code-pre-gate.tgz" -C "$CODE_ROOT" . 2>>"$LOG" || stop "Backup" "code archive failed"
record "Backup" PASS "database dump ($tables tables, $(du -h "$OUT_DIR/backup-pre-gate.sql" | cut -f1)) + code archive"

# ------------------------------------------------------------ 2. BEFORE snapshot (no migration, no deploy, no fixtures, no data change)
python3 "$HERE/page_snapshot.py" "$STAGING_URL" "$OUT_DIR/snapshot-before" > "$OUT_DIR/snapshot-before.txt" 2>&1 || stop "Snapshot BEFORE" "failed: $(tail -1 "$OUT_DIR/snapshot-before.txt")"
record "Snapshot BEFORE" PASS "clean baseline, taken before any change: $(tail -1 "$OUT_DIR/snapshot-before.txt")"

# ------------------------------------------------------------ 3. run-once migration check (never rerun blindly)
applied=$(q "SELECT COUNT(*) FROM permissions WHERE slug='dashboard.view'")
MIGRATED_BEFORE=0
if [ "$applied" = "0" ]; then
  [ "${ALREADY_MIGRATED:-0}" = "1" ] && stop "Run-once check" "ALREADY_MIGRATED=1 but the Phase 1 migration is NOT applied: the runbook and the database disagree"
  record "Run-once check" PASS "Phase 1 migration not yet applied"
elif [ "${ALREADY_MIGRATED:-0}" = "1" ]; then
  MIGRATED_BEFORE=1; record "Run-once check" PASS "already migrated, as the runbook declares (ALREADY_MIGRATED=1); migration NOT re-run"
else stop "Run-once check" "Phase 1 migration already applied (dashboard.view exists) and the runbook does not declare it: not re-running"; fi

# ------------------------------------------------------------ 4. 25 Sep CMS migration (if required)
cms=$(q "SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='blog_posts' AND column_name='published_json'")
if [ "$cms" = "0" ]; then
  [ "$MIGRATED_BEFORE" = "1" ] && stop "25 Sep CMS migration" "Phase 1 present but the 25 Sep CMS migration is missing: inconsistent database"
  $PYN_MYSQL < "$MIG/2026-09-25-cms-editing.sql" >>"$LOG" 2>&1 || stop "25 Sep CMS migration" "failed; restore backup-pre-gate.sql"
  record "25 Sep CMS migration" PASS "applied"
else record "25 Sep CMS migration" PASS "already present, skipped"; fi

# ------------------------------------------------------------ 5. Phase 1 migration (drill dump taken just before it)
verify_p1() { q "SELECT CONCAT((SELECT COUNT(*) FROM roles WHERE slug IN ('operations_manager','sales_manager','consultant','incorporation_consultant','content_manager','seo_manager','compliance_reviewer','finance_manager','developer','support')),'/',(SELECT COUNT(*) FROM permissions WHERE slug IN ('dashboard.view','approvals.view','activity.view','enquiries.view','enquiries.create','enquiries.export','customers.create','documents.view','documents.view_hr','products.view','products.manage','users.view','roles.view','roles.manage','audit.view','security.view','security.manage','governance.view','system.health.view')),'/',(SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name IN ('user_preferences','admin_documents_v')),'/',(SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND ((table_name='audit_logs' AND column_name IN ('actor_role','user_agent')) OR (table_name='customers' AND column_name='user_id' AND is_nullable='YES'))))"; }
if [ "$MIGRATED_BEFORE" = "0" ]; then
  $PYN_MYSQLDUMP > "$OUT_DIR/drill-pre-phase1.sql" 2>>"$LOG" || stop "Phase 1 migration" "pre-migration dump failed"
  $PYN_MYSQL < "$MIG/2026-09-28-phase1-admin-platform.sql" >>"$LOG" 2>&1 || stop "Phase 1 migration" "migration failed; restore backup-pre-gate.sql"
fi
v=$(verify_p1)
[ "$v" = "10/19/2/3" ] || stop "Phase 1 migration" "unexpected objects: $v (expected 10/19/2/3); restore backup-pre-gate.sql"
[ "$MIGRATED_BEFORE" = "1" ] && record "Phase 1 migration" PASS "present (not re-run): 10 roles / 19 permissions / preferences + documents view / audit + customer columns" \
                             || record "Phase 1 migration" PASS "applied once: 10 roles / 19 permissions / preferences + documents view / audit + customer columns"

# ------------------------------------------------------------ 6. create / verify staging fixtures (temporary)
FIX=1
php "$HERE/fixtures.php" "$CODE_ROOT" base >>"$LOG" 2>&1 || stop "Staging fixtures" "base fixtures failed (see gate.log)"
php "$HERE/fixtures.php" "$CODE_ROOT" cms >>"$LOG" 2>&1 || stop "Staging fixtures" "CMS fixture failed (see gate.log)"
php "$HERE/fixtures.php" "$CODE_ROOT" verify > "$OUT_DIR/fixtures-verify.txt" 2>&1 || stop "Staging fixtures" "missing: $(grep MISSING "$OUT_DIR/fixtures-verify.txt" | tr '\n' ';')"
record "Staging fixtures" PASS "$(grep -c '^ok' "$OUT_DIR/fixtures-verify.txt") fixture groups verified (12 staff-role accounts, portal accounts, customer, KYC document row, partner, employee, enquiries, partner application, inactive product, CMS article); all @stg.invalid / -STG- / STG Fixture"

# ------------------------------------------------------------ 7. deploy Phase 1 code, then verify the code version
if [ -n "${DEPLOY_CMD:-}" ]; then bash -c "$DEPLOY_CMD" >>"$LOG" 2>&1 || stop "Deploy Phase 1 code" "DEPLOY_CMD failed"
elif [ -t 0 ]; then read -r -p ">>> Deploy commit $EXPECTED_COMMIT to $CODE_ROOT, reload PHP-FPM (OPcache) and clear caches. Press Enter when done… " _
else stop "Deploy Phase 1 code" "no DEPLOY_CMD and no terminal to pause on"; fi
verify_code() { python3 - "$RELEASE_DIR" "$EXPECTED_COMMIT" "$CODE_ROOT" <<'PY'
import hashlib, os, subprocess, sys
rel, commit, root = sys.argv[1:4]
try:
    out = subprocess.run(['git', '-C', rel, 'ls-tree', '-r', commit, '--', 'admin', 'api', 'customer', 'employee', 'hrms', 'includes', 'pages', 'partner', 'public'],
                         capture_output=True, text=True, check=True).stdout
except Exception:
    print(f'cannot read commit {commit} in {rel}'); sys.exit(2)
bad, n = [], 0
for line in out.splitlines():
    meta, path = line.split('\t', 1); mode, typ, sha = meta.split()
    if typ != 'blob' or mode == '120000': continue
    n += 1
    try: d = open(os.path.join(root, path), 'rb').read()
    except OSError: bad.append(path + ' (missing)'); continue
    if hashlib.sha1(b'blob %d\0' % len(d) + d).hexdigest() != sha: bad.append(path)
print(f'{n - len(bad)}/{n} application files match {commit}' + ('; differ: ' + ', '.join(bad[:8]) if bad else ''))
sys.exit(1 if bad else 0)
PY
}
cv=$(verify_code); cvrc=$?
css=$(curl -s -o /dev/null -w '%{http_code}' --max-time 15 "$STAGING_URL/assets/admin/admin.css")
if [ $cvrc -eq 0 ] && [ "$css" = "200" ]; then record "Deploy Phase 1 code" PASS "$cv; web root serves Phase 1 assets (admin.css $css)"
elif [ $cvrc -eq 2 ]; then stop "Deploy Phase 1 code" "code version NOT VERIFIED: $cv"
else stop "Deploy Phase 1 code" "$cv; admin.css $css"; fi

# ------------------------------------------------------------ 8. hosting checks (evidence or NOT VERIFIED; the local built-in server never passes these)
H=$(curl -s -D - -o /dev/null --max-time 15 "$STAGING_URL/")
case "$STAGING_URL" in https://*) curl -s -o /dev/null --max-time 15 "$STAGING_URL/" && record "HTTPS" PASS "served over HTTPS with a valid certificate" || record "HTTPS" FAIL "TLS error";;
                       *) record "HTTPS" "NOT VERIFIED" "STAGING_URL is not https";; esac
if echo "$H" | grep -qi '^set-cookie:.*paynancial_session.*; *secure'; then record "Secure cookie" PASS "session cookie has Secure"
else record "Secure cookie" FAIL "session cookie without Secure: $(echo "$H" | grep -i '^set-cookie' | head -1 | tr -d '\r')"; fi
if echo "$H" | grep -qi '^x-powered-by: *php'; then record "PHP version exposure" FAIL "X-Powered-By sent: $(echo "$H" | grep -i '^x-powered-by' | tr -d '\r')"
else record "PHP version exposure" PASS "no X-Powered-By header"; fi
if echo "$H" | grep -qi '^server: *apache'; then record "Apache configuration" PASS "$(echo "$H" | grep -i '^server' | tr -d '\r')"
else record "Apache configuration" "NOT VERIFIED" "Server header: $(echo "$H" | grep -i '^server' | tr -d '\r' || echo none)"; fi
probe="$CODE_ROOT/public/uploads/gate-probe-$$.php"; echo '<?php echo "EXEC"."UTED";' > "$probe"
b=$(curl -s --max-time 15 "$STAGING_URL/uploads/$(basename "$probe")"); rm -f "$probe"
c=$(curl -s -o /dev/null -w '%{http_code}' --max-time 15 "$STAGING_URL/uploads/.htaccess")
if [[ "$b" != *EXECUTED* ]] && [[ "$c" =~ ^(403|404)$ ]]; then record "Uploads .htaccess" PASS "PHP in uploads not executed; .htaccess not served ($c)"
else record "Uploads .htaccess" FAIL "uploads executed PHP or served .htaccess (body='${b:0:20}', .htaccess=$c)"; fi
dbv=$(q "SELECT CONCAT(VERSION(), ' / ', @@version_comment)")
if [[ "$dbv" =~ ^8\. ]] && ! [[ "$dbv" =~ [Mm]aria[Dd][Bb] ]]; then record "MySQL 8 compatibility" PASS "$dbv (both migrations applied on it)"
else record "MySQL 8 compatibility" "NOT VERIFIED" "engine/version is $dbv"; fi
SID=$(php "$HERE/mksession.php" "$CODE_ROOT" stg-super@stg.invalid 2>/dev/null)
HP=$(curl -s --max-time 30 -b "paynancial_session=$SID" "$STAGING_URL/admin/system-health"); echo "$HP" > "$OUT_DIR/system-health.html"
state() { echo "$HP" | tr '\n' ' ' | grep -oP "$1</strong>.*?adm-pill--\K[a-z]+" | head -1; }
if echo "$HP" | grep -q 'adm-hc'; then record "System Health page" PASS "served by the Phase 1 PHP runtime; $(echo "$HP" | grep -o 'adm-pill--[a-z]*' | sort | uniq -c | tr -s ' ' | tr '\n' ' ')"
else record "System Health page" FAIL "not served (runtime not reloaded, or CLI and web sessions not on the same save path)"; fi
op=$(state 'PHP OPcache'); ts=$(state 'Anti-spam \(Turnstile\)'); em=$(state 'Email transport')
[ "$op" = "green" ] && record "OPcache" PASS "System Health: operational (web SAPI)" || record "OPcache" "NOT VERIFIED" "System Health state: ${op:-unreadable}"
[ "$ts" = "green" ] && record "Turnstile configuration" PASS "keys configured (System Health)" || record "Turnstile configuration" "NOT VERIFIED" "System Health state: ${ts:-unreadable}"

# ------------------------------------------------------------ 9–11. automated QA (fixtures present)
python3 "$REPO/tests/live/live_qa.py" "$STAGING_URL" > "$OUT_DIR/live_qa.txt" 2>&1 && record "live_qa.py" PASS "$(grep 'checks passed' "$OUT_DIR/live_qa.txt")" || record "live_qa.py" FAIL "$(grep 'checks passed' "$OUT_DIR/live_qa.txt" || echo 'did not complete')"
python3 "$HERE/rbac_matrix.py" "$STAGING_URL" "$CODE_ROOT" > "$OUT_DIR/rbac_matrix.txt" 2>&1
l=$(grep 'RBAC matrix:' "$OUT_DIR/rbac_matrix.txt"); [[ "$l" == *" 0 failed"* ]] && record "RBAC matrix (12 roles, 29/29 routes)" PASS "$l" || record "RBAC matrix (12 roles, 29/29 routes)" FAIL "${l:-did not complete}"
python3 "$HERE/security_probes.py" "$STAGING_URL" "$CODE_ROOT" > "$OUT_DIR/security_probes.txt" 2>&1
l=$(grep 'Security probes:' "$OUT_DIR/security_probes.txt"); [[ "$l" == *"55 passed, 0 failed"* ]] && record "Security probes (55)" PASS "$l" || record "Security probes (55)" FAIL "${l:-did not complete} (expected 55 passed, 0 failed)"

# ------------------------------------------------------------ 12. 12 real email-OTP sign-ins (people, real inboxes, the real staging app)
# The operator's result counts only when the staging audit log shows a completed sign-in (auth.login, with that
# role) for that real account after the gate started. No session-file injection: fixture accounts do not count.
declare -A LABEL=([super_admin]="Super Admin" [admin]="Administrator" [operations_manager]="Operations Manager" [sales_manager]="Sales Manager"
  [consultant]="Consultant" [incorporation_consultant]="Incorporation Consultant" [content_manager]="Content Manager" [seo_manager]="SEO Manager"
  [compliance_reviewer]="Legal / Compliance Reviewer" [finance_manager]="Finance Manager" [developer]="Developer" [support]="Support")
ok=0; nv=0; bad=0
for r in super_admin admin operations_manager sales_manager consultant incorporation_consultant content_manager seo_manager compliance_reviewer finance_manager developer support; do
  res=""; email=""; note=""
  if [ -n "${OTP_RESULTS:-}" ]; then line=$(grep -E "^$r " "$OTP_RESULTS" | head -1); read -r _ res email note <<< "$line"
  elif [ -t 0 ]; then read -r -p ">>> ${LABEL[$r]}: sign in at $STAGING_URL with a real staff account and the emailed OTP. Result [PASS/FAIL/NOT_VERIFIED] and account email: " res email; note="entered at the prompt"; fi
  [[ "$email" == *@* ]] || { note="${email:+$email }$note"; email=""; }
  seen=0; [ -n "$email" ] && [[ "$email" != *@stg.invalid ]] && seen=$(q "SELECT COUNT(*) FROM audit_logs a JOIN users u ON u.id=a.entity_id JOIN roles ro ON ro.id=u.role_id WHERE a.id > $AUDIT_START AND a.action='auth.login' AND a.actor_role='$r' AND ro.slug='$r' AND u.email=$(printf "'%s'" "${email//\'/}")")
  case "${res:-NOT_VERIFIED}" in
    PASS) if [ "${seen:-0}" -gt 0 ]; then final=PASS; ok=$((ok+1)); else final="NOT VERIFIED"; nv=$((nv+1)); note="operator PASS but no auth.login audit row for this account and role since the gate started; $note"; fi;;
    FAIL) final=FAIL; bad=$((bad+1));;
    *) final="NOT VERIFIED"; nv=$((nv+1));;
  esac
  printf '%s\t%s\t%s\t%s\t%s\t%s\n' "${LABEL[$r]}" "${res:-NOT_VERIFIED}" "${email:-none}" "${seen:-0}" "$final" "$note" >> "$OTP_TSV"
done
if [ $ok -eq 12 ]; then record "Email transport (real OTP delivery)" PASS "12/12 OTP emails received and used (System Health email check: ${em:-unreadable})"
elif [ $bad -gt 0 ]; then record "Email transport (real OTP delivery)" FAIL "$ok/12 confirmed, $bad failed"
else record "Email transport (real OTP delivery)" "NOT VERIFIED" "$ok/12 OTP emails confirmed (System Health email check: ${em:-unreadable})"; fi
if [ $ok -eq 12 ]; then record "12 real email-OTP sign-ins" PASS "12/12, each confirmed by an auth.login audit row"
elif [ $bad -gt 0 ]; then record "12 real email-OTP sign-ins" FAIL "$ok pass, $bad fail, $nv not verified (per-role table below)"
else record "12 real email-OTP sign-ins" "NOT VERIFIED" "$ok/12 confirmed (per-role table below)"; fi

# ------------------------------------------------------------ 13. clean up fixtures, and prove nothing remains
if [ "${KEEP_FIXTURES:-0}" = "1" ]; then record "Fixture cleanup" "NOT VERIFIED" "KEEP_FIXTURES=1: fixtures kept"; stop "Snapshot AFTER" "skipped: fixtures kept (KEEP_FIXTURES=1)"; fi
if cleanup_fixtures; then record "Fixture cleanup" PASS "nothing left: $(sed 's/^ok *//' "$OUT_DIR/fixture-cleanup.txt" | tr '\n' ';')"
else stop "Fixture cleanup" "records remain: $(grep LEFT "$OUT_DIR/fixture-cleanup.txt" | tr '\n' ';'); AFTER snapshot not taken"; fi

# ------------------------------------------------------------ 14–15. AFTER snapshot (clean) and comparison
python3 "$HERE/page_snapshot.py" "$STAGING_URL" "$OUT_DIR/snapshot-after" > "$OUT_DIR/snapshot-after.txt" 2>&1 \
  && record "Snapshot AFTER" PASS "clean, fixtures removed: $(tail -1 "$OUT_DIR/snapshot-after.txt")" || record "Snapshot AFTER" FAIL "$(tail -1 "$OUT_DIR/snapshot-after.txt")"
python3 "$HERE/snapshot_compare.py" "$OUT_DIR/snapshot-before" "$OUT_DIR/snapshot-after" > "$OUT_DIR/snapshot-compare.txt" 2>&1 \
  && record "Before/after comparison" PASS "$(tail -1 "$OUT_DIR/snapshot-compare.txt")" \
  || record "Before/after comparison" FAIL "$(tail -1 "$OUT_DIR/snapshot-compare.txt"); see snapshot-compare.txt"
diff -r "$OUT_DIR/snapshot-before" "$OUT_DIR/snapshot-after" > "$OUT_DIR/snapshot-diff.txt" 2>&1

# ------------------------------------------------------------ 16. rollback drill: restored disposable copies only, never the staging database
PRE="${DB_NAME}_gate_pre"; RB="${DB_NAME}_gate_rb"; CUR="${DB_NAME}_gate_cur"
A() { $PYN_MYSQL_ADMIN -N "$@"; }
schema() { A -e "SELECT table_name, column_name, column_type, is_nullable, column_default FROM information_schema.columns WHERE table_schema='$1' ORDER BY 1,2"; }
sums() { A -e "SELECT table_name FROM information_schema.tables WHERE table_schema='$1' AND table_type='BASE TABLE' ORDER BY 1" | while read -r t; do
  case " $2 " in *" $t "*) continue;; esac; echo "$t $(A -e "SELECT COUNT(*) FROM \`$1\`.\`$t\`") $(A -e "CHECKSUM TABLE \`$1\`.\`$t\`" | awk '{print $2}')"; done; }
integrity() { A -e "SET SESSION group_concat_max_len=4294967295;
  SELECT 'users', COUNT(*), MD5(GROUP_CONCAT(CONCAT_WS('|',id,email,role_id,status) ORDER BY id SEPARATOR ';')) FROM \`$1\`.users
  UNION ALL SELECT 'customers', COUNT(*), MD5(GROUP_CONCAT(CONCAT_WS('|',id,user_id,customer_code,company_name,kyc_status,billing_address) ORDER BY id SEPARATOR ';')) FROM \`$1\`.customers
  UNION ALL SELECT 'blog_posts (CMS)', COUNT(*), MD5(GROUP_CONCAT(CONCAT_WS('|',id,slug,status,live,MD5(content_json),MD5(published_json)) ORDER BY id SEPARATOR ';')) FROM \`$1\`.blog_posts
  UNION ALL SELECT 'roles held by users', COUNT(DISTINCT u.role_id), MD5(GROUP_CONCAT(DISTINCT r.slug ORDER BY r.slug)) FROM \`$1\`.users u JOIN \`$1\`.roles r ON r.id=u.role_id
  UNION ALL SELECT 'users without a valid role', COUNT(*), '' FROM \`$1\`.users u LEFT JOIN \`$1\`.roles r ON r.id=u.role_id WHERE r.id IS NULL
  UNION ALL SELECT 'original 6 roles present', COUNT(*), '' FROM \`$1\`.roles WHERE slug IN ('super_admin','admin','customer','partner','employee','hr')"; }
P1_TABLES="audit_logs customers roles permissions role_permissions user_permissions user_preferences"
dropdrill() { A -e "DROP DATABASE IF EXISTS \`$PRE\`; DROP DATABASE IF EXISTS \`$RB\`; DROP DATABASE IF EXISTS \`$CUR\`;" 2>>"$LOG"; }
dropdrill
# 16a. exact: the post-migration state rolled back must equal the pre-migration state, then re-apply = live schema
if [ "$MIGRATED_BEFORE" = "1" ]; then record "Rollback drill (exact, pre-Phase-1 copy)" "NOT VERIFIED" "no pre-migration baseline: the database was already migrated (ALREADY_MIGRATED=1)"
else
  A -e "CREATE DATABASE \`$PRE\`; CREATE DATABASE \`$RB\`;" 2>>"$LOG"
  A "$PRE" < "$OUT_DIR/drill-pre-phase1.sql" 2>>"$LOG"
  # the exact comparison needs the post-migration state with no test activity: re-create it from the pre dump
  A "$RB" < "$OUT_DIR/drill-pre-phase1.sql" 2>>"$LOG"; A "$RB" < "$MIG/2026-09-28-phase1-admin-platform.sql" >>"$LOG" 2>&1
  A "$RB" < "$MIG/2026-09-28-phase1-admin-platform-rollback.sql" >>"$LOG" 2>&1; rbrc=$?
  if [ $rbrc -eq 0 ] && diff <(schema "$PRE") <(schema "$RB") > "$OUT_DIR/drill-exact-schema.diff" && diff <(sums "$PRE" "") <(sums "$RB" "") > "$OUT_DIR/drill-exact-data.diff"; then
    A "$RB" < "$MIG/2026-09-28-phase1-admin-platform.sql" >>"$LOG" 2>&1 && diff <(schema "$DB_NAME") <(schema "$RB") > "$OUT_DIR/drill-exact-reapply.diff" \
      && record "Rollback drill (exact, pre-Phase-1 copy)" PASS "rollback = exact pre-Phase-1 schema and data (every table: rows + checksum); re-apply = live schema" \
      || record "Rollback drill (exact, pre-Phase-1 copy)" FAIL "re-apply after rollback differs from the live schema (drill-exact-reapply.diff)"
  else record "Rollback drill (exact, pre-Phase-1 copy)" FAIL "rollback rc=$rbrc; see drill-exact-schema.diff / drill-exact-data.diff"; fi
fi
# 16b. current: a copy of staging as it is now (after the gate, fixtures removed)
$PYN_MYSQLDUMP > "$OUT_DIR/drill-current.sql" 2>>"$LOG"
A -e "CREATE DATABASE \`$CUR\`;" 2>>"$LOG"; A "$CUR" < "$OUT_DIR/drill-current.sql" 2>>"$LOG"
nologin=$(A -e "SELECT COUNT(*) FROM \`$CUR\`.customers WHERE user_id IS NULL")
integrity "$CUR" > "$OUT_DIR/drill-current-integrity-before.txt"; sums "$CUR" "$P1_TABLES" > "$OUT_DIR/drill-current-tables-before.txt"; schema "$CUR" > "$OUT_DIR/drill-current-schema-before.txt"
A "$CUR" < "$MIG/2026-09-28-phase1-admin-platform-rollback.sql" >>"$LOG" 2>&1; rbrc=$?
if [ "$nologin" != "0" ]; then   # documented: blocked, and nothing changes, while customers without a login exist
  [ $rbrc -ne 0 ] && diff <(schema "$CUR") "$OUT_DIR/drill-current-schema-before.txt" >/dev/null \
    && record "Rollback drill (current staging copy)" PASS "rollback blocked with nothing changed, as documented ($nologin customers without a login)" \
    || record "Rollback drill (current staging copy)" FAIL "rollback not blocked, or partly applied, with $nologin customers without a login"
else
  integrity "$CUR" > "$OUT_DIR/drill-current-integrity-after.txt"; sums "$CUR" "$P1_TABLES" > "$OUT_DIR/drill-current-tables-after.txt"
  gone=$(A -e "SELECT CONCAT((SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$CUR' AND table_name IN ('user_preferences','admin_documents_v')),'/',(SELECT COUNT(*) FROM information_schema.columns WHERE table_schema='$CUR' AND table_name='audit_logs' AND column_name IN ('actor_role','user_agent')),'/',(SELECT COUNT(*) FROM \`$CUR\`.permissions WHERE slug='dashboard.view'))")
  ntab=$(wc -l < "$OUT_DIR/drill-current-tables-after.txt")
  if [ $rbrc -eq 0 ] && [ "$gone" = "0/0/0" ] \
     && diff "$OUT_DIR/drill-current-integrity-before.txt" "$OUT_DIR/drill-current-integrity-after.txt" > "$OUT_DIR/drill-current-integrity.diff" \
     && diff "$OUT_DIR/drill-current-tables-before.txt" "$OUT_DIR/drill-current-tables-after.txt" > "$OUT_DIR/drill-current-tables.diff" \
     && grep -qP "^users without a valid role\t0\t" "$OUT_DIR/drill-current-integrity-after.txt" && grep -qP "^original 6 roles present\t6\t" "$OUT_DIR/drill-current-integrity-after.txt"; then
    A "$CUR" < "$MIG/2026-09-28-phase1-admin-platform.sql" >>"$LOG" 2>&1 && diff <(schema "$DB_NAME") <(schema "$CUR") > "$OUT_DIR/drill-current-reapply.diff" \
      && record "Rollback drill (current staging copy)" PASS "Phase 1 objects removed; users, customers, CMS (blog_posts) and roles held by users unchanged; every user keeps a valid role; original 6 roles present; $ntab other tables identical (rows + checksum); re-apply = live schema" \
      || record "Rollback drill (current staging copy)" FAIL "re-apply after rollback differs from the live schema (drill-current-reapply.diff)"
  else record "Rollback drill (current staging copy)" FAIL "rollback rc=$rbrc, remaining Phase 1 objects $gone; see drill-current-integrity.diff / drill-current-tables.diff"; fi
fi
dropdrill

# ------------------------------------------------------------ 17. final gate table (written by the EXIT trap)
if grep -qP '^Rollback drill \(exact, pre-Phase-1 copy\)\tPASS' "$RES" && grep -qP '^Rollback drill \(current staging copy\)\tPASS' "$RES"; then
  record "Backups and rollback ready" PASS "backup-pre-gate.sql + code-pre-gate.tgz in $OUT_DIR; rollback drilled on restored copies"
else record "Backups and rollback ready" FAIL "backup taken, but a rollback drill did not pass"; fi
exit 0
