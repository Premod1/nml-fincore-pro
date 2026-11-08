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
                    + Seed Default Accounts
                </button>
            </form>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="fc-card">
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <a href="{{ route('fincore.accounts.index') }}"
                       class="btn btn-sm {{ !$typeFilter ? 'fc-btn-primary' : 'btn-outline-secondary' }}">
                        All Types
                    </a>
                    @foreach($types as $t)
                        <a href="{{ route('fincore.accounts.index', ['type' => $t->value]) }}"
                           class="btn btn-sm {{ $typeFilter === $t->value ? 'fc-btn-primary' : 'btn-outline-secondary' }}">
                            {{ ucfirst($t->value) }}
                        </a>
                    @endforeach
                </div>

                <div class="table-responsive">
                    <table class="fc-table">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Account Name</th>
                                <th>Type</th>
                                <th>Subtype</th>
                                <th class="text-end">Current Balance</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($accounts as $acc)
                                @php
                                    $typeVal = $acc->type instanceof \BackedEnum ? $acc->type->value : (string) $acc->type;
                                    $bal = $balances[$acc->id] ?? 0.0;
                                @endphp
                                <tr>
                                    <td class="fw-bold">{{ $acc->code }}</td>
                                    <td class="fw-semibold">{{ $acc->name }}</td>
                                    <td><span class="fc-badge fc-badge-info">{{ strtoupper($typeVal) }}</span></td>
                                    <td class="text-muted small">{{ $acc->subtype }}</td>
                                    <td class="text-end fw-bold">
                                        {{ $currency }} {{ number_format($bal, 2) }}
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('fincore.reports.general-ledger', ['account_id' => $acc->id]) }}"
                                           class="btn btn-sm btn-light">
                                            Ledger &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        No accounts found. Click <strong>Seed Default Accounts</strong> to load the standard Chart of Accounts.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="fc-card">
                <h6 class="fw-bold mb-3">Create New Ledger Account</h6>
                <form action="{{ route('fincore.accounts.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Account Code</label>
                        <input type="text" name="code" class="form-control form-control-sm" placeholder="e.g. 1120" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Account Name</label>
                        <input type="text" name="name" class="form-control form-control-sm" placeholder="e.g. Sampath Bank Current A/C" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Account Classification</label>
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
