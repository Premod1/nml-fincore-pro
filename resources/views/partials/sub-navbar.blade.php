<div class="fc-subnav">
    <a href="{{ route('fincore.dashboard') }}"
       class="fc-subnav-link {{ request()->routeIs('fincore.dashboard') ? 'active' : '' }}">
        📊 Overview
    </a>

    <a href="{{ route('fincore.accounts.index') }}"
       class="fc-subnav-link {{ request()->routeIs('fincore.accounts.*') ? 'active' : '' }}">
        🗂️ Chart of Accounts
    </a>

    <a href="{{ route('fincore.journals.index') }}"
       class="fc-subnav-link {{ request()->routeIs('fincore.journals.*') ? 'active' : '' }}">
        📝 Journal Entries
    </a>

    <a href="{{ route('fincore.reports.trial-balance') }}"
       class="fc-subnav-link {{ request()->routeIs('fincore.reports.trial-balance') ? 'active' : '' }}">
        ⚖️ Trial Balance
    </a>

    <a href="{{ route('fincore.reports.income-statement') }}"
       class="fc-subnav-link {{ request()->routeIs('fincore.reports.income-statement') ? 'active' : '' }}">
        📈 Profit &amp; Loss
    </a>

    <a href="{{ route('fincore.reports.balance-sheet') }}"
       class="fc-subnav-link {{ request()->routeIs('fincore.reports.balance-sheet') ? 'active' : '' }}">
        🏛️ Balance Sheet
    </a>

    <a href="{{ route('fincore.reports.general-ledger') }}"
       class="fc-subnav-link {{ request()->routeIs('fincore.reports.general-ledger') ? 'active' : '' }}">
        📖 General Ledger
    </a>

    <a href="{{ route('fincore.mappings.index') }}"
       class="fc-subnav-link ms-auto {{ request()->routeIs('fincore.mappings.*') ? 'active' : '' }}">
        ⚡ ERP Auto-Posting Rules
    </a>
</div>
