<div class="fc-subnav">
    {{-- QBO Signature '+ New' Global Action Drawer --}}
    <div class="fc-dropdown me-2">
        <button type="button" class="btn fc-btn-new">
            <span style="font-size: 1.1rem; line-height: 1;">+</span> New
        </button>
        <div class="fc-dropdown-menu" style="min-width: 260px;">
            <div class="px-3 py-1 text-muted fw-bold" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em;">
                Quick Accounting Actions
            </div>
            <a href="{{ route('fincore.journals.create') }}" class="fc-dropdown-item">
                📝 <strong>Journal Entry</strong> <small class="text-muted d-block">Manual debit/credit voucher</small>
            </a>
            <div class="fc-dropdown-divider"></div>
            <a href="{{ route('fincore.accounts.index') }}" class="fc-dropdown-item">
                🗂️ <strong>Add Account</strong> <small class="text-muted d-block">Create ledger account</small>
            </a>
            <div class="fc-dropdown-divider"></div>
            <a href="{{ route('fincore.mappings.index') }}" class="fc-dropdown-item">
                ⚡ <strong>ERP Posting Rules</strong> <small class="text-muted d-block">Map sales/purchases</small>
            </a>
        </div>
    </div>

    {{-- Main Navigation Links --}}
    <a href="{{ route('fincore.dashboard') }}"
       class="fc-subnav-link {{ request()->routeIs('fincore.dashboard') ? 'active' : '' }}">
        Dashboard
    </a>

    <a href="{{ route('fincore.accounts.index') }}"
       class="fc-subnav-link {{ request()->routeIs('fincore.accounts.*') ? 'active' : '' }}">
        Chart of Accounts
    </a>

    {{-- Transactions Dropdown --}}
    <div class="fc-dropdown">
        <button type="button" class="fc-dropdown-toggle fc-subnav-link {{ request()->routeIs('fincore.journals.*') ? 'active' : '' }}">
            Transactions ▾
        </button>
        <div class="fc-dropdown-menu">
            <a href="{{ route('fincore.journals.index') }}" class="fc-dropdown-item">Journal Entries</a>
            <a href="{{ route('fincore.journals.create') }}" class="fc-dropdown-item">+ New Journal Entry</a>
            <div class="fc-dropdown-divider"></div>
            <a href="#" class="fc-dropdown-item text-muted disabled">Bank Transactions &amp; Recon (Soon)</a>
            <a href="#" class="fc-dropdown-item text-muted disabled">Close Fiscal Period (Soon)</a>
        </div>
    </div>

    {{-- Financial Reports Dropdown --}}
    <div class="fc-dropdown">
        <button type="button" class="fc-dropdown-toggle fc-subnav-link {{ request()->routeIs('fincore.reports.*') ? 'active' : '' }}">
            Reports ▾
        </button>
        <div class="fc-dropdown-menu">
            <div class="px-3 py-1 text-muted fw-bold" style="font-size: 0.68rem; text-transform: uppercase;">Standard Financials</div>
            <a href="{{ route('fincore.reports.trial-balance') }}" class="fc-dropdown-item">Trial Balance</a>
            <a href="{{ route('fincore.reports.income-statement') }}" class="fc-dropdown-item">Profit and Loss (P&amp;L)</a>
            <a href="{{ route('fincore.reports.balance-sheet') }}" class="fc-dropdown-item">Balance Sheet</a>
            <a href="{{ route('fincore.reports.general-ledger') }}" class="fc-dropdown-item">General Ledger Report</a>
            <div class="fc-dropdown-divider"></div>
            <div class="px-3 py-1 text-muted fw-bold" style="font-size: 0.68rem; text-transform: uppercase;">Management Statements</div>
            <a href="#" class="fc-dropdown-item text-muted disabled">Statement of Cash Flows</a>
            <a href="#" class="fc-dropdown-item text-muted disabled">Accounts Receivable (A/R) Ageing</a>
            <a href="#" class="fc-dropdown-item text-muted disabled">Accounts Payable (A/P) Ageing</a>
        </div>
    </div>

    {{-- Settings (Right Aligned) --}}
    <a href="{{ route('fincore.mappings.index') }}"
       class="fc-subnav-link ms-auto {{ request()->routeIs('fincore.mappings.*') ? 'active' : '' }}">
        ⚙️ Accounting Settings
    </a>
</div>
