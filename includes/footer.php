<?php
/**
 * Site footer — brand strip, then ONE navigation row of five parallel
 * columns on desktop (>=1200px): Products & Solutions · Agentic AI ·
 * Developers · Resources & Trust · Company. Tablet (768–1199px) groups
 * them 3 + 2; mobile (<=767px) uses accordions. Structurally parallel:
 * every column is heading → divider → labelled subgroups → CTA, with the
 * CTAs sharing one baseline on desktop. Every link points at
 * an existing page or anchor; groups render from $footer_groups so a link
 * is defined exactly once. Descriptive text is justified. On narrow
 * screens each group collapses into an accordion (main.js; panels stay
 * open without JavaScript).
 * Business Services is intentionally not linked here yet.
 */
$footer_groups = [
    'products' => [
        'title' => 'Products & Solutions',
        'blocks' => [
            ['Payments', [
                ['Payment Gateway', '/products/payment-gateway'],
                ['Payment Links', '/products/payment-links'],
                ['Payment Collection', '/products/payment-collection'],
                ['Pricing', '/pricing'],
            ]],
            ['Solutions', [
                ['E-Commerce', '/solutions#ecommerce'],
                ['Travel', '/solutions#travel'],
                ['Healthcare', '/solutions#healthcare'],
                ['Education', '/solutions#education'],
                ['Retail', '/solutions#retail'],
                ['Hospitality', '/solutions#hospitality'],
                ['Enterprise', '/solutions#enterprise'],
            ]],
        ],
        'cta'   => ['Explore Products', '/products'],
    ],
    'agentic' => [
        'title' => 'Agentic AI',
        'badge' => 'New',
        'blocks' => [
            ['AI Finance', [
                ['Agentic Payments', '/agentic-ai'],
                ['AI Financial Agents', '/agentic-ai#financial-agents'],
                ['AI Orchestration', '/agentic-ai#payment-orchestration'],
                ['AI Governance', '/agentic-ai#governance'],
            ]],
        ],
        'cta' => ['Explore Agentic AI', '/agentic-ai'],
    ],
    'developers' => [
        'title' => 'Developers',
        'blocks' => [
            ['Build', [
                ['Documentation', '/developers#docs'],
                ['API Reference', '/developers#api-reference'],
                ['SDKs', '/developers#sdks'],
            ]],
            ['Connect', [
                ['Webhooks', '/developers#webhooks'],
                ['Authentication', '/developers#authentication'],
                ['Sandbox', '/developers#sandbox'],
                ['Integration Guide', '/developers#integration-guide'],
            ]],
            ['Support', [
                ['Developer Support', '/support'],
            ]],
        ],
        'cta' => ['Developer Center', '/developers'],
    ],
    'trust' => [
        'title' => 'Resources & Trust',
        'blocks' => [
            ['Resources', [
                ['Blog / Insights', '/blog'],
                ['FAQs', '/support#faqs'],
                ['Support Center', '/support'],
            ]],
            ['Trust', [
                ['Security & Compliance', '/security'],
                ['Trust Center', '/trust'],
                ['AI Governance', '/trust#ai-governance'],
                ['Privacy Policy', '/legal/privacy-policy'],
                ['Terms & Conditions', '/legal/terms-conditions'],
                ['Refund Policy', '/legal/refund-policy'],
            ], 'is-trust'],
        ],
        'cta' => ['Visit Trust Center', '/trust'],
    ],
    'company' => [
        'title' => 'Company',
        'blocks' => [
            ['About Us', [
                ['About Paynancial', '/about'],
                ['Our Journey', '/about#journey'],
                ['Leadership', '/leadership'],
            ]],
            ['Work With Us', [
                ['Careers', '/careers'],
                ['Partner Program', '/partners'],
                ['Contact', '/contact'],
            ]],
        ],
        'cta' => ['About Paynancial', '/about'],
    ],
];
?>
<footer class="site-footer ft" aria-labelledby="ft-heading">
  <h2 id="ft-heading" class="sr-only">Paynancial</h2>

  <div class="ft-brand">
    <div class="container ft-brand-grid">
      <div class="ft-brand-copy">
        <a href="/" class="ft-logo" aria-label="Paynancial home"><img src="<?= asset('images/paynancial-logo-dark-bg.png') ?>" alt="Paynancial" width="520" height="118" loading="lazy"></a>
        <p class="ft-statement">Payment &amp; financial infrastructure for modern businesses.</p>
        <a class="ft-sales" href="/contact?intent=sales"><?= e(cta_label()) ?> <span aria-hidden="true">→</span></a>
      </div>
      <form class="footer-newsletter ft-newsletter" id="newsletter-form" novalidate>
        <?= csrf_field() ?>
        <label for="newsletter-email">Get insights on payments, AI and financial infrastructure.</label>
        <div class="footer-newsletter-row">
          <input type="email" id="newsletter-email" name="email" placeholder="you@company.com" autocomplete="email" required>
          <button type="submit" class="btn btn-primary">Subscribe</button>
        </div>
        <p class="footer-newsletter-msg" aria-live="polite"></p>
      </form>
    </div>
  </div>

  <nav class="container ft-nav" aria-label="Footer">
    <?php foreach ($footer_groups as $key => $group): $panelId = 'ft-panel-' . $key; ?>
    <div class="ft-group ft-group-<?= e($key) ?>" data-ft-group>
      <h3 class="ft-title">
        <span><?= e($group['title']) ?><?php if (!empty($group['badge'])): ?> <span class="footer-badge"><?= e($group['badge']) ?></span><?php endif; ?></span>
        <button type="button" class="ft-toggle" aria-expanded="true" aria-controls="<?= e($panelId) ?>" data-ft-toggle>
          <span class="sr-only"><?= e($group['title']) ?></span><span class="ft-toggle-icon" aria-hidden="true"></span>
        </button>
      </h3>
      <div class="ft-panel" id="<?= e($panelId) ?>">
        <?php foreach ($group['blocks'] as $block): [$label, $links] = $block; $cls = $block[2] ?? ''; ?>
        <div class="ft-block <?= e($cls) ?>">
          <?php if ($label): ?><p class="ft-block-label"><?php if ($cls === 'is-trust'): ?><svg viewBox="0 0 24 24" width="13" height="13" aria-hidden="true" focusable="false"><path d="M12 3 5 6v5c0 4.5 3 8.3 7 10 4-1.7 7-5.5 7-10V6z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg><?php endif; ?><?= e($label) ?></p><?php endif; ?>
          <ul>
            <?php foreach ($links as [$text, $href]): ?>
            <li><a href="<?= e($href) ?>"><?= e($text) ?></a></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <?php endforeach; ?>
        <?php if (!empty($group['cta'])): ?>
        <div class="ft-cta-row">
          <a class="ft-cta" href="<?= e($group['cta'][1]) ?>"><?= e($group['cta'][0]) ?> <span aria-hidden="true">→</span></a>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </nav>

  <div class="footer-registry">
    <div class="container">
      <div class="footer-reg-row"><span>Legal Name</span><span>M/S Paynancial Technology Private Limited</span></div>
      <div class="footer-reg-row"><span>Email</span><a href="mailto:hello@paynancial.com">hello@paynancial.com</a></div>
      <div class="footer-reg-row"><span>GST No.</span><span class="mono">10AAOCP5173C1ZO</span></div>
      <div class="footer-reg-row"><span>CIN</span><span class="mono">U66190BR2024PTC067929</span></div>
    </div>
  </div>

  <div class="container ft-bottom">
    <p>&copy; <?= date('Y') ?> Paynancial Technology Pvt. Ltd. All Rights Reserved.</p>
    <ul class="ft-legal" aria-label="Legal">
      <li><a href="/legal/privacy-policy">Privacy</a></li>
      <li><a href="/legal/terms-conditions">Terms</a></li>
      <li><a href="/security">Security</a></li>
      <li><a href="/legal/cookie-policy">Cookies</a></li>
    </ul>
    <div class="footer-social ft-social" aria-label="Paynancial on social media" role="group">
        <a href="https://www.facebook.com/paynancial" target="_blank" rel="noopener noreferrer" aria-label="Paynancial on Facebook">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M13.5 22v-8.4h2.8l.4-3.3h-3.2V8.1c0-.95.27-1.6 1.63-1.6H17V3.5C16.7 3.46 15.68 3.37 14.5 3.37c-2.47 0-4.16 1.5-4.16 4.27v2.66H7.5v3.3h2.84V22h3.16Z"/></svg>
        </a>
        <a href="https://in.linkedin.com/company/paynancialai" target="_blank" rel="noopener noreferrer" aria-label="Paynancial on LinkedIn">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M20.45 20.45h-3.55v-5.57c0-1.33-.02-3.03-1.85-3.03-1.85 0-2.14 1.45-2.14 2.94v5.66H9.36V9h3.41v1.56h.05c.48-.9 1.64-1.85 3.37-1.85 3.6 0 4.27 2.37 4.27 5.46v6.28zM5.34 7.43a2.06 2.06 0 1 1 0-4.12 2.06 2.06 0 0 1 0 4.12zM7.12 20.45H3.56V9h3.56v11.45z"/></svg>
        </a>
        <a href="https://x.com/paynancial" target="_blank" rel="noopener noreferrer" aria-label="Paynancial on X">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M18.9 3H21l-6.4 7.3L22 21h-6.6l-5.2-6.6L3.8 21H1.7l6.9-7.9L1 3h6.7l4.7 6.1L18.9 3Zm-1.1 16h1.2L7.3 5H6l11.8 14Z"/></svg>
        </a>
        <a href="https://www.instagram.com/paynancial/" target="_blank" rel="noopener noreferrer" aria-label="Paynancial on Instagram">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.2" cy="6.8" r="1"/></svg>
        </a>
        <a href="https://in.pinterest.com/paynancial/" target="_blank" rel="noopener noreferrer" aria-label="Paynancial on Pinterest">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.5 2 3 5.9 3 10.3c0 2.5 1.4 4.2 2.3 4.2.4 0 .6-1.1.6-1.4 0-.4-1-1.2-1-2.8 0-3.3 2.5-5.7 5.9-5.7 2.9 0 5 1.6 5 4.4 0 2.6-1.2 5.3-3.5 5.3-.9 0-1.6-.7-1.4-1.6.3-1.2.9-2.5.9-3.4 0-.8-.4-1.5-1.3-1.5-1 0-1.9 1.1-1.9 2.6 0 1 .3 1.6.3 1.6s-1.1 4.9-1.3 5.7c-.4 1.6 0 3.7.1 3.9.1.1.2.1.2 0 .1-.1 1.2-1.6 1.5-3.2l.6-2.4c.3.7 1.3 1.2 2.3 1.2 3 0 5.2-2.8 5.2-6.4C19.6 4.9 16.4 2 12 2Z"/></svg>
        </a>
        <a href="https://www.youtube.com/@paynancial" target="_blank" rel="noopener noreferrer" aria-label="Paynancial on YouTube">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M22 12s0-3.2-.4-4.6c-.2-.9-.9-1.6-1.8-1.8C18.2 5 12 5 12 5s-6.2 0-7.8.6c-.9.2-1.6.9-1.8 1.8C2 8.8 2 12 2 12s0 3.2.4 4.6c.2.9.9 1.6 1.8 1.8C5.8 19 12 19 12 19s6.2 0 7.8-.6c.9-.2 1.6-.9 1.8-1.8C22 15.2 22 12 22 12Zm-12 3V9l5 3-5 3Z"/></svg>
        </a>
      </div>
  </div>
</footer>

<?php require_once __DIR__ . '/floating-enquiry.php'; fe_render((string) ($current_path ?? ($_SERVER['REQUEST_URI'] ?? '/'))); ?>

<?php include __DIR__ . '/login-panel.php'; ?>

<script src="<?= asset('js/main.js') ?>" defer></script>
