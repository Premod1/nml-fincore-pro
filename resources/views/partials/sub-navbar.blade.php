<div class="fc-subnav">
    {{-- Overview --}}
    <a href="{{ route('fincore.dashboard') }}"
       class="fc-subnav-link {{ request()->routeIs('fincore.dashboard') ? 'active' : '' }}">
        📊 Overview
    </a>

    {{-- Master Data --}}
    <a href="{{ route('fincore.accounts.index') }}"
       class="fc-subnav-link {{ request()->routeIs('fincore.accounts.*') ? 'active' : '' }}">
        🗂️ Chart of Accounts
    </a>

    {{-- Transactions Dropdown --}}
    <div class="fc-dropdown">
        <button type="button" class="fc-dropdown-toggle fc-subnav-link {{ request()->routeIs('fincore.journals.*') ? 'active' : '' }}">
            📝 Transactions ▾
        </button>
        <div class="fc-dropdown-menu">
            <a href="{{ route('fincore.journals.index') }}" class="fc-dropdown-item">Journal Entries</a>
            <a href="{{ route('fincore.journals.create') }}" class="fc-dropdown-item">+ New Manual Journal</a>
            <div class="fc-dropdown-divider"></div>
            <a href="#" class="fc-dropdown-item text-muted disabled">Bank Reconciliation (Soon)</a>
            <a href="#" class="fc-dropdown-item text-muted disabled">Period &amp; Year Lock (Soon)</a>
        </div>
    </div>

    {{-- Financial Reports Dropdown --}}
    <div class="fc-dropdown">
        <button type="button" class="fc-dropdown-toggle fc-subnav-link {{ request()->routeIs('fincore.reports.*') ? 'active' : '' }}">
            📈 Financial Reports ▾
        </button>
        <div class="fc-dropdown-menu">
            <a href="{{ route('fincore.reports.trial-balance') }}" class="fc-dropdown-item">⚖️ Trial Balance</a>
            <a href="{{ route('fincore.reports.income-statement') }}" class="fc-dropdown-item">📊 Profit &amp; Loss (P&amp;L)</a>
            <a href="{{ route('fincore.reports.balance-sheet') }}" class="fc-dropdown-item">🏛️ Balance Sheet</a>
            <a href="{{ route('fincore.reports.general-ledger') }}" class="fc-dropdown-item">📖 General Ledger</a>
            <div class="fc-dropdown-divider"></div>
            <a href="#" class="fc-dropdown-item text-muted disabled">Cash Flow Statement</a>
            <a href="#" class="fc-dropdown-item text-muted disabled">AR / AP Ageing</a>
            <a href="#" class="fc-dropdown-item text-muted disabled">Tax &amp; VAT Schedules</a>
        </div>
    </div>

    {{-- Settings / Posting Rules (Right Aligned) --}}
    <a href="{{ route('fincore.mappings.index') }}"
       class="fc-subnav-link ms-auto {{ request()->routeIs('fincore.mappings.*') ? 'active' : '' }}">
        ⚙️ ERP Posting Rules
    </a>
</div>
