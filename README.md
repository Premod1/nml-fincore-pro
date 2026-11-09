# NML FinCore PRO (`nml/fincore-pro`)

**Commercial UI Starter Kit & Zero-Accounting-Knowledge ERP Posting Rules Engine** for [`nml/fincore`](https://github.com/Premod1/nml-fincore).

> **License:** Proprietary / Commercial. Distributed to GitHub Sponsors (**$29 Tier**) with automated private repository access and lifetime updates.

---

## 🚀 How to Install & Connect to Your Host Laravel ERP (Post-Sponsorship)

Once you sponsor **$29** on [GitHub Sponsors (Premod1)](https://github.com/sponsors/Premod1), you are instantly invited as a collaborator to the private `Premod1/nml-fincore-pro` repository.

Follow these 3 simple steps to integrate it into any Laravel ERP, POS, or SaaS application:

### Step 1: Configure Private VCS in your ERP's `composer.json`

Open your host ERP's `composer.json` file and register the repository:

```json
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/Premod1/nml-fincore-pro.git"
    }
],
"require": {
    "nml/fincore-pro": "^1.0"
}
```

Then run the installation command:

```bash
composer require nml/fincore-pro
```

*(Note: Composer will authenticate using your GitHub account or Personal Access Token if prompted).*

---

### Step 2: Run Migrations & Publish Configuration

Execute the database migrations to create the core accounting tables and ERP posting rules:

```bash
# 1. Run migrations
php artisan migrate

# 2. Publish FinCore PRO configuration file
php artisan vendor:publish --tag=fincore-pro-config
```

---

### Step 3: Embed inside your Existing ERP Layout (`config/fincore-pro.php`)

FinCore PRO is built with **Scoped CSS Variables (`.fincore-scope`)**, meaning it will **never break your existing Bootstrap 4, Bootstrap 5, or custom ERP theme styles**.

In your `.env` or `config/fincore-pro.php`:

```env
# Mode: 'embedded' seamlessly renders within your ERP's master layout
FINCORE_PRO_MODE=embedded

# The Blade layout file of your ERP (e.g. layouts.app, layouts.admin, layouts.master)
FINCORE_PRO_LAYOUT=layouts.master

# The @yield section where main page content is injected
FINCORE_PRO_SECTION=content

# Brand color matching your ERP
FINCORE_PRO_COLOR=#0077c5

# Route prefix (e.g. your-erp.com/accounting)
FINCORE_PRO_PREFIX=accounting
```

---

### Step 4: Add to your ERP's Sidebar Menu

Simply add one link in your ERP's sidebar blade view:

```blade
<li class="nav-item">
    <a href="{{ route('fincore.dashboard') }}" class="nav-link">
        <i class="nav-icon fas fa-calculator"></i>
        <span>Accounting &amp; Finance</span>
    </a>
</li>
```

Now, clicking **"Accounting & Finance"** opens the complete QuickBooks-style Accounting Suite right inside your ERP!

---

## ⚡ Zero-Accounting-Knowledge ERP Event Bridge (`FinCorePro::recordErpEvent`)

Developers **never need to know debits, credits, tax splits, or inventory journals**. Simply fire business events with standard invoice or payment numbers directly from your controllers:

### 1. Credit Sale (Customer Invoice)
```php
use Nml\FinCorePro\Facades\FinCorePro;

// In your InvoiceController@store or POS checkout:
FinCorePro::recordErpEvent('credit_sale', [
    'net_amount'   => $invoice->sub_total,    // Credited to Sales Revenue (4000)
    'tax_amount'   => $invoice->vat_amount,   // Credited to VAT Payable (2300)
    'total_amount' => $invoice->grand_total,  // Debited to Accounts Receivable (1200)
    'cogs_amount'  => $invoice->cost_total,   // Debited to COGS (5000) & Credited to Inventory (1300)
    'reference'    => $invoice,               // Polymorphic reference to your Invoice model
    'partner'      => $invoice->customer,     // Customer model (for AR Ageing)
    'description'  => "Invoice #{$invoice->invoice_number}",
]);
```

### 2. Customer Payment Received
```php
FinCorePro::recordErpEvent('customer_payment', [
    'total_amount'    => $payment->amount,    // Debited to Bank/Cash (1100)
    'discount_amount' => $payment->discount,  // Debited to Sales Discount (6130)
    'settled_amount'  => $payment->settled,   // Credited to Accounts Receivable (1200)
    'reference'       => $payment,
    'description'     => "Payment for Invoice #{$invoice->invoice_number}",
]);
```

### 3. Credit Purchase (Supplier GRN / Bill)
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

### 4. Supplier Payment Made
```php
FinCorePro::recordErpEvent('supplier_payment', [
    'total_amount' => $billPayment->amount,   // Debited to Accounts Payable (2000) & Credited to Bank (1100)
    'reference'    => $billPayment,
    'description'  => "Settlement of Bill #{$bill->bill_number}",
]);
```

### 5. Direct Operational Expense Payment
```php
FinCorePro::recordErpEvent('expense_payment', [
    'total_amount' => 15000.00,               // Debited to Operating Expense (6100) & Credited to Cash (1000)
    'reference'    => 'CEB-BILL-NOV-2026',
    'description'  => 'Electricity Bill Payment - Office',
]);
```

> **Dynamic Re-Mapping:** If your accountant ever wants to change which accounts are debited or credited, they can do so directly from **Accounting -> Settings -> ERP Posting Rules** without touching any code!

---

## 🧪 Local Testing & Standalone Preview (Zero ERP Dependency)

To preview and test the complete UI on your local machine without an ERP:

```bash
cd nml-fincore-pro
php preview 8085
# Or via composer:
composer serve
```

Visit: **`http://127.0.0.1:8085/accounting`**
