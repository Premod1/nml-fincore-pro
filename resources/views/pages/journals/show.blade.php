@extends('fincore-pro::layouts.wrapper')

@section('fincore_content')
    @php
        $statusVal = $entry->status instanceof \BackedEnum ? $entry->status->value : (string) $entry->status;
    @endphp
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0">
                Voucher {{ $entry->entry_number }}
                <span class="fc-badge fc-badge-{{ $statusVal }} ms-2">{{ strtoupper($statusVal) }}</span>
            </h4>
            <small class="text-muted">Date: {{ $entry->date?->format('Y-m-d') }} | Ref: {{ $entry->reference ?? 'N/A' }}</small>
        </div>
        <div class="d-flex gap-2 fc-no-print">
            <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">🖨️ Print Voucher</button>
            @if($statusVal === 'draft' || $statusVal === 'submitted')
                <form action="{{ route('fincore.journals.post', $entry->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm">✓ Post to Ledger</button>
                </form>
            @endif
            @if($statusVal !== 'void')
                <form action="{{ route('fincore.journals.void', $entry->id) }}" method="POST" onsubmit="return confirm('Void this journal entry?');">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm">Void Entry</button>
                </form>
            @endif
        </div>
    </div>

    <div class="fc-card">
        <p class="mb-3"><strong>Narration:</strong> {{ $entry->description }}</p>

        <div class="table-responsive">
            <table class="fc-table">
                <thead>
                    <tr>
                        <th>Account Code &amp; Name</th>
                        <th>Description</th>
                        <th class="text-end">Debit ({{ $currency }})</th>
                        <th class="text-end">Credit ({{ $currency }})</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($entry->lines as $line)
                        <tr>
                            <td class="fw-semibold">
                                {{ $line->account?->code }} - {{ $line->account?->name }}
                            </td>
                            <td class="text-muted">{{ $line->description ?? '—' }}</td>
                            <td class="text-end fw-semibold">
                                {{ $line->type === 'debit' ? number_format($line->amount, 2) : '' }}
                            </td>
                            <td class="text-end fw-semibold">
                                {{ $line->type === 'credit' ? number_format($line->amount, 2) : '' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="fw-bold bg-light">
                        <td colspan="2" class="text-end">Total</td>
                        <td class="text-end">{{ $currency }} {{ number_format($entry->getDebitSum(), 2) }}</td>
                        <td class="text-end">{{ $currency }} {{ number_format($entry->getCreditSum(), 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection
