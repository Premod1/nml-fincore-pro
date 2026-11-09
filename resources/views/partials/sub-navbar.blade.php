<div class="fc-subnav">
    {{-- QBO Signature '+ New' Global Action Drawer --}}
    <div class="fc-dropdown me-2">
        <button type="button" class="btn fc-btn-new">
            <span style="font-size: 1.1rem; line-height: 1;">+</span> New
        </button>
        <div class="fc-dropdown-menu" style="min-width: 270px;">
            <div class="px-3 py-1 text-muted fw-bold" style="font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.05em;">
                Quick Accounting Actions
            </div>
            <a href="{{ route('fincore.journals.create') }}" class="fc-dropdown-item">
                📝 <strong>Journal Entry</strong> <small class="text-muted d-block">Manual debit/credit voucher</small>
            </a>
            <div class="fc-dropdown-divider"></div>
            <a href="{{ route('fincore.accounts.index') }}" class="fc-dropdown-item">
                🗂️ <strong>New Ledger Account</strong> <small class="text-muted d-block">Add to Chart of Accounts</small>
            </a>
            <div class="fc-dropdown-divider"></div>
            <a href="{{ route('fincore.mappings.index') }}" class="fc-dropdown-item">
                ⚡ <strong>ERP Posting Rules</strong> <small class="text-muted d-block">Automate sales &amp; bills</small>
            </a>
        </div>
    </div>

    {{-- 1. Dashboard --}}
    <a href="{{ route('fincore.dashboard') }}"
       class="fc-subnav-link {{ request()->routeIs('fincore.dashboard') ? 'active' : '' }}">
        Dashboard
    </a>

    {{-- 2. Banking & Cash --}}
    <div class="fc-dropdown">
        <button type="button" class="fc-dropdown-toggle fc-subnav-link">
            Banking ▾
        </button>
        <div class="fc-dropdown-menu" style="min-width: 240px;">
            <div class="px-3 py-1 text-muted fw-bold" style="font-size: 0.68rem; text-transform: uppercase;">Cash &amp; Bank</div>
            <a href="{{ route('fincore.accounts.index', ['type' => 'asset']) }}" class="fc-dropdown-item">
                Bank &amp; Cash Accounts
            </a>
            <a href="#" class="fc-dropdown-item text-muted disabled">
                Bank Reconciliation <span class="badge bg-light text-muted ms-1" style="font-size:0.6rem;">Soon</span>
            </a>
            <a href="#" class="fc-dropdown-item text-muted disabled">
                Internal Bank Transfers <span class="badge bg-light text-muted ms-1" style="font-size:0.6rem;">Soon</span>
            </a>
        </div>
    </div>

    {{-- 3. Sales & Customers --}}
    <div class="fc-dropdown">
        <button type="button" class="fc-dropdown-toggle fc-subnav-link">
            Sales &amp; Debtors ▾
        </button>
        <div class="fc-dropdown-menu" style="min-width: 250px;">
            <div class="px-3 py-1 text-muted fw-bold" style="font-size: 0.68rem; text-transform: uppercase;">Customer Receivables</div>
            <a href="{{ route('fincore.journals.index', ['search' => 'credit_sale']) }}" class="fc-dropdown-item">
                Customer Invoices (A/R)
            </a>
            <a href="{{ route('fincore.journals.index', ['search' => 'customer_payment']) }}" class="fc-dropdown-item">
                Customer Receipts &amp; Payments
            </a>
            <div class="fc-dropdown-divider"></div>
            <a href="#" class="fc-dropdown-item text-muted disabled">
                A/R Ageing Analysis <span class="badge bg-light text-muted ms-1" style="font-size:0.6rem;">Soon</span>
            </a>
            <a href="#" class="fc-dropdown-item text-muted disabled">
                Credit Notes &amp; Returns <span class="badge bg-light text-muted ms-1" style="font-size:0.6rem;">Soon</span>
            </a>
        </div>
    </div>

    {{-- 4. Expenses & Suppliers --}}
    <div class="fc-dropdown">
        <button type="button" class="fc-dropdown-toggle fc-subnav-link">
            Expenses &amp; Payables ▾
        </button>
        <div class="fc-dropdown-menu" style="min-width: 250px;">
            <div class="px-3 py-1 text-muted fw-bold" style="font-size: 0.68rem; text-transform: uppercase;">Supplier Payables</div>
            <a href="{{ route('fincore.journals.index', ['search' => 'credit_purchase']) }}" class="fc-dropdown-item">
                Supplier Bills &amp; GRNs (A/P)
            </a>
            <a href="{{ route('fincore.journals.index', ['search' => 'supplier_payment']) }}" class="fc-dropdown-item">
                Supplier Payments Made
            </a>
            <a href="{{ route('fincore.journals.index', ['search' => 'expense_payment']) }}" class="fc-dropdown-item">
                Operating Expense Payments
            </a>
            <div class="fc-dropdown-divider"></div>
            <a href="#" class="fc-dropdown-item text-muted disabled">
                A/P Ageing Analysis <span class="badge bg-light text-muted ms-1" style="font-size:0.6rem;">Soon</span>
            </a>
            <a href="#" class="fc-dropdown-item text-muted disabled">
                Staff Payroll Vouchers <span class="badge bg-light text-muted ms-1" style="font-size:0.6rem;">Soon</span>
            </a>
        </div>
    </div>

    {{-- 5. Accounting & Assets --}}
    <div class="fc-dropdown">
        <button type="button" class="fc-dropdown-toggle fc-subnav-link {{ request()->routeIs('fincore.accounts.*', 'fincore.journals.*') ? 'active' : '' }}">
            Accounting ▾
        </button>
        <div class="fc-dropdown-menu" style="min-width: 260px;">
            <div class="px-3 py-1 text-muted fw-bold" style="font-size: 0.68rem; text-transform: uppercase;">General Ledger</div>
            <a href="{{ route('fincore.accounts.index') }}" class="fc-dropdown-item">
                🗂️ Chart of Accounts
            </a>
            <a href="{{ route('fincore.journals.index') }}" class="fc-dropdown-item">
                📝 Journal Entries (Vouchers)
            </a>
            <a href="{{ route('fincore.journals.create') }}" class="fc-dropdown-item">
                + New Manual Journal Entry
            </a>
            <div class="fc-dropdown-divider"></div>
            <div class="px-3 py-1 text-muted fw-bold" style="font-size: 0.68rem; text-transform: uppercase;">Asset &amp; Period Control</div>
            <a href="#" class="fc-dropdown-item text-muted disabled">
                Fixed Assets &amp; Depreciation <span class="badge bg-light text-muted ms-1" style="font-size:0.6rem;">Soon</span>
            </a>
            <a href="#" class="fc-dropdown-item text-muted disabled">
                Close Fiscal Periods / Year <span class="badge bg-light text-muted ms-1" style="font-size:0.6rem;">Soon</span>
            </a>
            <a href="#" class="fc-dropdown-item text-muted disabled">
                Budgeting &amp; Variance <span class="badge bg-light text-muted ms-1" style="font-size:0.6rem;">Soon</span>
            </a>
        </div>
    </div>

    {{-- 6. Financial Reports --}}
    <div class="fc-dropdown">
        <button type="button" class="fc-dropdown-toggle fc-subnav-link {{ request()->routeIs('fincore.reports.*') ? 'active' : '' }}">
            Reports ▾
        </button>
        <div class="fc-dropdown-menu" style="min-width: 250px;">
            <div class="px-3 py-1 text-muted fw-bold" style="font-size: 0.68rem; text-transform: uppercase;">Standard Financials</div>
            <a href="{{ route('fincore.reports.trial-balance') }}" class="fc-dropdown-item">
                ⚖️ Trial Balance
            </a>
            <a href="{{ route('fincore.reports.income-statement') }}" class="fc-dropdown-item">
                📊 Profit and Loss (P&amp;L)
            </a>
            <a href="{{ route('fincore.reports.balance-sheet') }}" class="fc-dropdown-item">
                🏛️ Balance Sheet
            </a>
            <a href="{{ route('fincore.reports.general-ledger') }}" class="fc-dropdown-item">
                📖 General Ledger Statement
            </a>
            <div class="fc-dropdown-divider"></div>
            <div class="px-3 py-1 text-muted fw-bold" style="font-size: 0.68rem; text-transform: uppercase;">Audit &amp; Compliance</div>
            <a href="#" class="fc-dropdown-item text-muted disabled">
                Statement of Cash Flows <span class="badge bg-light text-muted ms-1" style="font-size:0.6rem;">Soon</span>
            </a>
            <a href="#" class="fc-dropdown-item text-muted disabled">
                VAT / Tax Schedules <span class="badge bg-light text-muted ms-1" style="font-size:0.6rem;">Soon</span>
            </a>
        </div>
    </div>

    {{-- 7. Settings (Right Aligned) --}}
    <a href="{{ route('fincore.settings.index') }}"
       class="fc-subnav-link ms-auto {{ request()->routeIs('fincore.settings.*') ? 'active' : '' }}">
        ⚙️ Settings
    </a>
</div>
