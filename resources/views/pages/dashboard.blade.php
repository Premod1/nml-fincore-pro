@extends('fincore-pro::layouts.wrapper')

@section('fincore_content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0">Financial Overview</h4>
            <small class="text-muted">Real-time General Ledger &amp; Double-Entry Metrics ({{ $currency }})</small>
        </div>
        <div class="d-flex gap-2">
            @if($accountsCount === 0)
                <form action="{{ route('fincore.accounts.seed') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-warning btn-sm fw-semibold">
                        ⚡ Initialize Standard Chart of Accounts
                    </button>
                </form>
            @endif
            <a href="{{ route('fincore.journals.create') }}" class="btn fc-btn-primary btn-sm">
                + New Manual Journal Entry
            </a>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="fc-card mb-0">
                <div class="fc-kpi-label">MTD Revenue</div>
                <div class="fc-kpi-value text-success">
                    {{ $currency }} {{ number_format($pnl['revenue']['total'] ?? 0, 2) }}
                </div>
                <small class="text-muted">Current month income</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="fc-card mb-0">
                <div class="fc-kpi-label">MTD Expenses</div>
                <div class="fc-kpi-value text-danger">
                    {{ $currency }} {{ number_format($pnl['expenses']['total'] ?? 0, 2) }}
                </div>
                <small class="text-muted">Current month costs</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="fc-card mb-0">
                <div class="fc-kpi-label">MTD Net Profit</div>
                <div class="fc-kpi-value {{ ($pnl['net_income'] ?? 0) >= 0 ? 'text-primary' : 'text-danger' }}">
                    {{ $currency }} {{ number_format($pnl['net_income'] ?? 0, 2) }}
                </div>
                <small class="text-muted">Revenue minus expenses</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="fc-card mb-0">
                <div class="fc-kpi-label">Total Assets</div>
                <div class="fc-kpi-value">
                    {{ $currency }} {{ number_format($bs['assets']['total'] ?? 0, 2) }}
                </div>
                <small class="text-muted">
                    Ledger Status:
                    @if($tb['is_balanced'] ?? true)
                        <span class="text-success fw-semibold">✓ Balanced</span>
                    @else
                        <span class="text-danger fw-semibold">⚠ Out of Balance</span>
                    @endif
                </small>
            </div>
        </div>
    </div>

    <div class="fc-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0">Recent Ledger Journal Entries</h6>
            <a href="{{ route('fincore.journals.index') }}" class="btn btn-outline-secondary btn-sm">View All</a>
        </div>

        <div class="table-responsive">
            <table class="fc-table">
                <thead>
                    <tr>
                        <th>Entry #</th>
                        <th>Date</th>
                        <th>Reference</th>
                        <th>Description</th>
                        <th class="text-end">Total Debit</th>
                        <th class="text-end">Total Credit</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentEntries as $entry)
                        @php
                            $statusVal = $entry->status instanceof \BackedEnum ? $entry->status->value : (string) $entry->status;
                        @endphp
                        <tr>
                            <td class="fw-semibold">{{ $entry->entry_number }}</td>
                            <td>{{ $entry->date?->format('Y-m-d') }}</td>
                            <td><span class="text-muted">{{ $entry->reference ?? '—' }}</span></td>
                            <td>{{ $entry->description }}</td>
                            <td class="text-end fw-semibold">{{ number_format($entry->getDebitSum(), 2) }}</td>
                            <td class="text-end fw-semibold">{{ number_format($entry->getCreditSum(), 2) }}</td>
                            <td>
                                <span class="fc-badge fc-badge-{{ $statusVal }}">
                                    {{ strtoupper($statusVal) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('fincore.journals.show', $entry->id) }}" class="btn btn-sm btn-light">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                No journal entries recorded yet. Use <strong>ERP Auto-Posting Rules</strong> or create a manual entry.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
