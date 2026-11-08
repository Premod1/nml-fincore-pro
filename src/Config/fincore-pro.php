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
    | Pre-configured business events so developers can record ERP transactions
    | without knowing debit/credit accounting rules. These can be customized
    | anytime from the Accounting -> ERP Mappings UI screen!
    |
    */
    'auto_post_erp_events' => env('FINCORE_PRO_AUTO_POST', true),

    'default_event_rules' => [
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
        'customer_payment' => [
            'name' => 'Customer Payment Received',
            'description' => 'Records payment received from a customer against outstanding receivables.',
            'jv_type' => 'cash_receipt',
            'lines' => [
                ['role' => 'bank_or_cash', 'type' => 'debit',  'amount_key' => 'total_amount', 'default_code' => '1100', 'label' => 'Bank / Cash Account'],
                ['role' => 'discount',     'type' => 'debit',  'amount_key' => 'discount_amount', 'default_code' => '6130', 'label' => 'Sales Discounts Allowed', 'optional' => true],
                ['role' => 'receivable',   'type' => 'credit', 'amount_key' => 'settled_amount',  'default_code' => '1200', 'label' => 'Accounts Receivable (A/R)'],
            ],
        ],
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
        'supplier_payment' => [
            'name' => 'Supplier Payment Made',
            'description' => 'Records payment made to a supplier to settle Accounts Payable.',
            'jv_type' => 'cash_payment',
            'lines' => [
                ['role' => 'payable',      'type' => 'debit',  'amount_key' => 'total_amount', 'default_code' => '2000', 'label' => 'Accounts Payable (A/P)'],
                ['role' => 'bank_or_cash', 'type' => 'credit', 'amount_key' => 'total_amount', 'default_code' => '1100', 'label' => 'Bank / Cash Account'],
            ],
        ],
        'expense_payment' => [
            'name' => 'Direct Expense Payment',
            'description' => 'Records utility, rent, salary, or general operational expense payment.',
            'jv_type' => 'cash_payment',
            'lines' => [
                ['role' => 'expense',      'type' => 'debit',  'amount_key' => 'total_amount', 'default_code' => '6100', 'label' => 'Operating Expense Account'],
                ['role' => 'bank_or_cash', 'type' => 'credit', 'amount_key' => 'total_amount', 'default_code' => '1000', 'label' => 'Cash / Bank Account'],
            ],
        ],
    ],
];
