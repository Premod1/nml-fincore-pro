@extends('fincore-pro::layouts.wrapper')

@section('fincore_content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0">Chart of Accounts</h4>
            <small class="text-muted">Manage General Ledger Accounts &amp; Live Balances ({{ $currency }})</small>
        </div>
        <div class="d-flex gap-2">
            <form action="{{ route('fincore.accounts.seed') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-secondary btn-sm">
                    + Seed Standard Accounts
                </button>
            </form>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="fc-card">
                {{-- Search & Type Filter Toolbar --}}
                <form method="GET" action="{{ route('fincore.accounts.index') }}" class="row g-2 mb-3">
                    <div class="col-md-5">
                        <input type="text" name="search" class="form-control form-control-sm"
                               placeholder="Search Code or Account Name..." value="{{ $search }}">
                    </div>
                    <div class="col-md-4">
                        <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">All Account Types</option>
                            @foreach($types as $t)
                                <option value="{{ $t->value }}" {{ $typeFilter === $t->value ? 'selected' : '' }}>
                                    {{ ucfirst($t->value) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-1">
                        <button type="submit" class="btn fc-btn-primary btn-sm flex-grow-1">Filter</button>
                        @if($search || $typeFilter)
                            <a href="{{ route('fincore.accounts.index') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
                        @endif
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="fc-table">
                        <thead>
                            <tr>
                                <th style="width: 15%;">Code</th>
                                <th style="width: 35%;">Account Name</th>
                                <th style="width: 15%;">Type</th>
                                <th style="width: 15%;">Subtype</th>
                                <th style="width: 20%;" class="text-end">Balance ({{ $currency }})</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($accounts as $acc)
                                @php
                                    $typeVal = $acc->type instanceof \BackedEnum ? $acc->type->value : (string) $acc->type;
                                    $bal = $balances[$acc->id] ?? 0.0;
                                @endphp
                                <tr>
                                    <td class="text-muted fw-semibold">{{ $acc->code }}</td>
                                    <td>
                                        <a href="{{ route('fincore.reports.general-ledger', ['account_id' => $acc->id]) }}"
                                           class="fc-drill-link">
                                            {{ $acc->name }}
                                        </a>
                                    </td>
                                    <td><span class="fc-badge fc-badge-info">{{ strtoupper($typeVal) }}</span></td>
                                    <td class="text-muted small">{{ $acc->subtype }}</td>
                                    <td class="text-end fw-semibold">
                                        <a href="{{ route('fincore.reports.general-ledger', ['account_id' => $acc->id]) }}"
                                           class="fc-drill-link text-dark">
                                            {{ number_format($bal, 2) }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        No accounts found matching filter.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination Links --}}
                @if($accounts->hasPages())
                    <div class="mt-3">
                        {{ $accounts->links() }}
                    </div>
                @endif
            </div>
        </div>

        <div class="col-lg-4">
            <div class="fc-card sticky-top" style="top: 1rem;">
                <h6 class="fw-bold mb-3">Create New Ledger Account</h6>
                <form action="{{ route('fincore.accounts.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Account Code</label>
                        <input type="text" name="code" class="form-control form-control-sm" placeholder="e.g. 1120" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Account Name</label>
                        <input type="text" name="name" class="form-control form-control-sm" placeholder="e.g. Commercial Bank Current A/C" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Classification Type</label>
                        <select name="type" class="form-select form-select-sm" required>
                            @foreach($types as $t)
                                <option value="{{ $t->value }}">{{ ucfirst($t->value) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Subtype</label>
                        <input type="text" name="subtype" class="form-control form-control-sm" placeholder="e.g. current_asset">
                    </div>
                    <button type="submit" class="btn fc-btn-primary btn-sm w-100">
                        + Add Account
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
