@extends('fincore-pro::layouts.wrapper')

@section('fincore_content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('fincore.banking.reconciliations.index') }}" class="text-decoration-none text-muted small">← Reconciliations</a>
                <span class="text-muted small">/</span>
                <h4 class="fw-bold mb-0">Reconciliation: {{ $reconciliation->account?->code }} - {{ $reconciliation->account?->name }}</h4>
            </div>
            <small class="text-muted">
                Statement As Of: <strong>{{ $reconciliation->statement_date?->format('Y-m-d') }}</strong>
                @if($reconciliation->is_finalized)
                    | <span class="fc-badge fc-badge-success">Finalized on {{ $reconciliation->reconciled_at?->format('Y-m-d H:i') }}</span>
                @else
                    | <span class="fc-badge fc-badge-warning">In Progress</span>
                @endif
            </small>
        </div>
        <div class="d-flex gap-2">
            @if(!$reconciliation->is_finalized)
                <form action="{{ route('fincore.banking.reconciliations.finalize', $reconciliation->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn fc-btn-primary btn-sm px-3"
                            {{ abs($difference) > 0.005 ? 'disabled' : '' }}
                            title="{{ abs($difference) > 0.005 ? 'Difference must be 0.00 to finalize' : 'Finalize and lock reconciliation' }}">
                        ✓ Finalize Reconciliation
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Reconciliation Balance Ribbon --}}
    <div class="row g-2 mb-4">
        <div class="col-md-2 col-6">
            <div class="fc-card text-center p-2">
                <div class="text-muted small" style="font-size:0.75rem;">Beginning Balance</div>
                <div class="fw-bold font-monospace mt-1">{{ number_format($reconciliation->opening_balance, 2) }}</div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="fc-card text-center p-2">
                <div class="text-muted small" style="font-size:0.75rem;">+ Cleared Deposits</div>
                <div class="fw-bold font-monospace text-success mt-1">+{{ number_format($clearedDebits, 2) }}</div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="fc-card text-center p-2">
                <div class="text-muted small" style="font-size:0.75rem;">- Cleared Payments</div>
                <div class="fw-bold font-monospace text-danger mt-1">-{{ number_format($clearedCredits, 2) }}</div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="fc-card text-center p-2">
                <div class="text-muted small" style="font-size:0.75rem;">Cleared Balance</div>
                <div class="fw-bold font-monospace text-primary mt-1">{{ number_format($clearedBalance, 2) }}</div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="fc-card text-center p-2">
                <div class="text-muted small" style="font-size:0.75rem;">Statement Ending</div>
                <div class="fw-bold font-monospace mt-1">{{ number_format($reconciliation->closing_balance, 2) }}</div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="fc-card text-center p-2 {{ abs($difference) < 0.005 ? 'bg-success-subtle' : 'bg-warning-subtle' }}"
                 style="{{ abs($difference) < 0.005 ? 'border-color: #22c55e;' : 'border-color: #f59e0b;' }}">
                <div class="small fw-semibold {{ abs($difference) < 0.005 ? 'text-success' : 'text-danger' }}" style="font-size:0.75rem;">
                    DIFFERENCE
                </div>
                <div class="fw-bold font-monospace mt-1 {{ abs($difference) < 0.005 ? 'text-success' : 'text-danger' }}" style="font-size: 1.1rem;">
                    {{ number_format($difference, 2) }}
                </div>
            </div>
        </div>
    </div>

    @if(abs($difference) > 0.005 && !$reconciliation->is_finalized)
        <div class="fc-alert fc-alert-warning mb-3">
            <span>ℹ️ <strong>Difference is {{ number_format($difference, 2) }} {{ $currency }}.</strong> Clear transactions below that match your bank statement until the difference reaches <strong>0.00</strong>.</span>
        </div>
    @elseif(!$reconciliation->is_finalized)
        <div class="fc-alert fc-alert-success mb-3">
            <span>🎉 <strong>All balances match!</strong> The difference is 0.00. You can now click <strong>"✓ Finalize Reconciliation"</strong> to lock this statement.</span>
        </div>
    @endif

    <div class="row g-3">
        {{-- Section 1: Uncleared Transactions --}}
        <div class="col-lg-6">
            <div class="fc-card p-0">
                <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">Uncleared Ledger Transactions ({{ $unreconciledLines->count() }})</h6>
                    <span class="text-muted small">Posted on or before statement date</span>
                </div>
                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="fc-table">
                        <thead class="sticky-top bg-light">
                            <tr>
                                <th>Date</th>
                                <th>JV #</th>
                                <th>Description</th>
                                <th class="text-end">Amount ({{ $currency }})</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($unreconciledLines as $line)
                                <tr>
                                    <td>{{ $line->journalEntry?->date?->format('Y-m-d') }}</td>
                                    <td>
                                        <a href="{{ route('fincore.journals.show', $line->journal_entry_id) }}" target="_blank" class="text-decoration-none font-monospace small">
                                            {{ $line->journalEntry?->entry_number }}
                                        </a>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold text-truncate" style="max-width: 180px;" title="{{ $line->description ?: $line->journalEntry?->description }}">
                                            {{ $line->description ?: $line->journalEntry?->description }}
                                        </div>
                                    </td>
                                    <td class="text-end font-monospace">
                                        @if($line->type === 'debit')
                                            <span class="text-success">+{{ number_format($line->amount, 2) }}</span>
                                            <span class="fc-badge fc-badge-info ms-1" style="font-size:0.6rem;">Dr</span>
                                        @else
                                            <span class="text-danger">-{{ number_format($line->amount, 2) }}</span>
                                            <span class="fc-badge fc-badge-warning ms-1" style="font-size:0.6rem;">Cr</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if(!$reconciliation->is_finalized)
                                            <form action="{{ route('fincore.banking.reconciliations.clear-line', $reconciliation->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="line_id" value="{{ $line->id }}">
                                                <button type="submit" class="btn btn-sm btn-outline-success py-0 px-2" style="font-size:0.75rem;">
                                                    ✓ Clear
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4 small">
                                        No uncleared transactions pending for this bank account.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Section 2: Cleared Transactions in this Statement --}}
        <div class="col-lg-6">
            <div class="fc-card p-0">
                <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-success">Cleared / Reconciled Transactions ({{ $reconciliation->lines->count() }})</h6>
                    <span class="text-muted small font-monospace">{{ number_format($reconciliation->lines->sum('amount'), 2) }} {{ $currency }}</span>
                </div>
                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="fc-table">
                        <thead class="sticky-top bg-light">
                            <tr>
                                <th>Date</th>
                                <th>JV #</th>
                                <th>Description</th>
                                <th class="text-end">Amount ({{ $currency }})</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reconciliation->lines as $line)
                                <tr>
                                    <td>{{ $line->journalEntry?->date?->format('Y-m-d') }}</td>
                                    <td>
                                        <a href="{{ route('fincore.journals.show', $line->journal_entry_id) }}" target="_blank" class="text-decoration-none font-monospace small">
                                            {{ $line->journalEntry?->entry_number }}
                                        </a>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold text-truncate" style="max-width: 180px;" title="{{ $line->description ?: $line->journalEntry?->description }}">
                                            {{ $line->description ?: $line->journalEntry?->description }}
                                        </div>
                                    </td>
                                    <td class="text-end font-monospace">
                                        @if($line->type === 'debit')
                                            <span class="text-success">+{{ number_format($line->amount, 2) }}</span>
                                            <span class="fc-badge fc-badge-info ms-1" style="font-size:0.6rem;">Dr</span>
                                        @else
                                            <span class="text-danger">-{{ number_format($line->amount, 2) }}</span>
                                            <span class="fc-badge fc-badge-warning ms-1" style="font-size:0.6rem;">Cr</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if(!$reconciliation->is_finalized)
                                            <form action="{{ route('fincore.banking.reconciliations.unclear-line', $reconciliation->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="line_id" value="{{ $line->id }}">
                                                <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size:0.75rem;" title="Move back to uncleared">
                                                    ✕ Unclear
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-muted small">Locked</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4 small">
                                        No transactions cleared yet. Click "✓ Clear" on the left list to match entries.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
