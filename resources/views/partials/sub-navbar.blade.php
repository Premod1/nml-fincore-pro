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
            <a href="{{ route('fincore.receipts.scanner') }}" class="fc-dropdown-item">
                🤖 <strong>AI Receipt &amp; Invoice OCR</strong> <small class="text-muted d-block">Scan PDF or photo to draft bill</small>
            </a>
            <div class="fc-dropdown-divider"></div>
            <a href="{{ route('fincore.accounts.index') }}" class="fc-dropdown-item">
                🗂️ <strong>New Ledger Account</strong> <small class="text-muted d-block">Add to Chart of Accounts</small>
            </a>
            <div class="fc-dropdown-divider"></div>
            <a href="{{ route('fincore.assets.index') }}" class="fc-dropdown-item">
                🏢 <strong>Register Fixed Asset</strong> <small class="text-muted d-block">Capital equipment &amp; depreciation</small>
            </a>
            <div class="fc-dropdown-divider"></div>
            <a href="{{ route('fincore.banking.reconciliations.index') }}" class="fc-dropdown-item">
                🏦 <strong>Bank Reconciliation</strong> <small class="text-muted d-block">Match ledger against statement</small>
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
        <button type="button" class="fc-dropdown-toggle fc-subnav-link {{ request()->routeIs('fincore.banking.*') ? 'active' : '' }}">
            Banking ▾
        </button>
        <div class="fc-dropdown-menu" style="min-width: 240px;">
            <div class="px-3 py-1 text-muted fw-bold" style="font-size: 0.68rem; text-transform: uppercase;">Cash &amp; Bank</div>
            <a href="{{ route('fincore.accounts.index', ['type' => 'asset']) }}" class="fc-dropdown-item">
                Bank &amp; Cash Accounts
            </a>
            <a href="{{ route('fincore.banking.reconciliations.index') }}" class="fc-dropdown-item">
                🏦 Bank Reconciliation
            </a>
        </div>
    </div>

    {{-- 3. Sales & Customers --}}
    <div class="fc-dropdown">
        <button type="button" class="fc-dropdown-toggle fc-subnav-link {{ request()->routeIs('fincore.reports.ar-ageing') ? 'active' : '' }}">
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
            <a href="{{ route('fincore.reports.ar-ageing') }}" class="fc-dropdown-item">
                ⏳ A/R Ageing Analysis (Debtors)
            </a>
        </div>
    </div>

    {{-- 4. Expenses & Suppliers --}}
    <div class="fc-dropdown">
        <button type="button" class="fc-dropdown-toggle fc-subnav-link {{ request()->routeIs('fincore.reports.ap-ageing', 'fincore.receipts.*') ? 'active' : '' }}">
            Expenses &amp; Payables ▾
        </button>
        <div class="fc-dropdown-menu" style="min-width: 250px;">
            <div class="px-3 py-1 text-muted fw-bold" style="font-size: 0.68rem; text-transform: uppercase;">Supplier Payables</div>
            <a href="{{ route('fincore.receipts.scanner') }}" class="fc-dropdown-item">
                🤖 AI Receipt &amp; Invoice Scanner
            </a>
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
            <a href="{{ route('fincore.reports.ap-ageing') }}" class="fc-dropdown-item">
                ⏳ A/P Ageing Analysis (Creditors)
            </a>
        </div>
    </div>

    {{-- 5. Accounting & Assets --}}
    <div class="fc-dropdown">
        <button type="button" class="fc-dropdown-toggle fc-subnav-link {{ request()->routeIs('fincore.accounts.*', 'fincore.journals.*', 'fincore.assets.*', 'fincore.budgets.*') ? 'active' : '' }}">
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
            <div class="px-3 py-1 text-muted fw-bold" style="font-size: 0.68rem; text-transform: uppercase;">Asset &amp; Financial Control</div>
            <a href="{{ route('fincore.assets.index') }}" class="fc-dropdown-item">
                🏢 Fixed Assets &amp; Depreciation
            </a>
            <a href="{{ route('fincore.budgets.index') }}" class="fc-dropdown-item">
                📊 Budgeting &amp; Variance Analysis
            </a>
        </div>
    </div>

    {{-- 6. Financial Reports --}}
    <div class="fc-dropdown">
        <button type="button" class="fc-dropdown-toggle fc-subnav-link {{ request()->routeIs('fincore.reports.*', 'fincore.audit-trail.*') ? 'active' : '' }}">
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
            <div class="px-3 py-1 text-muted fw-bold" style="font-size: 0.68rem; text-transform: uppercase;">Sub-Ledger Ageing</div>
            <a href="{{ route('fincore.reports.ar-ageing') }}" class="fc-dropdown-item">
                ⏳ A/R Ageing (Debtors)
            </a>
            <a href="{{ route('fincore.reports.ap-ageing') }}" class="fc-dropdown-item">
                ⏳ A/P Ageing (Creditors)
            </a>
            <div class="fc-dropdown-divider"></div>
            <div class="px-3 py-1 text-muted fw-bold" style="font-size: 0.68rem; text-transform: uppercase;">Compliance &amp; Security</div>
            <a href="{{ route('fincore.audit-trail.index') }}" class="fc-dropdown-item">
                🛡️ System Audit Trail
            </a>
        </div>
    </div>

    {{-- 7. Settings (Right Aligned) --}}
    <a href="{{ route('fincore.settings.index') }}"
       class="fc-subnav-link ms-auto {{ request()->routeIs('fincore.settings.*') ? 'active' : '' }}">
        ⚙️ Settings
    </a>
</div>
