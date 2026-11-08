@extends('fincore-pro::layouts.wrapper')

@section('fincore_content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0">Journal Entries &amp; Vouchers</h4>
            <small class="text-muted">Audit-compliant Double-Entry General Ledger Transactions ({{ $currency }})</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('fincore.journals.create') }}" class="btn fc-btn-primary btn-sm">
                + Create Journal Voucher
            </a>
        </div>
    </div>

    {{-- Filter Toolbar --}}
    <div class="fc-card">
        <form method="GET" action="{{ route('fincore.journals.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Search Narration / Ref / Entry #</label>
                <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="e.g. JE-2026 or INV-001">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">Voucher Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $st)
                        <option value="{{ $st->value }}" {{ request('status') === $st->value ? 'selected' : '' }}>
                            {{ ucfirst($st->value) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">From Date</label>
                <input type="date" name="from_date" class="form-control form-control-sm" value="{{ request('from_date') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold">To Date</label>
                <input type="date" name="to_date" class="form-control form-control-sm" value="{{ request('to_date') }}">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn fc-btn-primary btn-sm flex-grow-1">Filter</button>
                <a href="{{ route('fincore.journals.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
            </div>
        </form>
    </div>

    {{-- Vouchers Table --}}
    <div class="fc-card">
        <div class="table-responsive">
            <table class="fc-table">
                <thead>
                    <tr>
                        <th style="width: 14%">Voucher #</th>
                        <th style="width: 10%">Date</th>
                        <th style="width: 12%">Reference</th>
                        <th style="width: 28%">Narration / Description</th>
                        <th style="width: 12%" class="text-end">Total Debit ({{ $currency }})</th>
                        <th style="width: 12%" class="text-end">Total Credit ({{ $currency }})</th>
                        <th style="width: 6%">Status</th>
                        <th style="width: 6%" class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($entries as $entry)
                        @php
                            $statusVal = $entry->status instanceof \BackedEnum ? $entry->status->value : (string) $entry->status;
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('fincore.journals.show', $entry->id) }}" class="fw-bold text-decoration-none">
                                    {{ $entry->entry_number }}
                                </a>
                                @if($entry->type)
                                    <div class="text-muted" style="font-size: 0.72rem;">{{ strtoupper($entry->type instanceof \BackedEnum ? $entry->type->value : $entry->type) }}</div>
                                @endif
                            </td>
                            <td>{{ $entry->date?->format('Y-m-d') }}</td>
                            <td><span class="text-muted">{{ $entry->reference ?? '—' }}</span></td>
                            <td>
                                <div>{{ $entry->description }}</div>
                                <div class="text-muted small" style="font-size: 0.75rem;">
                                    {{ $entry->lines->count() }} line items
                                </div>
                            </td>
                            <td class="text-end fw-semibold">{{ number_format($entry->getDebitSum(), 2) }}</td>
                            <td class="text-end fw-semibold">{{ number_format($entry->getCreditSum(), 2) }}</td>
                            <td>
                                <span class="fc-badge fc-badge-{{ $statusVal }}">
                                    {{ strtoupper($statusVal) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('fincore.journals.show', $entry->id) }}" class="btn btn-sm btn-outline-secondary py-1 px-2">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <div class="mb-2" style="font-size: 1.5rem;">📝</div>
                                <strong>No journal vouchers found matching criteria.</strong>
                                <p class="small mb-3">Record transactions via ERP Auto-Posting Rules or create a manual journal voucher.</p>
                                <a href="{{ route('fincore.journals.create') }}" class="btn fc-btn-primary btn-sm">
                                    + Create Manual Journal Entry
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($entries->hasPages())
            <div class="mt-3">
                {{ $entries->links() }}
            </div>
        @endif
    </div>
@endsection
