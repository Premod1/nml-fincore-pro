@extends('fincore-pro::layouts.wrapper')

@section('fincore_content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0">General Ledger</h4>
            <small class="text-muted">Account Statement &amp; Transaction Audit Trail ({{ $currency }})</small>
        </div>
        <div class="d-flex gap-2 fc-no-print">
            <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">🖨️ Print Ledger</button>
        </div>
    </div>

    {{-- Filter Toolbar --}}
    <div class="fc-card fc-no-print">
        <form method="GET" action="{{ route('fincore.reports.general-ledger') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Account</label>
                <select name="account_id" class="form-select form-select-sm">
                    <option value="">-- All General Ledger Accounts --</option>
                    @foreach($allAccounts as $acc)
                        <option value="{{ $acc->id }}" {{ $accountId == $acc->id ? 'selected' : '' }}>
                            {{ $acc->code }} - {{ $acc->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">From Date</label>
                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">To Date</label>
                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn fc-btn-primary btn-sm w-100">Run Report</button>
            </div>
        </form>
    </div>

    {{-- Account Ledger Blocks --}}
    @foreach($report['accounts'] as $item)
        @if(count($item['entries']) > 0 || $accountId)
            <div class="fc-card">
                <div class="d-flex justify-content-between align-items-center pb-2 mb-2 border-bottom">
                    <div>
                        <span class="text-muted fw-bold me-2">{{ $item['account']->code }}</span>
                        <h6 class="fw-bold d-inline mb-0">{{ $item['account']->name }}</h6>
                        <span class="fc-badge fc-badge-info ms-2" style="font-size: 0.68rem;">{{ strtoupper($item['account']->type instanceof \BackedEnum ? $item['account']->type->value : $item['account']->type) }}</span>
                    </div>
                    <div class="small">
                        <span class="text-muted">Opening Balance:</span> <strong class="me-3">{{ $currency }} {{ number_format($item['opening_balance'], 2) }}</strong>
                        <span class="text-muted">Closing Balance:</span> <strong class="text-primary">{{ $currency }} {{ number_format($item['closing_balance'], 2) }}</strong>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="fc-table">
                        <thead>
                            <tr>
                                <th style="width: 10%;">Date</th>
                                <th style="width: 14%;">Voucher #</th>
                                <th style="width: 12%;">Reference</th>
                                <th style="width: 34%;">Transaction Narration</th>
                                <th style="width: 10%;" class="text-end">Debit</th>
                                <th style="width: 10%;" class="text-end">Credit</th>
                                <th style="width: 10%;" class="text-end">Running Bal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($item['entries'] as $line)
                                <tr>
                                    <td>{{ $line['date'] }}</td>
                                    <td>
                                        {{-- Drill-down to Source Voucher --}}
                                        <a href="{{ route('fincore.journals.show', $line['entry_number']) }}" class="fc-drill-link">
                                            {{ $line['entry_number'] }}
                                        </a>
                                    </td>
                                    <td><span class="text-muted">{{ $line['reference'] ?? '—' }}</span></td>
                                    <td>{{ $line['description'] }}</td>
                                    <td class="text-end fw-semibold">{{ $line['type'] === 'debit' ? number_format($line['amount'], 2) : '—' }}</td>
                                    <td class="text-end fw-semibold">{{ $line['type'] === 'credit' ? number_format($line['amount'], 2) : '—' }}</td>
                                    <td class="text-end fw-bold text-dark">{{ number_format($line['running_balance'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-3">No activity recorded for this account during selected period.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endforeach
@endsection
