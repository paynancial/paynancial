<?php
/**
 * Public payment link viewer: /pay/{ref}
 *
 * This renders the link's details honestly. Paynancial's live card/UPI
 * acceptance is processed through the merchant's configured gateway
 * credentials, which are not wired up in this codebase — so rather than
 * fabricate a fake "payment successful" flow, this page shows the link
 * details and directs the payer to the merchant for now. Wire this up
 * to a real gateway SDK before accepting live traffic.
 */
$linkRef = sanitize_input((string) ($pay_ref ?? ''));

$pdo = db();
$stmt = $pdo->prepare(
    'SELECT pl.*, p.business_name FROM payment_links pl
     LEFT JOIN partners p ON p.id = pl.partner_id
     WHERE pl.link_ref = :ref'
);
$stmt->execute(['ref' => $linkRef]);
$link = $stmt->fetch();

$page_meta = ['title' => 'Payment Link | Paynancial'];

$isExpired = $link && $link['expires_at'] && strtotime((string) $link['expires_at']) < time();
?>
<section style="min-height:60vh;display:flex;align-items:center;border-bottom:none;">
  <div class="container" style="max-width:520px;">
    <?php if (!$link): ?>
      <div class="panel" style="border:1px solid var(--border);padding:32px;">
        <h2>Payment Link Not Found</h2>
        <p class="text-muted" style="margin-top:10px;">This payment link doesn't exist or may have been removed.</p>
      </div>
    <?php elseif ($link['status'] === 'disabled' || $link['status'] === 'expired' || $isExpired): ?>
      <div class="panel" style="border:1px solid var(--border);padding:32px;">
        <h2>Link No Longer Active</h2>
        <p class="text-muted" style="margin-top:10px;">This payment link has expired or been disabled by the merchant. Please contact <?= e($link['business_name'] ?? 'the merchant') ?> for alternate payment instructions.</p>
      </div>
    <?php elseif ($link['status'] === 'paid'): ?>
      <div class="panel" style="border:1px solid var(--border);padding:32px;">
        <h2>Already Paid</h2>
        <p class="text-muted" style="margin-top:10px;">This payment link has already been paid.</p>
      </div>
    <?php else: ?>
      <div class="panel" style="border:1px solid var(--border);padding:32px;">
        <span class="eyebrow">Payment Request</span>
        <h2 style="margin-top:8px;"><?= e($link['title']) ?></h2>
        <p class="text-muted" style="margin-top:6px;">From <?= e($link['business_name'] ?? 'Paynancial Merchant') ?></p>
        <div class="ledger" style="margin-top:20px;">
          <div class="ledger-row">
            <span class="ledger-tag">Amount</span>
            <h3 style="font-size:1.3rem;"><?= $link['amount'] !== null ? e(format_amount((float) $link['amount'], $link['currency'])) : 'Enter amount at payment' ?></h3>
            <span></span>
          </div>
          <div class="ledger-row">
            <span class="ledger-tag">Reference</span>
            <h3 class="mono" style="font-size:0.95rem;"><?= e($link['link_ref']) ?></h3>
            <span></span>
          </div>
        </div>
        <div style="margin-top:24px;padding:14px;border:1px dashed var(--border);font-size:0.85rem;color:var(--text-muted);">
          Online card/UPI collection for this link isn't active yet — this merchant's payment gateway is still being configured. Please contact <?= e($link['business_name'] ?? 'the merchant') ?> directly to complete this payment for now.
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>
