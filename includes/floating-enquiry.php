<?php
/**
 * FloatingEnquiryWidget — persistent, secondary sales-contact channel for
 * the public marketing site (WhatsApp · Email · Call Sales).
 *
 * Rendered once from includes/footer.php. The wrapper ships `hidden` and the
 * script reveals it, so without JavaScript nothing inert is shown (the
 * contact page remains the no-JS route). Styles: assets/css/floating-enquiry.css.
 * Behaviour: assets/js/floating-enquiry.js (deferred, no dependencies).
 * Wording comes from the shared page-context CTA system (includes/cta-context.php).
 *
 * Contact details live ONLY in fe_channels(). Defaults are Paynancial's
 * existing published details (header WhatsApp link, MAIL_SALES_TO, contact
 * page phone). Override per install in config/config.php:
 *   define('PAYNANCIAL_SALES_WHATSAPP', '917066820820');   // digits, country code first
 *   define('PAYNANCIAL_SALES_EMAIL',    'hello@paynancial.com');
 *   define('PAYNANCIAL_SALES_PHONE',    '+916122999382');  // E.164
 */

declare(strict_types=1);

require_once __DIR__ . '/cta-context.php';

/** Contact channels — the single source of truth for the widget. */
function fe_channels(): array
{
    $whatsapp = defined('PAYNANCIAL_SALES_WHATSAPP') ? PAYNANCIAL_SALES_WHATSAPP : '917066820820';
    $email    = defined('PAYNANCIAL_SALES_EMAIL') ? PAYNANCIAL_SALES_EMAIL : (defined('MAIL_SALES_TO') ? MAIL_SALES_TO : 'hello@paynancial.com');
    $phone    = defined('PAYNANCIAL_SALES_PHONE') ? PAYNANCIAL_SALES_PHONE : '+916122999382';

    return [
        'whatsapp' => ['number' => preg_replace('/\D+/', '', (string) $whatsapp)],
        'email'    => ['address' => (string) $email],
        'phone'    => ['e164' => '+' . preg_replace('/\D+/', '', (string) $phone)],
    ];
}

/** Readable Indian formatting: "+91 612 2999 382" (Patna landline), "+91 7066 820 820" (mobile). */
function fe_format_phone(string $e164): string
{
    $d = preg_replace('/\D+/', '', $e164);
    if (strlen($d) !== 12 || !str_starts_with($d, '91')) {
        return '+' . $d;
    }
    $n = substr($d, 2);
    return str_starts_with($n, '612')
        ? '+91 612 ' . substr($n, 3, 4) . ' ' . substr($n, 7)
        : '+91 ' . substr($n, 0, 4) . ' ' . substr($n, 4, 3) . ' ' . substr($n, 7);
}

/**
 * Pages where the widget stays out of the way: the contact page already
 * lists every channel; auth and payment-link pages are focused tasks.
 */
function fe_is_excluded(string $path): bool
{
    $path = '/' . trim($path, '/');
    foreach (['/contact', '/signup', '/forgot-password', '/reset-password', '/pay'] as $p) {
        if ($path === $p || str_starts_with($path, $p . '/')) {
            return true;
        }
    }
    return false;
}

/** Echo the widget for the current request (no-op on excluded pages). */
function fe_render(string $requestUri): void
{
    $path = (string) (parse_url($requestUri, PHP_URL_PATH) ?? '/');
    if (fe_is_excluded($path)) {
        return;
    }
    $ctx = cta_context($path);
    $ch = fe_channels();

    $waHref    = 'https://wa.me/' . $ch['whatsapp']['number'] . '?text=' . rawurlencode($ctx['message']);
    $mailHref  = 'mailto:' . $ch['email']['address'] . '?subject=' . rawurlencode($ctx['subject']);
    $telHref   = 'tel:' . $ch['phone']['e164'];
    $phoneText = fe_format_phone($ch['phone']['e164']);

    $icon = fn (string $d, string $extra = '') => '<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false"' . $extra . '>' . $d . '</svg>';
    $arrow = $icon('<path d="M5 12h14M13 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>');
    $waIcon = $icon('<path fill="currentColor" d="M19.05 4.91A9.82 9.82 0 0 0 12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38a9.9 9.9 0 0 0 4.74 1.21h.01c5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.91-7.01Zm-7.01 15.24h-.01a8.23 8.23 0 0 1-4.19-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.2 8.2 0 0 1-1.26-4.38c0-4.54 3.7-8.23 8.24-8.23 2.2 0 4.27.86 5.82 2.42a8.18 8.18 0 0 1 2.41 5.82c0 4.54-3.7 8.23-8.22 8.23Zm4.52-6.16c-.25-.12-1.47-.72-1.69-.81-.23-.08-.39-.12-.56.13-.17.24-.64.81-.78.97-.14.17-.29.19-.54.06-.25-.12-1.05-.39-1.99-1.23-.74-.66-1.23-1.47-1.38-1.72-.14-.25-.02-.38.11-.51.11-.11.25-.29.37-.43.13-.15.17-.25.25-.41.08-.17.04-.31-.02-.43-.06-.12-.56-1.34-.76-1.84-.2-.48-.41-.42-.56-.42h-.48c-.17 0-.43.06-.66.31-.22.25-.87.85-.87 2.07 0 1.22.89 2.4 1.01 2.56.12.17 1.75 2.67 4.23 3.74.59.26 1.05.41 1.41.52.59.19 1.13.16 1.56.1.48-.07 1.47-.6 1.67-1.18.21-.58.21-1.07.14-1.18-.06-.1-.22-.16-.47-.28Z"/>');
    $mailIcon = $icon('<rect x="3" y="5" width="18" height="14" rx="2.5" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="m4 7 8 6 8-6" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>');
    $phoneIcon = $icon('<path d="M20.5 16.6v2.6a1.8 1.8 0 0 1-2 1.8 17.7 17.7 0 0 1-7.7-2.7 17.4 17.4 0 0 1-5.4-5.4A17.7 17.7 0 0 1 2.7 5.2a1.8 1.8 0 0 1 1.8-2h2.6a1.8 1.8 0 0 1 1.8 1.5c.1.8.3 1.7.6 2.5a1.8 1.8 0 0 1-.4 1.9L8 10.2a14.4 14.4 0 0 0 5.4 5.4l1.1-1.1a1.8 1.8 0 0 1 1.9-.4c.8.3 1.7.5 2.5.6a1.8 1.8 0 0 1 1.6 1.9Z" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>');
    ?>
<div class="fe" data-fe hidden data-fe-context="<?= e($ctx['key']) ?>" data-fe-cta="<?= e($ctx['label']) ?>">
  <button type="button" class="fe-trigger" data-fe-trigger aria-expanded="false" aria-controls="fe-panel" aria-haspopup="dialog" aria-label="<?= e($ctx['label']) ?>: contact options" data-fe-label="<?= e($ctx['label']) ?>: contact options">
    <span class="fe-dot" aria-hidden="true"></span>
    <span class="fe-trigger-label" aria-hidden="true"><span class="fe-label-full"><?= e($ctx['label']) ?></span><span class="fe-label-short">Enquire</span></span>
    <span class="fe-trigger-icon" aria-hidden="true">
      <svg viewBox="0 0 24 24" width="14" height="14" focusable="false"><path class="fe-chev" d="m7 14 5-5 5 5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </span>
  </button>

  <div class="fe-backdrop" data-fe-backdrop></div>

  <div class="fe-panel" id="fe-panel" role="dialog" aria-modal="false" aria-labelledby="fe-title" aria-describedby="fe-desc" data-fe-panel>
    <span class="fe-grabber" aria-hidden="true"></span>
    <div class="fe-head">
      <span class="fe-eyebrow"><?= e($ctx['area']) ?></span>
      <h2 class="fe-title" id="fe-title"><?= e($ctx['label']) ?></h2>
      <p class="fe-desc" id="fe-desc"><?= e($ctx['support']) ?></p>
      <button type="button" class="fe-x" data-fe-close aria-label="Close contact options">
        <svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false"><path d="M6 6l12 12M18 6 6 18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
      </button>
    </div>

    <ul class="fe-list">
      <li>
        <a class="fe-action" href="<?= e($waHref) ?>" target="_blank" rel="noopener noreferrer" data-fe-action="whatsapp" aria-label="WhatsApp: chat with Sales (opens WhatsApp)">
          <span class="fe-icon fe-icon-wa"><?= $waIcon ?></span>
          <span class="fe-text"><strong>WhatsApp</strong><span>Chat with Sales</span><em><?= e(fe_format_phone($ch['whatsapp']['number'])) ?></em></span>
          <span class="fe-go"><?= $arrow ?></span>
        </a>
      </li>
      <li>
        <a class="fe-action" href="<?= e($mailHref) ?>" data-fe-action="email" aria-label="Email our Sales team at <?= e($ch['email']['address']) ?>">
          <span class="fe-icon"><?= $mailIcon ?></span>
          <span class="fe-text"><strong>Email</strong><span>Email our Sales Team</span><em><?= e($ch['email']['address']) ?></em></span>
          <span class="fe-go"><?= $arrow ?></span>
        </a>
      </li>
      <li>
        <a class="fe-action" href="<?= e($telHref) ?>" data-fe-action="call" aria-label="Call Sales on <?= e($phoneText) ?>">
          <span class="fe-icon"><?= $phoneIcon ?></span>
          <span class="fe-text"><strong>Call Sales</strong><span>Speak with a Sales Expert</span><em><?= e($phoneText) ?></em></span>
          <span class="fe-go"><?= $arrow ?></span>
        </a>
      </li>
    </ul>

    <div class="fe-foot">
      <a class="fe-form-link" href="/contact?intent=sales" data-fe-action="form">Prefer a form? Send a detailed enquiry <?= $arrow ?></a>
      <button type="button" class="fe-close-btn" data-fe-close>Close</button>
    </div>
  </div>
</div>
<script src="<?= e(asset('js/floating-enquiry.js')) ?>" defer></script>
    <?php
}
