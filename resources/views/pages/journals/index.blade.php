@extends('fincore-pro::layouts.wrapper')

@section('fincore_content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0">Journal Entries</h4>
            <small class="text-muted">Audit-compliant Double-Entry Vouchers ({{ $currency }})</small>
        </div>
        <a href="{{ route('fincore.journals.create') }}" class="btn fc-btn-primary btn-sm">
            + Create Journal Voucher
        </a>
    </div>

    <div class="fc-card">
        <div class="table-responsive">
            <table class="fc-table">
                <thead>
                    <tr>
                        <th>Voucher #</th>
                        <th>Date</th>
                        <th>Reference</th>
                        <th>Description</th>
                        <th class="text-end">Debit ({{ $currency }})</th>
                        <th class="text-end">Credit ({{ $currency }})</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($entries as $entry)
                        @php
                            $statusVal = $entry->status instanceof \BackedEnum ? $entry->status->value : (string) $entry->status;
                        @endphp
                        <tr>
                            <td class="fw-bold">{{ $entry->entry_number }}</td>
                            <td>{{ $entry->date?->format('Y-m-d') }}</td>
                            <td>{{ $entry->reference ?? '—' }}</td>
                            <td>{{ $entry->description }}</td>
                            <td class="text-end fw-semibold">{{ number_format($entry->getDebitSum(), 2) }}</td>
                            <td class="text-end fw-semibold">{{ number_format($entry->getCreditSum(), 2) }}</td>
                            <td>
                                <span class="fc-badge fc-badge-{{ $statusVal }}">{{ strtoupper($statusVal) }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('fincore.journals.show', $entry->id) }}" class="btn btn-sm btn-outline-secondary">
                                    View / Actions
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No journal entries found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $entries->links() }}
        </div>
    </div>
@endsection
