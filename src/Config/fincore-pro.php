<?php

return [
    /*
    |--------------------------------------------------------------------------
    | UI Integration Mode
    |--------------------------------------------------------------------------
    |
    | 'embedded': Renders all Accounting screens directly inside your existing
    |             ERP layout (@extends your ERP's master Blade file).
    | 'standalone': Renders a full-screen dedicated Accounting Portal with its
    |               own sidebar and top navigation.
    |
    */
    'mode' => env('FINCORE_PRO_MODE', 'embedded'),

    /*
    |--------------------------------------------------------------------------
    | Host ERP Layout Settings (Used when mode = 'embedded')
    |--------------------------------------------------------------------------
    |
    | Specify the Blade layout file of your existing Bootstrap/Blade ERP and
    | the @yield() section name where main page content is rendered.
    |
    */
    'erp_layout' => env('FINCORE_PRO_LAYOUT', 'layouts.app'),
    'erp_section' => env('FINCORE_PRO_SECTION', 'content'),

    /*
    |--------------------------------------------------------------------------
    | Routing Configuration
    |--------------------------------------------------------------------------
    */
    'route_prefix' => env('FINCORE_PRO_PREFIX', 'accounting'),
    'middleware' => ['web'], // Add 'auth' in production ERP via config/fincore-pro.php

    /*
    |--------------------------------------------------------------------------
    | Branding & Theme Customization
    |--------------------------------------------------------------------------
    */
    'theme' => [
        'brand_name' => env('FINCORE_PRO_BRAND', 'FinCore Accounting'),
        'primary_color' => env('FINCORE_PRO_COLOR', '#2563eb'),
        'show_sub_navbar' => true,
        'back_to_erp_url' => '/',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default ERP Event Posting Rules
    |--------------------------------------------------------------------------
    |
    | Exhaustive suite of 14 standard real-world business transaction events
    | covering the full double-entry accounting lifecycle for modern ERPs.
    |
    */
    'auto_post_erp_events' => env('FINCORE_PRO_AUTO_POST', true),

    'default_event_rules' => [
        // 1. Sales & Invoicing
        'credit_sale' => [
            'name' => 'Credit Sale (Customer Invoice)',
            'description' => 'Records customer invoice on credit with optional tax and COGS/inventory reduction.',
            'jv_type' => 'sales',
            'lines' => [
                ['role' => 'receivable', 'type' => 'debit',  'amount_key' => 'total_amount', 'default_code' => '1200', 'label' => 'Accounts Receivable (A/R)'],
                ['role' => 'revenue',    'type' => 'credit', 'amount_key' => 'net_amount',   'default_code' => '4000', 'label' => 'Sales Revenue'],
                ['role' => 'tax',        'type' => 'credit', 'amount_key' => 'tax_amount',   'default_code' => '2300', 'label' => 'Sales Tax (VAT) Payable', 'optional' => true],
                ['role' => 'cogs',       'type' => 'debit',  'amount_key' => 'cogs_amount',  'default_code' => '5000', 'label' => 'Cost of Goods Sold (COGS)', 'optional' => true],
                ['role' => 'inventory',  'type' => 'credit', 'amount_key' => 'cogs_amount',  'default_code' => '1300', 'label' => 'Merchandise Inventory', 'optional' => true],
            ],
        ],
        'cash_sale' => [
            'name' => 'Cash / POS Sale',
            'description' => 'Records immediate cash/bank sale with optional tax and COGS/inventory reduction.',
            'jv_type' => 'cash_receipt',
            'lines' => [
                ['role' => 'cash',      'type' => 'debit',  'amount_key' => 'total_amount', 'default_code' => '1000', 'label' => 'Cash on Hand / Drawer'],
                ['role' => 'revenue',   'type' => 'credit', 'amount_key' => 'net_amount',   'default_code' => '4000', 'label' => 'Sales Revenue'],
                ['role' => 'tax',       'type' => 'credit', 'amount_key' => 'tax_amount',   'default_code' => '2300', 'label' => 'Sales Tax (VAT) Payable', 'optional' => true],
                ['role' => 'cogs',      'type' => 'debit',  'amount_key' => 'cogs_amount',  'default_code' => '5000', 'label' => 'Cost of Goods Sold (COGS)', 'optional' => true],
                ['role' => 'inventory', 'type' => 'credit', 'amount_key' => 'cogs_amount',  'default_code' => '1300', 'label' => 'Merchandise Inventory', 'optional' => true],
            ],
        ],
        'sales_return' => [
            'name' => 'Sales Return (Credit Note)',
            'description' => 'Records customer goods return, restores merchandise inventory and reverses sales revenue/debtors.',
            'jv_type' => 'sales',
            'lines' => [
                ['role' => 'revenue',    'type' => 'debit',  'amount_key' => 'net_amount',   'default_code' => '4000', 'label' => 'Sales Revenue (Reversal)'],
                ['role' => 'tax',        'type' => 'debit',  'amount_key' => 'tax_amount',   'default_code' => '2300', 'label' => 'Sales Tax (VAT) Payable Reversal', 'optional' => true],
                ['role' => 'receivable', 'type' => 'credit', 'amount_key' => 'total_amount', 'default_code' => '1200', 'label' => 'Accounts Receivable (A/R)'],
                ['role' => 'inventory',  'type' => 'debit',  'amount_key' => 'cogs_amount',  'default_code' => '1300', 'label' => 'Merchandise Inventory Restored', 'optional' => true],
                ['role' => 'cogs',       'type' => 'credit', 'amount_key' => 'cogs_amount',  'default_code' => '5000', 'label' => 'Cost of Goods Sold (COGS Reversal)', 'optional' => true],
            ],
        ],

        // 2. Customer Receipts & Advances
        'customer_payment' => [
            'name' => 'Customer Payment Received',
            'description' => 'Records payment received from a customer against outstanding receivables with optional discount.',
            'jv_type' => 'cash_receipt',
            'lines' => [
                ['role' => 'bank_or_cash', 'type' => 'debit',  'amount_key' => 'total_amount', 'default_code' => '1100', 'label' => 'Bank / Cash Account'],
                ['role' => 'discount',     'type' => 'debit',  'amount_key' => 'discount_amount', 'default_code' => '6130', 'label' => 'Sales Discounts Allowed', 'optional' => true],
                ['role' => 'receivable',   'type' => 'credit', 'amount_key' => 'settled_amount',  'default_code' => '1200', 'label' => 'Accounts Receivable (A/R)'],
            ],
        ],
        'customer_advance' => [
            'name' => 'Customer Advance / Down Payment',
            'description' => 'Records advance deposit received from a customer before invoicing.',
            'jv_type' => 'cash_receipt',
            'lines' => [
                ['role' => 'bank_or_cash', 'type' => 'debit',  'amount_key' => 'total_amount', 'default_code' => '1100', 'label' => 'Bank / Cash Account'],
                ['role' => 'advance',      'type' => 'credit', 'amount_key' => 'total_amount', 'default_code' => '2100', 'label' => 'Customer Advance / Unearned Revenue'],
            ],
        ],

        // 3. Purchases & Supplier Settlements
        'credit_purchase' => [
            'name' => 'Credit Purchase (Supplier GRN / Bill)',
            'description' => 'Records inventory/goods purchased on credit from a supplier.',
            'jv_type' => 'purchases',
            'lines' => [
                ['role' => 'inventory', 'type' => 'debit',  'amount_key' => 'net_amount',   'default_code' => '1300', 'label' => 'Merchandise Inventory'],
                ['role' => 'tax',       'type' => 'debit',  'amount_key' => 'tax_amount',   'default_code' => '2300', 'label' => 'Input VAT / Tax', 'optional' => true],
                ['role' => 'payable',   'type' => 'credit', 'amount_key' => 'total_amount', 'default_code' => '2000', 'label' => 'Accounts Payable (A/P)'],
            ],
        ],
        'purchase_return' => [
            'name' => 'Purchase Return (Debit Note)',
            'description' => 'Records goods returned to supplier and reduces Accounts Payable.',
            'jv_type' => 'purchases',
            'lines' => [
                ['role' => 'payable',   'type' => 'debit',  'amount_key' => 'total_amount', 'default_code' => '2000', 'label' => 'Accounts Payable (A/P)'],
                ['role' => 'inventory', 'type' => 'credit', 'amount_key' => 'net_amount',   'default_code' => '1300', 'label' => 'Merchandise Inventory'],
                ['role' => 'tax',       'type' => 'credit', 'amount_key' => 'tax_amount',   'default_code' => '2300', 'label' => 'Input VAT / Tax (Reversal)', 'optional' => true],
            ],
        ],
        'supplier_payment' => [
            'name' => 'Supplier Payment Made',
            'description' => 'Records payment made to a supplier to settle Accounts Payable.',
            'jv_type' => 'cash_payment',
            'lines' => [
                ['role' => 'payable',      'type' => 'debit',  'amount_key' => 'total_amount', 'default_code' => '2000', 'label' => 'Accounts Payable (A/P)'],
                ['role' => 'bank_or_cash', 'type' => 'credit', 'amount_key' => 'total_amount', 'default_code' => '1100', 'label' => 'Bank / Cash Account'],
            ],
        ],

        // 4. Banking & Cash Management
        'bank_transfer' => [
            'name' => 'Internal Cash / Bank Transfer',
            'description' => 'Transfers funds between bank accounts, or deposits cash into bank.',
            'jv_type' => 'general',
            'lines' => [
                ['role' => 'destination_bank', 'type' => 'debit',  'amount_key' => 'total_amount', 'default_code' => '1100', 'label' => 'Receiving Bank / Cash Account'],
                ['role' => 'source_bank',      'type' => 'credit', 'amount_key' => 'total_amount', 'default_code' => '1000', 'label' => 'Transferring Cash / Bank Account'],
            ],
        ],

        // 5. Operating Expenses & Payroll
        'expense_payment' => [
            'name' => 'Direct Expense Payment',
            'description' => 'Records utility, rent, office supplies or general operational expense payment.',
            'jv_type' => 'cash_payment',
            'lines' => [
                ['role' => 'expense',      'type' => 'debit',  'amount_key' => 'total_amount', 'default_code' => '6100', 'label' => 'Operating Expense Account'],
                ['role' => 'bank_or_cash', 'type' => 'credit', 'amount_key' => 'total_amount', 'default_code' => '1000', 'label' => 'Cash / Bank Account'],
            ],
        ],
        'payroll_salary' => [
            'name' => 'Monthly Staff Payroll Journal',
            'description' => 'Records gross salary expense with EPF/ETF and net salary liabilities.',
            'jv_type' => 'general',
            'lines' => [
                ['role' => 'salary_expense', 'type' => 'debit',  'amount_key' => 'gross_amount', 'default_code' => '6300', 'label' => 'Salaries & Wages Expense'],
                ['role' => 'payroll_tax',    'type' => 'credit', 'amount_key' => 'epf_etf_amount', 'default_code' => '2200', 'label' => 'Payroll & EPF/ETF Payable', 'optional' => true],
                ['role' => 'net_payable',    'type' => 'credit', 'amount_key' => 'net_amount',     'default_code' => '2200', 'label' => 'Net Salaries Payable / Bank'],
            ],
        ],

        // 6. Inventory Adjustments & Asset Management
        'inventory_adjustment_loss' => [
            'name' => 'Inventory Damaged / Loss Write-Off',
            'description' => 'Writes off damaged, expired, or missing stock to inventory loss expense.',
            'jv_type' => 'general',
            'lines' => [
                ['role' => 'loss_expense', 'type' => 'debit',  'amount_key' => 'total_amount', 'default_code' => '6650', 'label' => 'Inventory Damaged & Write-off Loss'],
                ['role' => 'inventory',    'type' => 'credit', 'amount_key' => 'total_amount', 'default_code' => '1300', 'label' => 'Merchandise Inventory Asset'],
            ],
        ],
        'asset_purchase' => [
            'name' => 'Capital Fixed Asset Acquisition',
            'description' => 'Records purchase of machinery, vehicles, computers or office equipment.',
            'jv_type' => 'purchases',
            'lines' => [
                ['role' => 'fixed_asset',  'type' => 'debit',  'amount_key' => 'total_amount', 'default_code' => '1500', 'label' => 'Fixed Assets Account'],
                ['role' => 'bank_or_cash', 'type' => 'credit', 'amount_key' => 'total_amount', 'default_code' => '1100', 'label' => 'Bank Account / Creditor'],
            ],
        ],
        'customer_bad_debt' => [
            'name' => 'Customer Bad Debt Write-Off',
            'description' => 'Writes off uncollectible customer receivables to bad debts expense.',
            'jv_type' => 'general',
            'lines' => [
                ['role' => 'bad_debt_expense', 'type' => 'debit',  'amount_key' => 'total_amount', 'default_code' => '6600', 'label' => 'Bad Debts Written Off Expense'],
                ['role' => 'receivable',       'type' => 'credit', 'amount_key' => 'total_amount', 'default_code' => '1200', 'label' => 'Accounts Receivable (A/R)'],
            ],
        ],
    ],
];
