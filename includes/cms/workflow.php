<?php
/**
 * CMS editorial workflow — shared by blog articles (blog_posts) and CMS
 * pages (cms_pages: homepage hero, per-page SEO).
 *
 *   DRAFT → EDITORIAL REVIEW → SEO/AEO REVIEW → BUSINESS/LEGAL REVIEW
 *         → APPROVED → PUBLISHED
 *
 * - Every transition is checked server-side against a cms.* permission and
 *   written to audit_logs (the approval history).
 * - Any review stage can reject back to DRAFT; a reason is mandatory.
 * - Only APPROVED content can be published, and only with an explicit
 *   confirmation. Publishing freezes a snapshot (published_json): that
 *   snapshot is what the public site shows.
 * - Editing content after approval or publication returns the working copy
 *   to DRAFT and clears the approval; the published snapshot stays live
 *   until a new version is approved and published.
 * - Outside super_admin, nobody passes, approves or publishes their own
 *   submission (separation of duties).
 */

declare(strict_types=1);

require_once __DIR__ . '/../permissions.php';

function cms_statuses(): array
{
    return [
        'draft'                 => 'Draft',
        'editorial_review'      => 'Editorial review',
        'seo_review'            => 'SEO / AEO review',
        'business_legal_review' => 'Business / legal review',
        'approved'              => 'Approved',
        'published'             => 'Published',
    ];
}

function cms_status_badge(string $status): string
{
    return match ($status) {
        'published' => 'success', 'approved' => 'info', 'draft' => 'neutral', default => 'pending',
    };
}

/** Workflow actions: from-statuses, target, permission, and whether a reason is required. */
function cms_actions(): array
{
    return [
        'submit'        => ['label' => 'Submit for editorial review', 'from' => ['draft'], 'to' => 'editorial_review', 'perm' => 'cms.submit'],
        'pass_editorial' => ['label' => 'Pass editorial review', 'from' => ['editorial_review'], 'to' => 'seo_review', 'perm' => 'cms.review.editorial', 'review' => true],
        'pass_seo'      => ['label' => 'Pass SEO / AEO review', 'from' => ['seo_review'], 'to' => 'business_legal_review', 'perm' => 'cms.review.seo', 'review' => true],
        'approve'       => ['label' => 'Approve (business / legal)', 'from' => ['business_legal_review'], 'to' => 'approved', 'perm' => 'cms.approve', 'review' => true],
        'reject'        => ['label' => 'Reject to draft', 'from' => ['editorial_review', 'seo_review', 'business_legal_review', 'approved'], 'to' => 'draft', 'perm' => null, 'reason' => true],
        'withdraw'      => ['label' => 'Withdraw to draft', 'from' => ['editorial_review', 'seo_review', 'business_legal_review'], 'to' => 'draft', 'perm' => 'cms.edit'],
        'publish'       => ['label' => 'Publish', 'from' => ['approved'], 'to' => 'published', 'perm' => 'cms.publish', 'review' => true, 'confirm' => true],
        'unpublish'     => ['label' => 'Unpublish', 'from' => null, 'to' => 'draft', 'perm' => 'cms.unpublish', 'reason' => true, 'confirm' => true],
    ];
}

/** Permission needed to reject from a stage: the reviewer of that stage. */
function cms_reject_permission(string $status): string
{
    return match ($status) {
        'editorial_review' => 'cms.review.editorial',
        'seo_review'       => 'cms.review.seo',
        default            => 'cms.approve',
    };
}

/** Storage mapping per content type. */
function cms_entity(string $type): array
{
    return match ($type) {
        'article' => ['table' => 'blog_posts', 'status' => 'status', 'audit' => 'blog_post'],
        'page'    => ['table' => 'cms_pages', 'status' => 'workflow_status', 'audit' => 'cms_page'],
        default   => throw new InvalidArgumentException('Unknown CMS type'),
    };
}

function cms_audit(PDO $pdo, ?int $userId, string $action, string $entityType, ?int $entityId, array $meta = []): void
{
    $pdo->prepare(
        'INSERT INTO audit_logs (user_id, action, entity_type, entity_id, ip_address, meta_json)
         VALUES (:uid, :action, :type, :eid, :ip, :meta)'
    )->execute([
        'uid' => $userId, 'action' => $action, 'type' => $entityType, 'eid' => $entityId,
        'ip' => PHP_SAPI === 'cli' ? 'cli' : client_ip(),
        'meta' => json_encode($meta, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
    ]);
}

function cms_load(PDO $pdo, string $type, int $id): ?array
{
    $ent = cms_entity($type);
    $stmt = $pdo->prepare('SELECT * FROM ' . $ent['table'] . ' WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

/** Actions this user may take on this item right now (for the UI; transitions re-check). */
function cms_available_actions(array $user, string $type, array $row): array
{
    $out = [];
    foreach (array_keys(cms_actions()) as $action) {
        if (cms_action_error($user, $type, $row, $action) === null) {
            $out[] = $action;
        }
    }
    return $out;
}

/** Why the action is not allowed, or null when it is. */
function cms_action_error(array $user, string $type, array $row, string $action): ?string
{
    $def = cms_actions()[$action] ?? null;
    if ($def === null) {
        return 'Unknown action.';
    }
    $status = (string) ($row[cms_entity($type)['status']] ?? 'draft');
    if ($action === 'unpublish') {
        if ((int) $row['live'] !== 1) {
            return 'This item is not live.';
        }
    } elseif (!in_array($status, $def['from'], true)) {
        return 'Not allowed from "' . (cms_statuses()[$status] ?? $status) . '".';
    }
    $perm = $action === 'reject' ? cms_reject_permission($status) : $def['perm'];
    if (!user_can($user, $perm)) {
        return 'You do not have permission to do this (' . $perm . ').';
    }
    if (!empty($def['review']) && ($user['role'] ?? '') !== 'super_admin'
        && (int) ($row['submitted_by'] ?? 0) === (int) $user['id']) {
        return 'You cannot review, approve or publish your own submission.';
    }
    return null;
}

/**
 * Apply a workflow action. $snapshot builds the public snapshot on publish.
 * Returns the new status. Throws CmsDenied / RuntimeException with a message
 * suitable for the editor.
 */
function cms_transition(PDO $pdo, array $user, string $type, int $id, string $action, string $reason = '',
    bool $confirmed = false, ?callable $snapshot = null): string
{
    $ent = cms_entity($type);
    $def = cms_actions()[$action] ?? null;
    $row = cms_load($pdo, $type, $id);
    if ($row === null || $def === null) {
        throw new RuntimeException('Item or action not found.');
    }
    $error = cms_action_error($user, $type, $row, $action);
    if ($error !== null) {
        throw new CmsDenied($error);
    }
    $reason = cms_text($reason, 500);
    if (!empty($def['reason']) && $reason === '') {
        throw new RuntimeException('A reason is required.');
    }
    if (!empty($def['confirm']) && !$confirmed) {
        throw new RuntimeException('Please tick the confirmation box first.');
    }

    $from = (string) ($row[$ent['status']] ?? 'draft');
    $to = $def['to'];
    $uid = (int) $user['id'];
    $set = [$ent['status'] . ' = :to', 'lock_version = lock_version + 1'];
    $params = ['to' => $to, 'id' => $id];

    switch ($action) {
        case 'submit':
            $set[] = 'submitted_by = :uid';
            $set[] = 'submitted_at = NOW()';
            $set[] = 'review_note = NULL';
            $params['uid'] = $uid;
            break;
        case 'approve':
            $set[] = 'approved_by = :uid';
            $set[] = 'approved_at = NOW()';
            $params['uid'] = $uid;
            break;
        case 'reject':
        case 'withdraw':
            $set[] = 'approved_by = NULL';
            $set[] = 'approved_at = NULL';
            $set[] = 'review_note = :note';
            $params['note'] = $reason !== '' ? $reason : null;
            break;
        case 'publish':
            if ($snapshot === null) {
                throw new RuntimeException('Nothing to publish.');
            }
            $snap = $snapshot($row, $user);
            $set[] = 'published_json = :snap';
            $set[] = 'live = 1';
            $set[] = 'published_at = NOW()';
            $set[] = 'published_by = :uid';
            $params['snap'] = json_encode($snap, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $params['uid'] = $uid;
            break;
        case 'unpublish':
            $set[] = 'live = 0';
            $set[] = 'approved_by = NULL';
            $set[] = 'approved_at = NULL';
            $set[] = 'review_note = :note';
            $params['note'] = $reason;
            break;
    }

    $pdo->beginTransaction();
    try {
        // Guarded update: fails if someone else moved the item meanwhile.
        $sql = 'UPDATE ' . $ent['table'] . ' SET ' . implode(', ', $set) . ' WHERE id = :id AND lock_version = :lock';
        $params['lock'] = (int) $row['lock_version'];
        $upd = $pdo->prepare($sql);
        $upd->execute($params);
        if ($upd->rowCount() !== 1) {
            throw new RuntimeException('This item was changed by someone else. Reload and try again.');
        }
        cms_audit($pdo, $uid, 'cms.' . $action, $ent['audit'], $id, array_filter([
            'from' => $from, 'to' => $to, 'reason' => $reason ?: null,
            'title' => $row['title'] ?? $row['page_key'] ?? null,
        ]));
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
    return $to;
}

/** Approval history for one item (newest first). */
function cms_history(PDO $pdo, string $type, int $id, int $limit = 100): array
{
    $stmt = $pdo->prepare(
        'SELECT al.action, al.meta_json, al.created_at, al.ip_address, u.full_name
         FROM audit_logs al LEFT JOIN users u ON u.id = al.user_id
         WHERE al.entity_type = :t AND al.entity_id = :id AND al.action LIKE \'cms.%\'
         ORDER BY al.id DESC LIMIT ' . max(1, min(500, $limit))
    );
    $stmt->execute(['t' => cms_entity($type)['audit'], 'id' => $id]);
    return $stmt->fetchAll();
}

function cms_history_label(string $action): string
{
    $key = substr($action, 4);
    return match ($key) {
        'create' => 'Created', 'save' => 'Edited', 'import' => 'Imported from file',
        default => cms_actions()[$key]['label'] ?? $action,
    };
}

function cms_user_name(PDO $pdo, ?int $id): ?string
{
    if (!$id) {
        return null;
    }
    $stmt = $pdo->prepare('SELECT full_name FROM users WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $name = $stmt->fetchColumn();
    return $name === false ? null : (string) $name;
}
