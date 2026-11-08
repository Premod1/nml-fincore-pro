# NML FinCore PRO (`nml/fincore-pro`)

**Commercial UI Starter Kit & Zero-Accounting-Knowledge ERP Posting Rules Engine** for [`nml/fincore`](https://github.com/Premod1/nml-fincore).

> **Note:** This is a commercial/proprietary package. Host this in a **Private GitHub Repository** and distribute access via Composer (Satis / Private Packagist / Anystack / Polar.sh / GitHub Sponsors).

---

## ✨ Key Commercial Features

1. **Dual-Mode Bootstrap/Blade UI Wrapper:**
   * **Embedded Mode (`mode => 'embedded'`):** Renders all accounting views directly inside your existing ERP's Sidebar & Header (`@extends(config('fincore-pro.erp_layout'))`).
   * **Standalone Mode (`mode => 'standalone'`):** Launches a dedicated full-screen Accounting Portal at `/accounting`.
   * **Scoped CSS Variables (`.fincore-scope`):** Zero CSS collisions with your existing Bootstrap 4/5 or custom ERP styles.

2. **Zero-Accounting-Knowledge ERP Event Bridge (`FinCorePro::recordErpEvent`):**
   * Developers don't need to know Debits, Credits, VAT splits, or COGS inventory journals!
   * Simply trigger business events from your ERP (`credit_sale`, `cash_sale`, `customer_payment`, `credit_purchase`, `supplier_payment`, `expense_payment`).
   * Accountants can dynamically re-map which General Ledger accounts are debited/credited directly from the **ERP Auto-Posting Rules UI** (`/accounting/mappings`) without touching code.

3. **Complete Ready-Made Financial Screens:**
   * Financial Overview KPI Dashboard (`/accounting`)
   * Chart of Accounts Manager with one-click standard seeding (`/accounting/accounts`)
   * Real-time Balanced Manual Journal Voucher Creator (`/accounting/journals/create`)
   * Interactive Trial Balance with Ledger Drill-down (`/accounting/reports/trial-balance`)
   * Profit & Loss Statement (`/accounting/reports/income-statement`)
   * Balance Sheet (`/accounting/reports/balance-sheet`)
   * General Ledger Explorer (`/accounting/reports/general-ledger`)

---

## 🚀 Quick Installation in Your Company ERP (Local Testing)

Add both local repositories to your ERP's `composer.json`:

```json
"repositories": [
    {
        "type": "path",
        "url": "../nml-fincore"
    },
    {
        "type": "path",
        "url": "../nml-fincore-pro"
    }
],
"require": {
    "nml/fincore": "*",
    "nml/fincore-pro": "*"
}
```

Then run:

```bash
composer require nml/fincore-pro
php artisan migrate
php artisan vendor:publish --tag=fincore-pro-config
```

Configure your ERP layout in `config/fincore-pro.php` (or `.env`):

```env
FINCORE_PRO_MODE=embedded
FINCORE_PRO_LAYOUT=layouts.app
FINCORE_PRO_SECTION=content
FINCORE_PRO_COLOR=#2563eb
```

---

## ⚡ Recording ERP Transactions (Zero Accounting Knowledge Needed)

In your `InvoiceController` or `SaleController`:

```php
use Nml\FinCorePro\Facades\FinCorePro;

// Example 1: Credit Sale (Invoice with VAT + Inventory COGS)
FinCorePro::recordErpEvent('credit_sale', [
    'net_amount'   => $invoice->sub_total,    // e.g. 10,000.00 -> Credited to Sales Revenue (4000)
    'tax_amount'   => $invoice->vat_amount,   // e.g.  1,800.00 -> Credited to VAT Payable (2300)
    'total_amount' => $invoice->grand_total,  // e.g. 11,800.00 -> Debited to Accounts Receivable (1200)
    'cogs_amount'  => $invoice->total_cost,   // e.g.  6,500.00 -> Debited to COGS (5000) & Credited to Inventory (1300)
    'reference'    => $invoice,               // Polymorphic link to your ERP Invoice model
    'partner'      => $invoice->customer,     // Polymorphic link to Customer for AR Ageing
    'description'  => "Invoice #{$invoice->invoice_no}",
]);
```
