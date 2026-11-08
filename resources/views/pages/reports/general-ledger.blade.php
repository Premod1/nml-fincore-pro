@extends('fincore-pro::layouts.wrapper')

@section('fincore_content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-0">General Ledger Drill-Down</h4>
            <small class="text-muted">Detailed Account Transactions &amp; Running Balances ({{ $currency }})</small>
        </div>
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm fc-no-print">🖨️ Print Ledger</button>
    </div>

    <div class="fc-card fc-no-print">
        <form method="GET" action="{{ route('fincore.reports.general-ledger') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-semibold">Account</label>
                <select name="account_id" class="form-select form-select-sm">
                    <option value="">-- All Active Accounts --</option>
                    @foreach($allAccounts as $acc)
                        <option value="{{ $acc->id }}" {{ $accountId == $acc->id ? 'selected' : '' }}>
                            {{ $acc->code }} - {{ $acc->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Start Date</label>
                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">End Date</label>
                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn fc-btn-primary btn-sm w-100">Filter</button>
            </div>
        </form>
    </div>

    @foreach($report['accounts'] as $item)
        @if(count($item['entries']) > 0 || $accountId)
            <div class="fc-card">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-bold mb-0">
                        {{ $item['account']->code }} — {{ $item['account']->name }}
                    </h6>
                    <div class="small text-muted">
                        Opening: <strong>{{ $currency }} {{ number_format($item['opening_balance'], 2) }}</strong> |
                        Closing: <strong>{{ $currency }} {{ number_format($item['closing_balance'], 2) }}</strong>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="fc-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Voucher #</th>
                                <th>Ref</th>
                                <th>Description</th>
                                <th class="text-end">Debit</th>
                                <th class="text-end">Credit</th>
                                <th class="text-end">Running Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($item['entries'] as $line)
                                <tr>
                                    <td>{{ $line['date'] }}</td>
                                    <td class="fw-semibold">{{ $line['entry_number'] }}</td>
                                    <td>{{ $line['reference'] ?? '—' }}</td>
                                    <td>{{ $line['description'] }}</td>
                                    <td class="text-end">{{ $line['type'] === 'debit' ? number_format($line['amount'], 2) : '—' }}</td>
                                    <td class="text-end">{{ $line['type'] === 'credit' ? number_format($line['amount'], 2) : '—' }}</td>
                                    <td class="text-end fw-bold">{{ number_format($line['running_balance'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-3">No transactions in selected date range.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endforeach
@endsection
