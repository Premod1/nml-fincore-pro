# NML FinCore PRO - Comprehensive Roadmap & Sub-Pages Implementation Masterplan

This document provides a complete audit of all accounting sub-pages, business features, and modules supported by the underlying **[`nml/fincore`](https://github.com/Premod1/nml-fincore)** engine, detailing what is currently built in **`nml/fincore-pro`** and the exact implementation specifications for all upcoming sub-pages.

---

## 🗺️ Master Navigation & Sub-Pages Inventory

| Top Menu Section | Sub-Page Route | Underlying Core Engine / Model | Implementation Status |
| :--- | :--- | :--- | :--- |
| **📊 Overview** | `/accounting` | `LedgerEngine::getIncomeStatement()`, `getBalanceSheet()`, `getTrialBalance()` | ✅ **Completed** |
| **🗂️ Master Data** | `/accounting/accounts` | `Account`, `ChartOfAccountsInitializer` | ✅ **Completed** |
| **📝 Transactions** | `/accounting/journals` | `JournalEntry`, `JournalEntryLine`, `JvStatus` | ✅ **Completed** |
| | `/accounting/journals/create` | Manual Journal Voucher with Real-time Balanced Dr/Cr | ✅ **Completed** |
| | `/accounting/journals/{id}` | Voucher Show, Post, Void, Audit Print | ✅ **Completed** |
| | `/accounting/reconciliation` | `BankReconciliationEngine`, `BankReconciliation` | ⏳ **Roadmap: Module 1** |
| | `/accounting/periods` | `FiscalYearClosingEngine`, `FiscalYear`, `FiscalPeriod` | ⏳ **Roadmap: Module 2** |
| | `/accounting/assets` | `DepreciationEngine`, `FixedAsset`, `DepreciationLog` | ⏳ **Roadmap: Module 3** |
| **📈 Financial Reports**| `/accounting/reports/trial-balance` | `LedgerEngine::getTrialBalance()` | ✅ **Completed** |
| | `/accounting/reports/income-statement` | `LedgerEngine::getIncomeStatement()` | ✅ **Completed** |
| | `/accounting/reports/balance-sheet` | `LedgerEngine::getBalanceSheet()` | ✅ **Completed** |
| | `/accounting/reports/general-ledger` | `LedgerEngine::getGeneralLedger()` | ✅ **Completed** |
| | `/accounting/reports/cash-flow` | `LedgerEngine::getCashFlowStatement()` | ⏳ **Roadmap: Module 4** |
| | `/accounting/reports/partner-ageing` | `PartnerAgeingEngine::getReceivablesAgeingReport()`, `getPayablesAgeingReport()` | ⏳ **Roadmap: Module 5** |
| | `/accounting/reports/budget-variance` | `BudgetEngine::getBudgetVarianceReport()`, `Budget` | ⏳ **Roadmap: Module 6** |
| **⚙️ ERP Rules** | `/accounting/mappings` | `ErpPostingEngine`, `PostingRule`, `PostingRuleLine` | ✅ **Completed** |
| | `/accounting/audit-logs` | `AuditLogService`, `AuditLog` | ⏳ **Roadmap: Module 7** |

---

## 🏗️ Detailed Specifications for Upcoming Sub-Pages

### Module 1: Bank Reconciliation UI (`/accounting/reconciliation`)
* **Underlying Engine:** `Nml\FinCore\Services\BankReconciliationEngine`
* **Purpose:** Allows accountants to match bank statements with posted General Ledger bank lines and prevent discrepancies.
* **Key Components & Screens:**
  1. **Bank Account Selector & Statement Input:**
     * Dropdown of Cash/Bank accounts (`code 1100`).
     * Statement End Date, Statement Opening Balance, Statement Closing Balance.
  2. **Interactive Matching Checklist:**
     * Table of unreconciled lines (`getUnreconciledLines($accountId, $endDate)`).
     * Checkbox to mark cleared entries (`clearLine($lineId, $reconId)`).
  3. **Auto-Match Algorithm:**
     * CSV statement upload parser (Date, Ref, Amount).
     * Exact and Fuzzy matching suggestions by Date + Amount.
  4. **Live Difference Indicator:**
     * Formula: `Statement Ending Balance - Cleared Balance = Difference`.
     * "Finalize Reconciliation" button unlocks only when Difference is `0.00`.

---

### Module 2: Fiscal Periods & Year-End Closing (`/accounting/periods`)
* **Underlying Engine:** `Nml\FinCore\Services\FiscalYearClosingEngine`
* **Purpose:** Fiscal period locking and annual profit/loss retained earnings rollup.
* **Key Components & Screens:**
  1. **Fiscal Calendar Grid:**
     * List of 12 monthly periods per fiscal year.
     * Quick toggle: **Lock / Unlock Period** (`is_closed`).
     * Prevents back-dated entries or fraudulent transaction modifications in closed months.
  2. **Year-End Closing Wizard (`/accounting/periods/year-end`):**
     * Select Retained Earnings account (`code 3100`).
     * Displays calculated Net Profit/Loss for the year.
     * Generates atomic compound closing journal entry (`JvType::CLOSING`), resets all Revenue and Expense accounts to `0.00`, and transfers net profit to Retained Earnings.

---

### Module 3: Fixed Asset Register & Automated Depreciation (`/accounting/assets`)
* **Underlying Engine:** `Nml\FinCore\Services\DepreciationEngine`
* **Purpose:** Track tangible capital assets and run monthly depreciation schedules.
* **Key Components & Screens:**
  1. **Asset Register Table (`/accounting/assets`):**
     * Asset Code, Name, Asset Account (`1500`), Accumulated Depreciation Account (`1550`), Purchase Date, Purchase Cost, Salvage Value.
     * Depreciation Method: **Straight Line** or **Reducing Balance**.
  2. **Depreciation Run Wizard:**
     * One-click "Run Monthly Depreciation for [Month/Year]".
     * Automatically calculates depreciation for all active assets via `calculateMonthlyDepreciation()`.
     * Automatically creates and posts balanced depreciation vouchers (`Debit 6500 - Depreciation Expense`, `Credit 1550 - Accumulated Depreciation`).

---

### Module 4: Statement of Cash Flows (`/accounting/reports/cash-flow`)
* **Underlying Engine:** `LedgerEngine::getCashFlowStatement($startDate, $endDate, $sbuCode)` (Indirect Method)
* **Purpose:** Audit-compliant Cash Flow statement categorized by international standard accounting principles.
* **Key Sections Rendered:**
  1. **Cash Flows from Operating Activities:**
     * Starting Net Income (from Income Statement).
     * Add back non-cash expenses (Depreciation).
     * Working capital adjustments (Changes in Current Assets and Current Liabilities).
  2. **Cash Flows from Investing Activities:**
     * Capital expenditures, purchases/sales of Fixed Assets.
  3. **Cash Flows from Financing Activities:**
     * Debt drawdowns, repayments, capital/equity changes.
  4. **Net Increase/(Decrease) in Cash & Cash Equivalents:**
     * Opening Cash + Net Change = Closing Cash (validated against Bank & Cash accounts).

---

### Module 5: Customer & Supplier Ageing Reports (`/accounting/reports/partner-ageing`)
* **Underlying Engine:** `Nml\FinCore\Services\PartnerAgeingEngine`
* **Purpose:** Granular aging analysis of overdue invoices and bills for liquidity tracking.
* **Sub-Screens:**
  1. **Accounts Receivable (A/R) Ageing (`/accounting/reports/ar-ageing`):**
     * Grouped by Customer (`partnerable_type`, `partnerable_id`).
     * Aging Buckets: `Current (Not Due)`, `1-30 Days`, `31-60 Days`, `61-90 Days`, `91-120 Days`, `120+ Days Overdue`.
  2. **Accounts Payable (A/P) Ageing (`/accounting/reports/ap-ageing`):**
     * Grouped by Supplier with the same aging intervals.
  3. **Drill-down to Source Invoice/Bill:**
     * Clicking an invoice opens the original ERP reference or ledger voucher.

---

### Module 6: Budgeting & Variance Analysis (`/accounting/reports/budget-variance`)
* **Underlying Engine:** `Nml\FinCore\Services\BudgetEngine`
* **Purpose:** Monthly and annual cost center control.
* **Key Components:**
  1. **Monthly Budget Setup Grid:**
     * Table of Revenue and Expense accounts across 12 months.
     * Direct in-cell budget amount entry.
  2. **Variance Analysis Report:**
     * Columns: `Account Code`, `Account Name`, `Budgeted Amount`, `Actual Spent/Earned`, `Variance (Amount)`, `Variance (%)`.
     * Color-coded progress badges (Green = Under expense budget / Over revenue target; Red = Budget overrun).

---

### Module 7: Compliance Audit Trail Viewer (`/accounting/audit-logs`)
* **Underlying Engine:** `Nml\FinCore\Services\AuditLogService`
* **Purpose:** Regulatory compliance and immutable tracking.
* **Key Components:**
  1. **Timeline Log Table:**
     * Timestamp, User Name, Action (`created`, `updated`, `posted`, `voided`).
     * Target Journal Entry / Account.
     * JSON Diff viewer highlighting **Old Value vs New Value**.

---

### Module 8: Print-Ready PDF & Excel Engine (All Reports)
* **Purpose:** Generate professional, audit-compliant financial reports for management, tax authorities, and external auditors.
* **Formats Supported:**
  * Clean Printable HTML (with `@media print` styling already prepared in `styles.blade.php`).
  * Direct PDF download via `barryvdh/laravel-dompdf`.
  * Direct Excel (.xlsx / .csv) download via `maatwebsite/excel` or native fast CSV streaming.

---

## 📅 Recommended Development Sequence

```
Phase 1 (Completed):
  ├── Architecture & Dual-Mode UI Wrapper
  ├── ERP Auto-Posting Engine & Account Mappings
  ├── Overview Dashboard
  ├── Chart of Accounts Manager
  ├── Manual Journal Voucher Screen
  └── Basic Reports (Trial Balance, P&L, Balance Sheet, Ledger)

Phase 2 (Next Sprint - Advanced Operational UI):
  ├── Module 1: Bank Reconciliation UI
  ├── Module 2: Fiscal Periods & Year-Closing Wizard
  └── Module 4: Statement of Cash Flows UI

Phase 3 (Enterprise & Asset Management):
  ├── Module 3: Fixed Assets & Depreciation Engine UI
  ├── Module 5: Partner AR/AP Ageing Reports
  └── Module 6: Budget vs Actual Variance UI

Phase 4 (Compliance & Commercial Distribution):
  ├── Module 7: Audit Log Viewer
  ├── Module 8: One-click PDF & Excel Report Exports
  └── Commercial Licensing & Private Packagist Setup
```
