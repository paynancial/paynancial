<?php
/**
 * Shared admin UI for the CMS pages (admin/cms*.php): flash messages,
 * fields with character counters, the workflow panel and approval history.
 * Every button here is only a request — cms_transition() re-checks
 * permissions and state server-side.
 */

declare(strict_types=1);

require_once __DIR__ . '/articles.php';
require_once __DIR__ . '/pages.php';
require_once __DIR__ . '/upload.php';

function cms_redirect(string $url, ?string $ok = null, ?string $err = null): never
{
    if ($ok !== null) {
        flash('cms_ok', $ok);
    }
    if ($err !== null) {
        flash('cms_err', $err);
    }
    header('Location: ' . $url, true, 303);
    exit;
}

function cms_flash_messages(): void
{
    foreach (['cms_ok' => 'success', 'cms_err' => 'failed'] as $key => $class) {
        $msg = flash($key);
        if ($msg) {
            echo '<div class="cms-alert cms-alert--' . $class . '" role="' . ($class === 'failed' ? 'alert' : 'status') . '">' . e($msg) . '</div>';
        }
    }
}

function cms_no_access(string $perm = 'cms.view'): void
{
    http_response_code(403);
    echo '<div class="panel"><div class="panel-head"><h2>No access</h2></div><p class="text-muted">You do not have the <code>'
        . e($perm) . '</code> permission. Ask a super admin to grant it (Admin → CMS → Access).</p></div>';
}

/** A labelled text input / textarea with a live character counter (soft limit) and a hard maxlength. */
function cms_field(string $name, string $label, ?string $value, array $limits = [0, 0], array $opt = []): void
{
    [$soft, $hard] = $limits + [0, 0];
    $id = 'f-' . $name;
    $err = $opt['error'] ?? null;
    $attrs = ($hard ? ' maxlength="' . $hard . '"' : '') . ($soft ? ' data-soft-limit="' . $soft . '"' : '')
        . (!empty($opt['required']) ? ' required' : '') . (!empty($opt['disabled']) ? ' disabled' : '')
        . (isset($opt['placeholder']) ? ' placeholder="' . e($opt['placeholder']) . '"' : '')
        . ($err ? ' aria-invalid="true" aria-describedby="' . $id . '-err"' : (isset($opt['help']) ? ' aria-describedby="' . $id . '-help"' : ''));
    echo '<div class="cms-field' . ($err ? ' has-error' : '') . '">';
    echo '<label for="' . $id . '">' . e($label) . (!empty($opt['required']) ? ' <span aria-hidden="true">*</span>' : '') . '</label>';
    if (($opt['type'] ?? 'input') === 'textarea') {
        echo '<textarea id="' . $id . '" name="' . e($name) . '" rows="' . (int) ($opt['rows'] ?? 3) . '"' . $attrs
            . (!empty($opt['mono']) ? ' class="cms-mono"' : '') . '>' . e($value) . '</textarea>';
    } else {
        echo '<input type="text" id="' . $id . '" name="' . e($name) . '" value="' . e($value) . '"' . $attrs . '>';
    }
    if ($soft) {
        echo '<span class="cms-count" data-for="' . $id . '" aria-live="polite"></span>';
    }
    if (isset($opt['help'])) {
        echo '<small id="' . $id . '-help" class="cms-help">' . $opt['help'] . '</small>';
    }
    if ($err) {
        echo '<small id="' . $id . '-err" class="cms-error">' . e($err) . '</small>';
    }
    echo '</div>';
}

/** DRAFT → … → PUBLISHED progress indicator. */
function cms_stepper(string $status, bool $live): void
{
    $order = array_keys(cms_statuses());
    $pos = array_search($status, $order, true);
    echo '<ol class="cms-steps" aria-label="Workflow">';
    foreach (cms_statuses() as $key => $label) {
        $i = array_search($key, $order, true);
        $state = $i < $pos ? 'done' : ($i === $pos ? 'current' : 'todo');
        echo '<li class="is-' . $state . '"' . ($state === 'current' ? ' aria-current="step"' : '') . '>' . e($label) . '</li>';
    }
    echo '</ol>';
    if ($live && $status !== 'published') {
        echo '<p class="cms-note">The previously published version stays live until this version is approved and published.</p>';
    }
}

/** Status badges for a row. */
function cms_status_badges(string $status, bool $live): string
{
    $html = '<span class="badge ' . cms_status_badge($status) . '">' . e(cms_statuses()[$status] ?? $status) . '</span>';
    if ($live) {
        $html .= ' <span class="badge success">Live</span>';
    }
    return $html;
}

/** Workflow actions available to this user (forms POST back to $postUrl). */
function cms_workflow_panel(array $user, string $type, array $row, string $postUrl): void
{
    $status = (string) ($row[cms_entity($type)['status']] ?? 'draft');
    $live = (int) $row['live'] === 1;
    $available = cms_available_actions($user, $type, $row);
    echo '<div class="panel cms-workflow"><div class="panel-head"><h2>Workflow</h2>' . cms_status_badges($status, $live) . '</div>';
    cms_stepper($status, $live);
    if (!empty($row['review_note'])) {
        echo '<div class="cms-alert cms-alert--pending"><strong>Last review note:</strong> ' . e($row['review_note']) . '</div>';
    }
    if (!$available) {
        $why = [];
        foreach (['submit', 'pass_editorial', 'pass_seo', 'approve', 'publish'] as $a) {
            $def = cms_actions()[$a];
            if (in_array($status, $def['from'], true)) {
                $why[] = cms_action_error($user, $type, $row, $a);
            }
        }
        echo '<p class="text-muted cms-small">No workflow action is available to you at this stage.'
            . ($why ? ' ' . e(implode(' ', array_unique(array_filter($why)))) : '') . '</p>';
    }
    foreach ($available as $action) {
        $def = cms_actions()[$action];
        $danger = in_array($action, ['reject', 'unpublish'], true);
        echo '<form method="post" action="' . e($postUrl) . '" class="cms-action' . ($danger ? ' cms-action--danger' : '') . '">';
        echo csrf_field();
        echo '<input type="hidden" name="op" value="transition"><input type="hidden" name="wf" value="' . e($action) . '">';
        echo '<input type="hidden" name="lock" value="' . (int) $row['lock_version'] . '">';
        if (!empty($def['reason'])) {
            echo '<label class="cms-small" for="reason-' . e($action) . '">Reason (required, saved to the history)</label>';
            echo '<textarea id="reason-' . e($action) . '" name="reason" rows="2" maxlength="500" required></textarea>';
        }
        if ($action === 'publish') {
            echo '<label class="cms-check"><input type="checkbox" name="confirm" value="1" required> I confirm this approved version should go live now'
                . ($type === 'article' ? ' (indexing: ' . ((int) ($row['indexable'] ?? 0) === 1 ? 'indexable' . ((int) $row['in_sitemap'] === 1 ? ' + sitemap' : '') : 'noindex') . ')' : '') . '.</label>';
        } elseif ($action === 'unpublish') {
            echo '<label class="cms-check"><input type="checkbox" name="confirm" value="1" required> I confirm this should be taken off the public site now.</label>';
        }
        echo '<button type="submit" class="btn btn-sm ' . ($danger ? 'btn-outline cms-btn-danger' : ($action === 'publish' ? 'btn-primary' : 'btn-outline')) . '">' . e($def['label']) . '</button>';
        echo '</form>';
    }
    echo '</div>';
}

function cms_history_panel(PDO $pdo, string $type, int $id): void
{
    $rows = cms_history($pdo, $type, $id);
    echo '<div class="panel"><div class="panel-head"><h2>Approval history</h2><span class="text-muted cms-small">From audit logs</span></div>';
    if (!$rows) {
        echo '<p class="text-muted cms-small">No history yet.</p></div>';
        return;
    }
    echo '<ol class="cms-history">';
    foreach ($rows as $h) {
        $m = json_decode((string) $h['meta_json'], true) ?: [];
        echo '<li><div><strong>' . e(cms_history_label($h['action'])) . '</strong>';
        if (!empty($m['from']) && !empty($m['to']) && $m['from'] !== $m['to']) {
            echo ' <span class="text-muted">' . e((cms_statuses()[$m['from']] ?? $m['from']) . ' → ' . (cms_statuses()[$m['to']] ?? $m['to'])) . '</span>';
        }
        echo '</div><div class="cms-small text-muted">' . e($h['full_name'] ?? 'System') . ' · ' . e($h['created_at']) . '</div>';
        foreach (['reason' => 'Reason', 'note' => 'Note'] as $k => $label) {
            if (!empty($m[$k])) {
                echo '<div class="cms-small">' . e($label) . ': ' . e((string) $m[$k]) . '</div>';
            }
        }
        echo '</li>';
    }
    echo '</ol></div>';
}

/** Run a workflow transition from a POST and redirect back with a message. */
function cms_handle_transition(PDO $pdo, array $user, string $type, int $id, string $back, ?callable $snapshot): never
{
    $action = (string) ($_POST['wf'] ?? '');
    try {
        $row = cms_load($pdo, $type, $id);
        if ($row === null) {
            throw new RuntimeException('Item not found.');
        }
        if ((int) ($_POST['lock'] ?? -1) !== (int) $row['lock_version']) {
            throw new RuntimeException('This item changed since you opened it. Review the latest version and try again.');
        }
        $to = cms_transition($pdo, $user, $type, $id, $action, (string) ($_POST['reason'] ?? ''), !empty($_POST['confirm']), $snapshot);
        $msg = match ($action) {
            'publish' => 'Published. The live site now shows this version.',
            'unpublish' => 'Unpublished. It is no longer on the public site.',
            default => 'Moved to ' . (cms_statuses()[$to] ?? $to) . '.',
        };
        cms_redirect($back, $msg);
    } catch (CmsDenied | RuntimeException $e) {
        cms_redirect($back, null, $e->getMessage());
    }
}
