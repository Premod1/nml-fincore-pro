# NML FinCore PRO (`nml/fincore-pro`)

**Commercial UI Starter Kit & Zero-Accounting-Knowledge ERP Posting Rules Engine** for [`nml/fincore`](https://github.com/Premod1/nml-fincore).

> **License:** Proprietary / Commercial. Host this in a **Private GitHub Repository** and distribute access via Composer (e.g. Satis, Private Packagist, Polar.sh, or GitHub Sponsors).

---

## ⚡ Quick Start: Running the Built-in UI Preview (Standalone)

You can launch and test the full accounting UI locally without embedding it into an ERP:

```bash
cd /home/pramod/project/pramod/nml-fincore-pro
php preview 8085
# Or via composer:
composer serve
```

Open your browser at: **`http://127.0.0.1:8085/accounting`**

*(This automatically boots a local SQLite database, runs all 14 Core + Pro migrations, and seeds the standard Chart of Accounts & ERP Posting Rules).*

---

## 📖 Developer Guide: Integrating into Your Existing Laravel ERP

### 1. Add Local Package to Host ERP's `composer.json`

Add repository path references in your host ERP `composer.json`:

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
    "nml/fincore": "*@dev|^1.0",
    "nml/fincore-pro": "*@dev|^1.0"
}
```

Then install and publish assets:

```bash
composer require nml/fincore-pro
php artisan migrate
php artisan vendor:publish --tag=fincore-pro-config
```

### 2. Configure Host ERP Layout (`config/fincore-pro.php` or `.env`)

To embed FinCore PRO screens seamlessly inside your existing Blade + Bootstrap ERP sidebar/navbar without breaking existing styles:

```env
FINCORE_PRO_MODE=embedded
FINCORE_PRO_LAYOUT=layouts.master     # The Blade layout file of your ERP
FINCORE_PRO_SECTION=content           # The @yield section where page content is injected
FINCORE_PRO_COLOR=#2563eb             # Primary color matching your ERP brand
FINCORE_PRO_PREFIX=accounting         # Route prefix (e.g. your-erp.com/accounting)
```

### 3. Add to Host ERP Sidebar Navigation

Add a link in your ERP's sidebar menu pointing to FinCore PRO:

```blade
<li class="nav-item">
    <a href="{{ route('fincore.dashboard') }}" class="nav-link">
        <i class="nav-icon fas fa-calculator"></i>
        <p>Accounting</p>
    </a>
</li>
```

---

## 💡 Zero-Accounting-Knowledge ERP Event Bridge (`FinCorePro::recordErpEvent`)

Developers **never need to calculate Debits, Credits, Tax lines, or COGS entries in application controllers**. Simply fire business events with standard payloads:

### A. Credit Sale (Customer Invoice)
```php
use Nml\FinCorePro\Facades\FinCorePro;

FinCorePro::recordErpEvent('credit_sale', [
    'net_amount'   => $invoice->sub_total,    // Credited to Sales Revenue (4000)
    'tax_amount'   => $invoice->vat_amount,   // Credited to VAT Payable (2300)
    'total_amount' => $invoice->grand_total,  // Debited to Accounts Receivable (1200)
    'cogs_amount'  => $invoice->cost_total,   // Debited to COGS (5000) & Credited to Inventory (1300)
    'reference'    => $invoice,               // Polymorphic reference to ERP Invoice model
    'partner'      => $invoice->customer,     // Customer model (for AR Ageing)
    'description'  => "Invoice #{$invoice->invoice_number}",
]);
```

### B. Customer Payment Received
```php
FinCorePro::recordErpEvent('customer_payment', [
    'total_amount'    => $payment->amount,    // Debited to Bank/Cash (1100)
    'discount_amount' => $payment->discount,  // Debited to Sales Discount (6130 - optional)
    'settled_amount'  => $payment->settled,   // Credited to Accounts Receivable (1200)
    'reference'       => $payment,
    'description'     => "Payment for Invoice #{$invoice->invoice_number}",
]);
```

### C. Credit Purchase (Supplier GRN / Bill)
```php
FinCorePro::recordErpEvent('credit_purchase', [
    'net_amount'   => $grn->sub_total,        // Debited to Merchandise Inventory (1300)
    'tax_amount'   => $grn->vat_amount,       // Debited to Input VAT (2300)
    'total_amount' => $grn->grand_total,      // Credited to Accounts Payable (2000)
    'reference'    => $grn,
    'partner'      => $grn->supplier,         // Supplier model (for AP Ageing)
    'description'  => "GRN #{$grn->grn_number}",
]);
```

### D. Direct Operational Expense Payment
```php
FinCorePro::recordErpEvent('expense_payment', [
    'total_amount' => 15000.00,               // Debited to Operating Expense (6100)
    'reference'    => 'CEB-BILL-NOV-2026',
    'description'  => 'Electricity Bill Payment - Factory',
]);
```

> **Account Re-mapping:** Accountants can change which General Ledger accounts are targeted anytime from the **Accounting -> ERP Auto-Posting Rules** UI screen without requiring developer assistance or code changes!
