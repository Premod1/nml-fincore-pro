# CHANGELOG

All notable changes, architectural implementations, and milestones for **NML FinCore PRO (`nml/fincore-pro`)** are documented here.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

---

## [1.0.0] - 2026-11-08 22:50:00 +05:30

### Initial Commercial Release & Architecture Setup

#### Added
- **Zero-Accounting-Knowledge ERP Event Bridge (`FinCorePro::recordErpEvent`):**
  - Allows host Laravel ERP developers to record complex transactions (`credit_sale`, `cash_sale`, `customer_payment`, `credit_purchase`, `supplier_payment`, `expense_payment`) with plain business numbers (net, tax, cogs, total).
  - Automatically calculates and distributes Debit/Credit splits, Tax liability, and Inventory COGS lines without manual debit/credit logic in ERP controllers.
  - Polymorphic transaction references linking directly to host ERP models (Invoices, Bills, Payments).
- **ERP Auto-Posting Rules & Account Mapping UI (`/accounting/mappings`):**
  - Interactive web interface allowing accountants to dynamically re-map which General Ledger accounts are targeted by each ERP business event.
  - Clean client-ready interface with instant save capability.
  - Underlying database migrations (`fincore_posting_rules` and `fincore_posting_rule_lines`).
- **Dual-Mode UI Architecture:**
  - **Embedded Mode:** Seamlessly extends existing host ERP layouts (`@extends(config('fincore-pro.erp_layout'))`) preserving existing sidebar and top navbar.
  - **Standalone Mode:** Self-contained accounting portal with dedicated master layouts.
  - **Scoped CSS Variable System:** Isolated CSS (`.fincore-scope`, `--fc-*`) ensuring zero CSS collisions with host Bootstrap 4/5 or custom theme styles.
- **Hierarchical SaaS Dropdown Navigation:**
  - Clean drop-down navigation (`📊 Overview`, `🗂️ Chart of Accounts`, `📝 Transactions ▾`, `📈 Financial Reports ▾`, `⚙️ ERP Posting Rules`) designed to comfortably scale without breaking UI layouts.
- **Enhanced Journal Entries Sub-Page (`/accounting/journals`):**
  - Complete vouchers table with Entry Number, Date, Reference, Narration, Line-item counts, and Dr/Cr totals.
  - Multi-parameter filter toolbar: Search by Entry Number/Ref/Narration, filter by Voucher Status (`draft`, `submitted`, `posted`, `void`), and Date range filtering (`from_date` to `to_date`).
  - Voucher Detail view (`/accounting/journals/{id}`) with action buttons for Post to Ledger, Void Entry, and Clean Print.
  - Real-time Balanced Manual Journal Entry Creator (`/accounting/journals/create`).
- **Standard Financial Reports:**
  - Real-time Trial Balance with General Ledger drill-down (`/accounting/reports/trial-balance`).
  - Profit & Loss Statement (Income Statement) with date filtering (`/accounting/reports/income-statement`).
  - Balance Sheet (Statement of Financial Position) with real-time dynamic current-year earnings rollup (`/accounting/reports/balance-sheet`).
  - General Ledger Explorer with running balances and account statement filters (`/accounting/reports/general-ledger`).
- **Developer Tools & Documentation:**
  - Standalone local preview runner (`php preview [port]` or `composer serve`) using Testbench with auto-SQLite migration & seeding.
  - Complete integration guide in [README.md](README.md).
  - Complete end-to-end architecture flow in [WORKFLOW.md](WORKFLOW.md).
  - Exhaustive sub-page specifications and upcoming implementation plans in [FUTURE_ROADMAP.md](FUTURE_ROADMAP.md).
