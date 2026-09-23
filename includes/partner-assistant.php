<?php
/**
 * Partner Assistant — a scoped, rules-based Q&A helper for the Partner
 * Hub. This is NOT a general-purpose LLM chatbot: it matches a query
 * against a small set of known intents and answers strictly from the
 * logged-in partner's own data, queried with partner_id = $context's
 * partner_id (never from user input). Every branch below is a plain
 * SQL lookup, not a language model — labeled as such in the UI so
 * partners aren't misled about what it can do.
 *
 * Extension point: to back this with a real LLM later, keep this
 * function's job as "fetch the partner-scoped facts", pass its
 * returned data as grounding context into the model call, and never
 * let the model itself construct or alter the SQL/partner_id scoping.
 */
function partner_assistant_answer(PDO $pdo, array $context, string $query): array
{
    $partnerId = $context['partner_id'];
    $q = strtolower(trim($query));

    if ($q === '' || in_array($q, ['help', 'hi', 'hello', 'what can you do'], true)) {
        return ['answer' => "I'm a rules-based assistant scoped to your own partner account — I can't see other partners' data. Ask me about: customers/pipeline, a specific customer by name or application code, commissions, settlements, transactions, proposals, support tickets, or your onboarding status."];
    }

    // ---- Specific customer lookup (by application code or name match) ----
    if (preg_match('/PYN-CUST-\d{4}-\d{6}/i', $query, $m) || strpos($q, 'customer ') !== false || strpos($q, 'application') !== false) {
        $needle = $m[0] ?? trim(str_ireplace(['customer', 'application', 'status of', 'status'], '', $query));
        if ($needle !== '') {
            $stmt = $pdo->prepare(
                'SELECT application_code, business_name, pipeline_stage FROM customer_applications
                 WHERE partner_id = :pid AND (business_name LIKE :q OR application_code LIKE :q) LIMIT 1'
            );
            $stmt->execute(['pid' => $partnerId, 'q' => '%' . $needle . '%']);
            $row = $stmt->fetch();
            if ($row) {
                $stage = ucwords(str_replace('_', ' ', $row['pipeline_stage']));
                return ['answer' => "{$row['business_name']} ({$row['application_code']}) is currently at pipeline stage: {$stage}."];
            }
            return ['answer' => "I couldn't find a customer matching \"{$needle}\" in your account."];
        }
    }

    // ---- Pipeline / customers overview ----
    if (preg_match('/\b(customer|lead|pipeline|application)s?\b/', $q)) {
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) AS total, COALESCE(SUM(pipeline_stage = 'active'),0) AS active_cnt,
             COALESCE(SUM(pipeline_stage NOT IN ('active','lost','rejected')),0) AS in_progress
             FROM customer_applications WHERE partner_id = :pid"
        );
        $stmt->execute(['pid' => $partnerId]);
        $row = $stmt->fetch();
        return ['answer' => "You have {$row['total']} customer application(s) total — {$row['active_cnt']} active, {$row['in_progress']} still in progress. Open the Customers page for the full pipeline."];
    }

    // ---- Commission ----
    if (preg_match('/\b(commission|earning|payout rate)s?\b/', $q)) {
        $stmt = $pdo->prepare(
            "SELECT COALESCE(SUM(CASE WHEN status IN ('accrued','paid') THEN amount ELSE 0 END),0) AS earned,
             COALESCE(SUM(CASE WHEN status = 'accrued' THEN amount ELSE 0 END),0) AS pending
             FROM commissions WHERE partner_id = :pid"
        );
        $stmt->execute(['pid' => $partnerId]);
        $row = $stmt->fetch();
        return ['answer' => 'Your lifetime commission earned is ' . format_amount((float) $row['earned']) . ', with ' . format_amount((float) $row['pending']) . ' currently pending payout. See the Commissions page for the full breakdown and active rules.'];
    }

    // ---- Settlements ----
    if (preg_match('/\b(settlement|settled)s?\b/', $q)) {
        $stmt = $pdo->prepare(
            "SELECT COALESCE(SUM(CASE WHEN status = 'settled' THEN net_amount ELSE 0 END),0) AS settled,
             COALESCE(SUM(CASE WHEN status IN ('pending','processing') THEN net_amount ELSE 0 END),0) AS pending
             FROM settlements WHERE partner_id = :pid"
        );
        $stmt->execute(['pid' => $partnerId]);
        $row = $stmt->fetch();
        return ['answer' => format_amount((float) $row['settled']) . ' has been settled to you so far, with ' . format_amount((float) $row['pending']) . ' pending. Check the Settlements page for period-by-period detail.'];
    }

    // ---- Transactions / volume ----
    if (preg_match('/\b(transaction|volume|sales)s?\b/', $q)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) AS cnt, COALESCE(SUM(amount),0) AS total FROM transactions WHERE partner_id = :pid AND status = 'success'");
        $stmt->execute(['pid' => $partnerId]);
        $row = $stmt->fetch();
        return ['answer' => "You've processed {$row['cnt']} successful transaction(s) totaling " . format_amount((float) $row['total']) . '. See the Transactions page for the full trend.'];
    }

    // ---- Proposals ----
    if (strpos($q, 'proposal') !== false) {
        $stmt = $pdo->prepare("SELECT status, COUNT(*) AS cnt FROM proposals WHERE partner_id = :pid GROUP BY status");
        $stmt->execute(['pid' => $partnerId]);
        $rows = $stmt->fetchAll();
        if (empty($rows)) {
            return ['answer' => "You haven't created any proposals yet. Head to the Proposals page to build your first one."];
        }
        $parts = array_map(static fn ($r) => "{$r['cnt']} {$r['status']}", $rows);
        return ['answer' => 'Your proposals: ' . implode(', ', $parts) . '.'];
    }

    // ---- Support tickets ----
    if (preg_match('/\b(ticket|support)s?\b/', $q)) {
        $user = current_user();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM support_tickets WHERE user_id = :uid AND status != 'closed'");
        $stmt->execute(['uid' => $user['id'] ?? 0]);
        $count = (int) $stmt->fetchColumn();
        return ['answer' => "You have {$count} open support ticket(s). Visit the Support page to view or reply."];
    }

    // ---- Onboarding / account status ----
    if (preg_match('/\b(onboard|kyc|verif|approv|account status)\w*/', $q)) {
        $stmt = $pdo->prepare('SELECT status, kyc_status FROM partners WHERE id = :pid');
        $stmt->execute(['pid' => $partnerId]);
        $row = $stmt->fetch();
        if ($row) {
            return ['answer' => "Your partner account status is \"{$row['status']}\" with KYC status \"{$row['kyc_status']}\". See the Onboarding Status page for the full checklist."];
        }
    }

    // ---- Last resort: try the raw query as a customer name lookup ----
    $stopwords = ['tell', 'me', 'about', 'show', 'find', 'what', 'is', 'the', 'status', 'of', 'for', 'a', 'an', 'my', 'customer', 'application'];
    $needle = trim(preg_replace('/\s+/', ' ', str_ireplace($stopwords, '', $query)) ?? '');
    if (mb_strlen($needle) >= 3) {
        $stmt = $pdo->prepare(
            'SELECT application_code, business_name, pipeline_stage FROM customer_applications
             WHERE partner_id = :pid AND business_name LIKE :q LIMIT 1'
        );
        $stmt->execute(['pid' => $partnerId, 'q' => '%' . $needle . '%']);
        $row = $stmt->fetch();
        if ($row) {
            $stage = ucwords(str_replace('_', ' ', $row['pipeline_stage']));
            return ['answer' => "{$row['business_name']} ({$row['application_code']}) is currently at pipeline stage: {$stage}."];
        }
    }

    return ['answer' => "I'm not able to answer that yet — I can help with customers/pipeline, a specific customer lookup, commissions, settlements, transactions, proposals, support tickets, or your onboarding status."];
}
