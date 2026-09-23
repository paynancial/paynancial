<?php
/**
 * Site header: light glass nav with mega menus + login/get-started actions.
 * Expects $current_path to be set by the front controller (optional).
 */
$current_path = $current_path ?? ($_SERVER['REQUEST_URI'] ?? '/');
?>
<a class="sr-only" href="#main-content">Skip to main content</a>
<div class="utility-bar">
  <div class="utility-left">
    <span>AI-Powered Financial Infrastructure</span>
    <span>Global Payments</span>
    <span>Secure &amp; Trusted</span>
  </div>
  <div class="utility-right">
    <span class="utility-support">
      <a href="https://wa.me/917066820820" target="_blank" rel="noopener">
        <i class="support-dot" aria-hidden="true"></i>24&times;7 Support
      </a>
    </span>
    <div class="utility-lang" data-lang-select>
      <button type="button" class="utility-lang-btn" aria-haspopup="true" aria-expanded="false">
        EN <i class="chev" aria-hidden="true"></i>
      </button>
      <div class="utility-lang-menu" role="menu" aria-label="Select language">
        <button type="button" class="utility-lang-option is-active" role="menuitem">English</button>
        <button type="button" class="utility-lang-option" role="menuitem" disabled>हिन्दी <span class="soon">Coming soon</span></button>
        <button type="button" class="utility-lang-option" role="menuitem" disabled>தமிழ் <span class="soon">Coming soon</span></button>
        <button type="button" class="utility-lang-option" role="menuitem" disabled>తెలుగు <span class="soon">Coming soon</span></button>
        <button type="button" class="utility-lang-option" role="menuitem" disabled>বাংলা <span class="soon">Coming soon</span></button>
        <button type="button" class="utility-lang-option" role="menuitem" disabled>मराठी <span class="soon">Coming soon</span></button>
      </div>
    </div>
  </div>
</div>
<header class="site-header">
  <div class="container">
    <a href="/" class="brand" aria-label="Paynancial home">
      <img src="<?= asset('images/paynancial-logo.png') ?>" alt="Paynancial" class="brand-logo">
    </a>

    <nav aria-label="Primary">
      <ul class="main-nav">
        <li class="nav-item">
          <button class="nav-link" aria-haspopup="true" aria-expanded="false">Products <i class="chev" aria-hidden="true"></i></button>
          <div class="mega-menu mega-menu-wide">
            <div>
              <div class="mega-col-title">Accept &amp; Collect</div>
              <a class="mega-link mega-link-plain" href="/products/payment-gateway"><strong>Payment Gateway</strong></a>
              <a class="mega-link mega-link-plain" href="/products/payment-links"><strong>Payment Links</strong></a>
              <a class="mega-link mega-link-plain" href="/contact?intent=sales&amp;product=payment-pages"><strong>Payment Pages</strong></a>
              <a class="mega-link mega-link-plain" href="/contact?intent=sales&amp;product=upi-payments"><strong>UPI Payments</strong></a>
              <a class="mega-link mega-link-plain" href="/contact?intent=sales&amp;product=recurring-payments"><strong>Recurring Payments</strong></a>
              <a class="mega-link mega-link-plain" href="/contact?intent=sales&amp;product=subscription-billing"><strong>Subscription Billing</strong></a>
              <a class="mega-link mega-link-plain" href="/products/payment-collection"><strong>Smart Collections</strong></a>
            </div>
            <div>
              <div class="mega-col-title">Pay &amp; Move Money</div>
              <a class="mega-link mega-link-plain" href="/products/payouts"><strong>Payouts</strong></a>
              <a class="mega-link mega-link-plain" href="/contact?intent=sales&amp;product=bulk-payouts"><strong>Bulk Payouts</strong></a>
              <a class="mega-link mega-link-plain" href="/contact?intent=sales&amp;product=vendor-payments"><strong>Vendor Payments</strong></a>
              <a class="mega-link mega-link-plain" href="/contact?intent=sales&amp;product=employee-payments"><strong>Employee Payments</strong></a>
              <a class="mega-link mega-link-plain" href="/contact?intent=sales&amp;product=partner-payments"><strong>Partner Payments</strong></a>
              <a class="mega-link mega-link-plain" href="/contact?intent=sales&amp;product=international-payments"><strong>International Payments</strong></a>
            </div>
            <div>
              <div class="mega-col-title">Financial Operations</div>
              <a class="mega-link mega-link-plain" href="/products/payment-analytics"><strong>Reconciliation</strong></a>
              <a class="mega-link mega-link-plain" href="/products/payment-analytics"><strong>Settlements</strong></a>
              <a class="mega-link mega-link-plain" href="/products/payment-analytics"><strong>Refunds</strong></a>
              <a class="mega-link mega-link-plain" href="/contact?intent=sales&amp;product=chargebacks"><strong>Chargebacks</strong></a>
              <a class="mega-link mega-link-plain" href="/contact?intent=sales&amp;product=invoice-management"><strong>Invoice Management</strong></a>
              <a class="mega-link mega-link-plain" href="/contact?intent=sales&amp;product=expense-management"><strong>Expense Management</strong></a>
              <a class="mega-link mega-link-plain" href="/products/payment-analytics"><strong>Finance Analytics</strong></a>
              <a class="mega-link mega-link-plain" href="/contact?intent=sales&amp;product=mis-reports"><strong>MIS &amp; Reports</strong></a>
            </div>
            <div>
              <div class="mega-col-title">AI &amp; Intelligence</div>
              <a class="mega-link mega-link-plain" href="/contact?intent=sales&amp;product=paynancial-ai"><strong>Paynancial AI</strong></a>
              <a class="mega-link mega-link-plain" href="/contact?intent=sales&amp;product=ai-fraud-detection"><strong>AI Fraud Detection</strong></a>
              <a class="mega-link mega-link-plain" href="/contact?intent=sales&amp;product=ai-reconciliation"><strong>AI Reconciliation</strong></a>
              <a class="mega-link mega-link-plain" href="/contact?intent=sales&amp;product=ai-financial-assistant"><strong>AI Financial Assistant</strong></a>
              <a class="mega-link mega-link-plain" href="/contact?intent=sales&amp;product=ai-cash-flow-intelligence"><strong>AI Cash-Flow Intelligence</strong></a>
              <a class="mega-link mega-link-plain" href="/contact?intent=sales&amp;product=ai-revenue-forecasting"><strong>AI Revenue Forecasting</strong></a>
            </div>
            <div>
              <div class="mega-col-title">Embedded Finance</div>
              <a class="mega-link mega-link-plain" href="/contact?intent=sales&amp;product=embedded-payments"><strong>Embedded Payments</strong></a>
              <a class="mega-link mega-link-plain" href="/contact?intent=sales&amp;product=embedded-payouts"><strong>Embedded Payouts</strong></a>
              <a class="mega-link mega-link-plain" href="/contact?intent=sales&amp;product=embedded-billing"><strong>Embedded Billing</strong></a>
              <a class="mega-link mega-link-plain" href="/contact?intent=sales&amp;product=wallet-infrastructure"><strong>Wallet Infrastructure</strong></a>
              <a class="mega-link mega-link-plain" href="/contact?intent=sales&amp;product=split-payments"><strong>Split Payments</strong></a>
              <a class="mega-link mega-link-plain" href="/contact?intent=sales&amp;product=white-label-payments"><strong>White-Label Payments</strong></a>
            </div>
            <div>
              <div class="mega-col-title">Developer Platform</div>
              <a class="mega-link mega-link-plain" href="/developers#docs"><strong>Payment APIs</strong></a>
              <a class="mega-link mega-link-plain" href="/developers#docs"><strong>Payout APIs</strong></a>
              <a class="mega-link mega-link-plain" href="/developers#sdks"><strong>SDKs</strong></a>
              <a class="mega-link mega-link-plain" href="/developers#webhooks"><strong>Webhooks</strong></a>
              <a class="mega-link mega-link-plain" href="/developers#sandbox"><strong>Sandbox</strong></a>
              <a class="mega-link mega-link-plain" href="/developers"><strong>API Dashboard</strong></a>
            </div>
          </div>
        </li>
        <li class="nav-item">
          <button class="nav-link" aria-haspopup="true" aria-expanded="false">Solutions <i class="chev" aria-hidden="true"></i></button>
          <div class="mega-menu">
            <div>
              <div class="mega-col-title">By Industry</div>
              <a class="mega-link" href="/solutions#ecommerce"><strong>E-Commerce</strong><span>Fast, reliable checkout at scale</span></a>
              <a class="mega-link" href="/solutions#travel"><strong>Travel</strong><span>High-value, multi-currency bookings</span></a>
              <a class="mega-link" href="/solutions#healthcare"><strong>Healthcare</strong><span>Secure billing for clinics &amp; hospitals</span></a>
              <a class="mega-link" href="/solutions#education"><strong>Education</strong><span>Fee collection made simple</span></a>
            </div>
            <div>
              <div class="mega-col-title">&nbsp;</div>
              <a class="mega-link" href="/solutions#retail"><strong>Retail</strong><span>Online and in-store payment flows</span></a>
              <a class="mega-link" href="/solutions#hospitality"><strong>Hospitality</strong><span>Bookings, deposits, and on-site payments</span></a>
              <a class="mega-link" href="/solutions#professional-services"><strong>Professional Services</strong><span>Simple invoicing and collection</span></a>
              <a class="mega-link" href="/solutions#enterprise"><strong>Enterprise</strong><span>Custom infrastructure for scale</span></a>
            </div>
          </div>
        </li>
        <li class="nav-item">
          <button class="nav-link" aria-haspopup="true" aria-expanded="false">Developers <i class="chev" aria-hidden="true"></i></button>
          <div class="mega-menu">
            <div>
              <div class="mega-col-title">Build</div>
              <a class="mega-link" href="/developers#docs"><strong>API Documentation</strong><span>Everything you need to integrate</span></a>
              <a class="mega-link" href="/developers#integration-guide"><strong>Integration Guide</strong><span>Step-by-step setup for your stack</span></a>
              <a class="mega-link" href="/developers#sdks"><strong>SDKs</strong><span>PHP, JavaScript, Python client libraries</span></a>
            </div>
            <div>
              <div class="mega-col-title">Reference</div>
              <a class="mega-link" href="/developers#api-reference"><strong>API Reference</strong><span>Every endpoint, request &amp; response</span></a>
              <a class="mega-link" href="/developers#sandbox"><strong>Sandbox</strong><span>Test integrations safely</span></a>
              <a class="mega-link" href="/developers#webhooks"><strong>Webhooks</strong><span>Real-time event notifications</span></a>
            </div>
          </div>
        </li>
        <li class="nav-item"><a class="nav-link" href="/pricing">Pricing</a></li>
        <li class="nav-item"><a class="nav-link" href="/partners">Partners</a></li>
        <li class="nav-item">
          <button class="nav-link" aria-haspopup="true" aria-expanded="false">Resources <i class="chev" aria-hidden="true"></i></button>
          <div class="mega-menu">
            <div>
              <div class="mega-col-title">Learn</div>
              <a class="mega-link" href="/developers#docs"><strong>API Documentation</strong><span>Everything you need to integrate</span></a>
              <a class="mega-link" href="/developers#integration-guide"><strong>Integration Guides</strong><span>Step-by-step setup for your stack</span></a>
              <a class="mega-link" href="/support#faqs"><strong>FAQs</strong><span>Answers to common questions</span></a>
            </div>
            <div>
              <div class="mega-col-title">Get Help</div>
              <a class="mega-link" href="/support"><strong>Support Center</strong><span>Get help from our support team</span></a>
              <a class="mega-link" href="/security"><strong>Security &amp; Compliance</strong><span>How we protect every transaction</span></a>
              <a class="mega-link" href="/blog"><strong>Blog / Insights</strong><span>Product updates &amp; payment insights</span></a>
            </div>
          </div>
        </li>
        <li class="nav-item">
          <button class="nav-link" aria-haspopup="true" aria-expanded="false">Company <i class="chev" aria-hidden="true"></i></button>
          <div class="mega-menu">
            <div>
              <div class="mega-col-title">About</div>
              <a class="mega-link" href="/about"><strong>About Us</strong><span>Our mission &amp; story</span></a>
              <a class="mega-link" href="/leadership"><strong>Leadership</strong><span>The team steering Paynancial</span></a>
              <a class="mega-link" href="/careers"><strong>Careers</strong><span>Build the future of payments with us</span></a>
            </div>
            <div>
              <div class="mega-col-title">Connect</div>
              <a class="mega-link" href="/contact"><strong>Contact</strong><span>Talk to sales or support</span></a>
              <a class="mega-link" href="/blog"><strong>News / Blog</strong><span>Company updates &amp; announcements</span></a>
              <a class="mega-link" href="/partners"><strong>Partners</strong><span>Grow with the Paynancial partner program</span></a>
            </div>
          </div>
        </li>
      </ul>
    </nav>

    <div class="header-actions">
      <a href="/contact" class="btn-contact btn-contact-outline">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
        Contact Us
      </a>
      <button type="button" class="btn-login-signup" data-login-open>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        Login / Sign Up
      </button>
      <button class="hamburger" aria-label="Open menu"><span></span></button>
    </div>
  </div>
</header>

<div class="tape" aria-hidden="true">
  <div class="tape-track">
    <?php
      $tape_items = ["INITIATE → VERIFY → <strong>SETTLE</strong>", "BUILT FOR SPEED", "MADE IN INDIA", "AGENT-READY APIS", "INITIATE → VERIFY → <strong>SETTLE</strong>", "BUILT FOR SPEED", "MADE IN INDIA", "AGENT-READY APIS"];
      foreach ($tape_items as $tape_item) {
          echo '<span class="tape-item">' . $tape_item . '</span>';
      }
    ?>
  </div>
</div>

<div class="mobile-nav" aria-hidden="true">
  <div class="mobile-nav-header">
    <span class="brand"><img src="<?= asset('images/paynancial-logo.png') ?>" alt="Paynancial" class="brand-logo"></span>
    <button class="login-close mobile-nav-close" aria-label="Close menu">&times;</button>
  </div>
  <details>
    <summary>Products <i class="chev" aria-hidden="true"></i></summary>
    <ul>
      <li class="mobile-nav-group-label">Accept &amp; Collect</li>
      <li><a href="/products/payment-gateway">Payment Gateway</a></li>
      <li><a href="/products/payment-links">Payment Links</a></li>
      <li><a href="/contact?intent=sales&amp;product=payment-pages">Payment Pages</a></li>
      <li><a href="/contact?intent=sales&amp;product=upi-payments">UPI Payments</a></li>
      <li><a href="/contact?intent=sales&amp;product=recurring-payments">Recurring Payments</a></li>
      <li><a href="/contact?intent=sales&amp;product=subscription-billing">Subscription Billing</a></li>
      <li><a href="/products/payment-collection">Smart Collections</a></li>

      <li class="mobile-nav-group-label">Pay &amp; Move Money</li>
      <li><a href="/products/payouts">Payouts</a></li>
      <li><a href="/contact?intent=sales&amp;product=bulk-payouts">Bulk Payouts</a></li>
      <li><a href="/contact?intent=sales&amp;product=vendor-payments">Vendor Payments</a></li>
      <li><a href="/contact?intent=sales&amp;product=employee-payments">Employee Payments</a></li>
      <li><a href="/contact?intent=sales&amp;product=partner-payments">Partner Payments</a></li>
      <li><a href="/contact?intent=sales&amp;product=international-payments">International Payments</a></li>

      <li class="mobile-nav-group-label">Financial Operations</li>
      <li><a href="/products/payment-analytics">Reconciliation</a></li>
      <li><a href="/products/payment-analytics">Settlements</a></li>
      <li><a href="/products/payment-analytics">Refunds</a></li>
      <li><a href="/contact?intent=sales&amp;product=chargebacks">Chargebacks</a></li>
      <li><a href="/contact?intent=sales&amp;product=invoice-management">Invoice Management</a></li>
      <li><a href="/contact?intent=sales&amp;product=expense-management">Expense Management</a></li>
      <li><a href="/products/payment-analytics">Finance Analytics</a></li>
      <li><a href="/contact?intent=sales&amp;product=mis-reports">MIS &amp; Reports</a></li>

      <li class="mobile-nav-group-label">AI &amp; Intelligence</li>
      <li><a href="/contact?intent=sales&amp;product=paynancial-ai">Paynancial AI</a></li>
      <li><a href="/contact?intent=sales&amp;product=ai-fraud-detection">AI Fraud Detection</a></li>
      <li><a href="/contact?intent=sales&amp;product=ai-reconciliation">AI Reconciliation</a></li>
      <li><a href="/contact?intent=sales&amp;product=ai-financial-assistant">AI Financial Assistant</a></li>
      <li><a href="/contact?intent=sales&amp;product=ai-cash-flow-intelligence">AI Cash-Flow Intelligence</a></li>
      <li><a href="/contact?intent=sales&amp;product=ai-revenue-forecasting">AI Revenue Forecasting</a></li>

      <li class="mobile-nav-group-label">Embedded Finance</li>
      <li><a href="/contact?intent=sales&amp;product=embedded-payments">Embedded Payments</a></li>
      <li><a href="/contact?intent=sales&amp;product=embedded-payouts">Embedded Payouts</a></li>
      <li><a href="/contact?intent=sales&amp;product=embedded-billing">Embedded Billing</a></li>
      <li><a href="/contact?intent=sales&amp;product=wallet-infrastructure">Wallet Infrastructure</a></li>
      <li><a href="/contact?intent=sales&amp;product=split-payments">Split Payments</a></li>
      <li><a href="/contact?intent=sales&amp;product=white-label-payments">White-Label Payments</a></li>

      <li class="mobile-nav-group-label">Developer Platform</li>
      <li><a href="/developers#docs">Payment APIs</a></li>
      <li><a href="/developers#docs">Payout APIs</a></li>
      <li><a href="/developers#sdks">SDKs</a></li>
      <li><a href="/developers#webhooks">Webhooks</a></li>
      <li><a href="/developers#sandbox">Sandbox</a></li>
      <li><a href="/developers">API Dashboard</a></li>
    </ul>
  </details>
  <details>
    <summary>Solutions <i class="chev" aria-hidden="true"></i></summary>
    <ul>
      <li><a href="/solutions#ecommerce">E-Commerce</a></li>
      <li><a href="/solutions#travel">Travel</a></li>
      <li><a href="/solutions#healthcare">Healthcare</a></li>
      <li><a href="/solutions#education">Education</a></li>
      <li><a href="/solutions#retail">Retail</a></li>
      <li><a href="/solutions#hospitality">Hospitality</a></li>
      <li><a href="/solutions#professional-services">Professional Services</a></li>
      <li><a href="/solutions#enterprise">Enterprise</a></li>
    </ul>
  </details>
  <details>
    <summary>Developers <i class="chev" aria-hidden="true"></i></summary>
    <ul>
      <li><a href="/developers#docs">API Documentation</a></li>
      <li><a href="/developers#integration-guide">Integration Guide</a></li>
      <li><a href="/developers#sdks">SDKs</a></li>
      <li><a href="/developers#api-reference">API Reference</a></li>
      <li><a href="/developers#sandbox">Sandbox</a></li>
      <li><a href="/developers#webhooks">Webhooks</a></li>
    </ul>
  </details>
  <details>
    <summary>Resources <i class="chev" aria-hidden="true"></i></summary>
    <ul>
      <li><a href="/developers#docs">API Documentation</a></li>
      <li><a href="/developers#integration-guide">Integration Guides</a></li>
      <li><a href="/support#faqs">FAQs</a></li>
      <li><a href="/support">Support Center</a></li>
      <li><a href="/security">Security &amp; Compliance</a></li>
      <li><a href="/blog">Blog / Insights</a></li>
    </ul>
  </details>
  <details>
    <summary>Company <i class="chev" aria-hidden="true"></i></summary>
    <ul>
      <li><a href="/about">About Us</a></li>
      <li><a href="/leadership">Leadership</a></li>
      <li><a href="/careers">Careers</a></li>
      <li><a href="/contact">Contact</a></li>
      <li><a href="/blog">News / Blog</a></li>
      <li><a href="/partners">Partners</a></li>
    </ul>
  </details>
  <a href="/pricing" class="nav-link" style="font-weight:700;">Pricing</a>
  <div class="mobile-nav-actions">
    <a href="/contact" class="btn-contact btn-contact-outline">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
      Contact Us
    </a>
    <button type="button" class="btn-login-signup" data-login-open>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      Login / Sign Up
    </button>
  </div>
</div>
