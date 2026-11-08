# NML FinCore PRO - Complete Architecture & Implementation Roadmap

This document outlines the full end-to-end architecture, user journeys, monetization plan, and technical workflow for **NML FinCore PRO (`nml/fincore-pro`)** built on top of the open-source **[`nml/fincore`](https://github.com/Premod1/nml-fincore)** core engine.

---

## 1. System Architecture & Dual-Mode Concept

```
  ┌────────────────────────────────────────────────────────────────────────┐
  │                           Host Laravel ERP                             │
  │     (Blade, Bootstrap 4/5, Tailwind, or Livewire - e.g. sumal-erp)     │
  └───────────────────────────────────┬────────────────────────────────────┘
                                      │
                     FinCorePro::recordErpEvent(...)
                                      ▼
  ┌────────────────────────────────────────────────────────────────────────┐
  │                   NML FinCore PRO (Commercial Addon)                   │
  │   - ERP Auto-Posting Rules Engine                                      │
  │   - Account Mapping UI (/accounting/mappings)                          │
  │   - Scoped Bootstrap/Blade UI Kit (.fc-* isolated CSS variables)       │
  │   - Dual Mode: Embedded (@extends ERP Layout) OR Standalone Portal     │
  └───────────────────────────────────┬────────────────────────────────────┘
                                      │
                         Core Double-Entry & Ledger
                                      ▼
  ┌────────────────────────────────────────────────────────────────────────┐
  │                   NML FinCore (Free / Open-Source Core)                │
  │   - General Ledger, Balance Sheet, P&L, Trial Balance Calculation     │
  │   - Fixed Assets, Depreciation, Fiscal Periods & Locking               │
  │   - Polymorphic Transactions, Audit Logs, Immutable Journals           │
  └────────────────────────────────────────────────────────────────────────┘
```

### Dual-Mode UI Integration
1. **Embedded Mode (`FINCORE_PRO_MODE=embedded`):**
   * Uses `@extends(config('fincore-pro.erp_layout'))`.
   * Inherits the existing ERP's Sidebar, Top Header, and Authentication guards.
   * Scoped CSS (`.fincore-scope`, `--fc-primary`) ensures zero style degradation or clashes with host Bootstrap or custom ERP stylesheets.
2. **Standalone Mode (`FINCORE_PRO_MODE=standalone`):**
   * Operates as a self-contained Accounting Portal at `/accounting`.
   * Ideal for clients whose ERP frontend is built with decoupling technologies (e.g. React SPA, Vue, or legacy UI).

---

## 2. Complete Project Workflow & Lifecycle

### Milestone 1: Core Foundation & UI Integration (Completed)
- [x] Dual-mode Layout wrapper (`wrapper.blade.php`, `standalone.blade.php`).
- [x] Hierarchical SaaS Dropdown Navigation (`sub-navbar.blade.php`).
- [x] Zero-Accounting-Knowledge ERP Posting Engine (`ErpPostingEngine.php`).
- [x] Database migrations for dynamic mapping rules (`fincore_posting_rules`, `fincore_posting_rule_lines`).
- [x] Accountant-facing Mapping Management UI (`/accounting/mappings`).
- [x] Financial Overview Dashboard (`/accounting`).
- [x] Standard Chart of Accounts Manager (`/accounting/accounts`).
- [x] Real-time Balanced Manual Journal Entry screen (`/accounting/journals/create`).
- [x] Financial Statements: Trial Balance, Profit & Loss, Balance Sheet, General Ledger.
- [x] Standalone local preview runner (`php preview [port]`).

### Milestone 2: Advanced Financial Modules (Next Phase)
- [ ] **Smart Bank Reconciliation UI (`/accounting/reconciliation`):**
  * CSV/OFX statement upload.
  * Auto-matching engine linking statement records with General Ledger bank entries.
  * Visual reconciliation matching checklist with unreconciled difference tracker.
- [ ] **Maker-Checker & Approval Workflows:**
  * Draft voucher submission (`status: submitted`).
  * Senior Accountant / Manager Approve & Void actions with audit notes.
- [ ] **Fiscal Period & Year-End Closing UI (`/accounting/periods`):**
  * Visual period locking calendar (prevent back-dated transactions).
  * Automated Year-End retained earnings rollup journal generation.
- [ ] **Partner Ageing Reports (AR / AP Ageing):**
  * 30 / 60 / 90 / 120+ days aging buckets for Customer Receivables and Supplier Payables.

### Milestone 3: Audit-Ready PDF & Excel Reports
- [ ] Integration of headless PDF export templates (Barryvdh DomPDF / Snappy) for Trial Balance, Balance Sheet, and Income Statement.
- [ ] One-click Excel/CSV report exports.

### Milestone 4: Packaging, Licensing & Commercialization
- [ ] Private Composer Repository setup (using Anystack.sh, Polar.sh, or GitHub Sponsors).
- [ ] License key validation middleware for multi-tenant / client deployments.
- [ ] Launch on LemonSqueezy / Gumroad (\$69 Single / \$179 Unlimited Agency).

---

## 3. Developer Quick Reference

### Testing Local UI
```bash
cd /home/pramod/project/pramod/nml-fincore-pro
php preview 8085
```

### Event Bridge Code Snippet (ERP Controllers)
```php
use Nml\FinCorePro\Facades\FinCorePro;

// Credit Sale Event
FinCorePro::recordErpEvent('credit_sale', [
    'net_amount'   => $order->sub_total,
    'tax_amount'   => $order->tax_total,
    'total_amount' => $order->grand_total,
    'cogs_amount'  => $order->cost_price,
    'reference'    => $order,
    'partner'      => $order->customer,
    'description'  => "Sales Order #{$order->order_number}",
]);
```
