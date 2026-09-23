<footer class="site-footer">
  <div class="footer-statement">
    <div class="container footer-statement-grid">
      <div>
        <h2>Building the financial infrastructure for an AI-native economy.</h2>
        <p>Payments, payouts, billing, reconciliation and financial intelligence — connected through one platform, built for a world where AI agents are starting to run a growing share of the work in between.</p>
      </div>
      <form class="footer-newsletter" id="newsletter-form" novalidate>
        <?= csrf_field() ?>
        <label for="newsletter-email">Get insights on payments, AI and financial infrastructure.</label>
        <div class="footer-newsletter-row">
          <input type="email" id="newsletter-email" name="email" placeholder="you@company.com" required>
          <button type="submit" class="btn btn-primary">Subscribe</button>
        </div>
        <p class="footer-newsletter-msg" aria-live="polite"></p>
      </form>
    </div>
  </div>

  <div class="container">
    <div class="footer-grid">
      <div class="footer-brand">
        <a href="/" class="brand"><img src="<?= asset('images/paynancial-logo-dark-bg.png') ?>" alt="Paynancial" class="brand-logo"></a>
        <p>Smart Solutions. Simplified Payments.</p>
        <div class="footer-social">
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
      <div>
        <h4>Company</h4>
        <ul>
          <li><a href="/about">About Paynancial</a></li>
          <li><a href="/technology">Technology</a></li>
          <li><a href="/leadership">Leadership</a></li>
          <li><a href="/careers">Careers</a></li>
          <li><a href="/contact">Contact Us</a></li>
          <li><a href="/blog">News &amp; Insights</a></li>
        </ul>
      </div>
      <div>
        <h4>Products &amp; Solutions</h4>
        <ul>
          <li><a href="/products">Payment Infrastructure</a></li>
          <li><a href="/products#cat-ai-intelligence">AI &amp; Intelligence</a></li>
          <li><a href="/solutions#business">By Business Size</a></li>
          <li><a href="/solutions#industries">By Industry</a></li>
          <li><a href="/solutions#enterprise">Enterprise</a></li>
          <li><a href="/pricing">Pricing</a></li>
        </ul>
      </div>
      <div>
        <h4>Agentic AI <span class="footer-badge">New</span></h4>
        <ul>
          <li><a href="/agentic-ai">Agentic AI in Finance</a></li>
          <li><a href="/agentic-ai#financial-agents">AI Financial Agents</a></li>
          <li><a href="/agentic-ai#payment-orchestration">Payment Orchestration</a></li>
          <li><a href="/agentic-ai#governance">Human-in-the-Loop &amp; Governance</a></li>
          <li><a href="/technology">The Future of Financial Infrastructure</a></li>
        </ul>
      </div>
      <div>
        <h4>Developers</h4>
        <ul>
          <li><a href="/developers#docs">Documentation</a></li>
          <li><a href="/developers#api-reference">API Reference</a></li>
          <li><a href="/developers#webhooks">Webhooks</a></li>
          <li><a href="/developers#sandbox">Sandbox</a></li>
          <li><a href="/developers#agentic-ai">Agent-Ready APIs</a></li>
        </ul>
      </div>
      <div>
        <h4>Resources &amp; Trust</h4>
        <ul>
          <li><a href="/blog">Insights / Blog</a></li>
          <li><a href="/support#faqs">FAQs</a></li>
          <li><a href="/support">Support Center</a></li>
          <li><a href="/trust">Trust Center</a></li>
          <li><a href="/security">Security</a></li>
        </ul>
      </div>
    </div>
  </div>

  <div class="footer-registry">
    <div class="container">
      <div class="footer-reg-row"><span>Legal Name</span><span>M/S Paynancial Technology Private Limited</span></div>
      <div class="footer-reg-row"><span>Email</span><a href="mailto:hello@paynancial.com">hello@paynancial.com</a></div>
      <div class="footer-reg-row"><span>GST No.</span><span class="mono">10AAOCP5173C1ZO</span></div>
      <div class="footer-reg-row"><span>CIN</span><span class="mono">U66190BR2024PTC067929</span></div>
    </div>
  </div>

  <div class="container">
    <div class="footer-bottom">
      <span>&copy; <?= date('Y') ?> Paynancial Technology Pvt. Ltd. All Rights Reserved.</span>
      <ul>
        <li><a href="/legal/privacy-policy">Privacy Policy</a></li>
        <li><a href="/legal/terms-conditions">Terms &amp; Conditions</a></li>
        <li><a href="/legal/refund-policy">Refund Policy</a></li>
        <li><a href="/legal/cookie-policy">Cookie Policy</a></li>
        <li><a href="/security">Security</a></li>
        <li><a href="/trust">Trust Center</a></li>
      </ul>
    </div>
  </div>
</footer>

<?php include __DIR__ . '/login-panel.php'; ?>

<script src="<?= asset('js/main.js') ?>" defer></script>
