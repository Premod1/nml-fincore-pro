@extends('fincore-pro::layouts.wrapper')

@section('fincore_content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0">Trial Balance</h4>
            <small class="text-muted">As of {{ $endDate }} ({{ $currency }})</small>
        </div>
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm fc-no-print">🖨️ Print / Save PDF</button>
    </div>

    <div class="fc-card fc-no-print">
        <form method="GET" action="{{ route('fincore.reports.trial-balance') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Start Date (Optional)</label>
                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">End Date</label>
                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">SBU / Branch Code</label>
                <input type="text" name="sbu_code" class="form-control form-control-sm" value="{{ $sbuCode }}" placeholder="All Branches">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn fc-btn-primary btn-sm w-100">Filter Report</button>
            </div>
        </form>
    </div>

    <div class="fc-card">
        <div class="table-responsive">
            <table class="fc-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Account Name</th>
                        <th>Type</th>
                        <th class="text-end">Debit ({{ $currency }})</th>
                        <th class="text-end">Credit ({{ $currency }})</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($report['accounts'] as $item)
                        @php
                            $acc = $item['account'];
                            $typeVal = $acc->type instanceof \BackedEnum ? $acc->type->value : (string) $acc->type;
                        @endphp
                        <tr>
                            <td class="fw-bold">{{ $acc->code }}</td>
                            <td>
                                <a href="{{ route('fincore.reports.general-ledger', ['account_id' => $acc->id, 'end_date' => $endDate]) }}"
                                   class="text-decoration-none fw-semibold">
                                    {{ $acc->name }}
                                </a>
                            </td>
                            <td><span class="fc-badge fc-badge-info">{{ strtoupper($typeVal) }}</span></td>
                            <td class="text-end">{{ $item['debit'] > 0 ? number_format($item['debit'], 2) : '—' }}</td>
                            <td class="text-end">{{ $item['credit'] > 0 ? number_format($item['credit'], 2) : '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No posted ledger balances for this period.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="fw-bold bg-light">
                        <td colspan="3" class="text-end">Total</td>
                        <td class="text-end">{{ $currency }} {{ number_format($report['total_debits'], 2) }}</td>
                        <td class="text-end">{{ $currency }} {{ number_format($report['total_credits'], 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection
